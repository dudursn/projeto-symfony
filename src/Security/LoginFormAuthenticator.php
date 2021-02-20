<?php

namespace App\Security;

use App\Entity\Logins;
use App\Services\FlashMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $flashMessages;
    private $security;
    private $tokenStorage;
    private $session;
    
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, FlashMessages $flashMessages, Security $security, TokenStorageInterface $tokenStorage, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        /***   ESPECÍFICOS DA REGRA DE NEGÓCIO   ***/
        $this->flashMessages = $flashMessages;
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(Logins::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Usuário não existe. Por favor, verifique se o e-mail está correto.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if($this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            if (!$user->getAtivo()) {
                // fail authentication with a custom error
                throw new CustomUserMessageAuthenticationException('Acesso desativado. Por favor, entre em contato com o suporte para avaliar o motivo da suspensão.');
            }
            return true;
        }
        throw new CustomUserMessageAuthenticationException('Senha incorreta! Por favor, tente novamente (certifique-se que o caps lock está desativado)');
        return false;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        $ignoredPath = [
            $this->urlGenerator->generate('index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('registro', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];
        
        if ($targetPath && (!in_array($targetPath, $ignoredPath))) {
            return new RedirectResponse($targetPath);
        } else if($token->getUser()->getRole() == 'ROLE_ADMIN') {
            return new RedirectResponse($this->urlGenerator->generate('admin-eleicoes'));
        } else if($token->getUser()->getRole() == 'ROLE_USUARIO' || $token->getUser()->getRole() == 'ROLE_CANDIDATO') {
            return new RedirectResponse($this->urlGenerator->generate('index'));
        } 
        $this->flashMessages->add('Não foi possível identificar o Nível de Acesso do Usuário', 'd');
        return new RedirectResponse($this->urlGenerator->generate('app_logout'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }
}
