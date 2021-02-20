<?php

namespace App\Form;

use App\Entity\Logins;
use App\Entity\Usuarios;
use App\Form\DataTransformer\IntToCpfCnpjTransformer;
use App\Form\EventListener\LoginsEventListener;
use App\Services\Constants;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecuperacaoType extends AbstractType
{
    private $loginsEventListener;
    private $urlGenerator;
    private $intToCpfCnpjTransformer;
    
    public function __construct(LoginsEventListener $loginsEventListener, UrlGeneratorInterface $urlGenerator, IntToCpfCnpjTransformer $intToCpfCnpj) {
        $this->loginsEventListener = $loginsEventListener;
        $this->urlGenerator = $urlGenerator;
        $this->intToCpfCnpjTransformer = $intToCpfCnpj;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
               
                ->add('pass', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, [
                    'type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
                    'required' => true,
                    'data' => '',
                    'invalid_message' => 'O valor do campo Confirme a Senha precisar ser igual ao valor do campo Senha.',
                    'first_options'  => [
                        'label' => 'Nova Senha (*)',
                        'help' => 'Será usada para acessar a plataforma de votos',
                    ],
                    'second_options' => ['label' => 'Confirme a Senha'],                    
                    //'options' => ['attr' => ['class' => 'password-field']],
                ])
                
        ;
    
       
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => Usuarios::class,
        ]);
    }
}
