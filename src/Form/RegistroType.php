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

class RegistroType extends AbstractType
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
                ->add('nome', null, [
                    'required' => true,
                    'label' => 'Nome Completo (*)',
                    'attr' => [
                        'autofocus' => 'autofocus',
                    ],
                ])
                ->add('doc', null, [
                    'required' => true,
                    'label' => 'CPF (*)',
                    'attr' => [
                        'class' => 'maskAsCpf',
                    ],
                    'constraints' => [
                        new \App\Validator\Constraints\CpfCnpj(),
                    ],
                ])
                ->add('nascimento', DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'label' => 'Data de Nascimento (Opcional)',
                    'attr' => [
                        'class' => 'maskAsDate',
                    ],
                ])
                ->add('whatsapp', null, [
                    'required' => true,
                    'label' => 'WhatsApp (*)',
                    'attr' => [
                        'class' => 'maskAsTel',
                    ] 
                ])
                ->add('telefone', null, [
                    'required' => false,
                    'label' => 'Telefone (Opcional)',
                    'attr' => [
                        'class' => 'maskAsTel',
                    ] 
                ])
                ->add('email', EmailType::class, [
                    'required' => true,
                    'label' => 'E-mail (*)',
                    'help' => 'Será usado para acessar a plataforma de votos',
                    'constraints' => [
                        new \Symfony\Component\Validator\Constraints\Email(),
                    ],
                ])
                ->add('pass', \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class, [
                    'type' => \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
                    'required' => true,
                    'data' => '',
                    'invalid_message' => 'O valor do campo Confirme a Senha precisar ser igual ao valor do campo Senha.',
                    'first_options'  => [
                        'label' => 'Senha (*)',
                        'help' => 'Será usada para acessar a plataforma de votos',
                    ],
                    'second_options' => ['label' => 'Confirme a Senha'],                    
                    //'options' => ['attr' => ['class' => 'password-field']],
                ])
                
        ;
        
        
        $builder->get('doc')->addModelTransformer($this->intToCpfCnpjTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => Usuarios::class,
        ]);
    }
}
