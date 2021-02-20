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

class UsuariosType extends AbstractType
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
                ->add('login', EntityType::class, [
                    'required' => true,
                    'class' => Logins::class,
                    'choice_label' => 'nome',
                    'choice_value' => 'id',
                    'choices' => [],
                    //'query_builder' => $this->cidadesMapper->select(array(), 'nome', 'query_builder'),
                    'placeholder' => false,
                    'label' => 'Login',
                    'attr' => [                        
                        'class' => 'rtlMultiSelect',
                        'data-url' => $this->urlGenerator->generate('api-logins', []), 
                    ]
                ])
                ->add('nome', null, [
                    'required' => true,
                    'attr' => [
                        //'autofocus' => 'autofocus',
                    ],
                ])
                ->add('doc', null, [
                    'required' => true,
                    'label' => 'CPF',
                    'attr' => [
                        'class' => 'maskAsCpf',
                    ]
                ])
                ->add('whatsapp', null, [
                    'required' => false,
                    'label' => 'WhatsApp',
                    'attr' => [
                        'class' => 'maskAsTel',
                    ] 
                ])
                ->add('telefone', null, [
                    'required' => false,
                    'label' => 'Telefone',
                    'attr' => [
                        'class' => 'maskAsTel',
                    ] 
                ])
                ->add('email', EmailType::class, [
                    'required' => false,
                    'label' => 'E-mail',
                    'help' => 'Deixe vazio para usar o e-mail de acesso'
                ])
                ->add('cargo', null, [
                    'required' => false,
                ])
                ->add('rg', null, [
                    'required' => false,
                    'label' => 'RG'
                ])
                ->add('matricula', null, [
                    'required' => false,
                    'label' => 'Matrícula'
                ])
                ->add('nascimento', DateType::class, [
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'class' => 'maskAsDate',
                    ],
                ])
                ->add('sexo', ChoiceType::class, [
                    'required' => false,
                    'choices' => Constants::pessoasSexosChoiceArray(),
                    'placeholder' => false,
                ])
                ->add('cep', null, [
                    'required' => false,
                    'label' => 'CEP',
                    'attr' => [
                        'class' => 'maskAsCep',
                    ]
                ])
                ->add('logradouro', null, [
                    'required' => false,
                ])
                ->add('enumero', null, [
                    'required' => false,
                    'label' => 'Número',
                ])
                ->add('complemento', null, [
                    'required' => false,
                ])
                ->add('bairro', null, [
                    'required' => false,
                ])
                ->add('cidade', null, [
                    'required' => false,
                ])
                ->add('uf', null, [
                    'required' => false,
                    'label' => 'UF',
                ])
                
                ;
        
        $builder->get('login')->addEventSubscriber($this->loginsEventListener);
        $builder->get('doc')->addModelTransformer($this->intToCpfCnpjTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,
        ]);
    }
}
