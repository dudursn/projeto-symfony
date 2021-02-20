<?php

namespace App\Form;

use App\Entity\Candidatos;
use App\Entity\Usuarios;
use App\Form\DataTransformer\IntToCpfCnpjTransformer;
use App\Form\EventListener\UsuariosEventListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CandidatosType extends AbstractType
{
    private $usuariosEventListener;
    private $urlGenerator;
    
    
    public function __construct(UsuariosEventListener $usuariosEventListener, UrlGeneratorInterface $urlGenerator) {
        $this->usuariosEventListener = $usuariosEventListener;
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('usuario', EntityType::class, [
                    'required' => true,
                    'class' => Usuarios::class,
                    'choice_label' => 'nome',
                    'choice_value' => 'id',
                    'choices' => [],
                    //'query_builder' => $this->cidadesMapper->select(array(), 'nome', 'query_builder'),
                    'placeholder' => false,
                    'label' => 'Usuário',
                    'attr' => [                        
                        'class' => 'rtlMultiSelect',
                        'data-url' => $this->urlGenerator->generate('api-usuarios', []), 
                    ]
                ])
                ->add('apelido', null, [
                    'required' => true,
                    'attr' => [
                        //'autofocus' => 'autofocus',
                    ],
                ])
                ->add('mandato', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'required' => true,
                    'choices' => \App\Services\Constants::candidatosMandatosChoiceArray(),
                    'placeholder' => false,
                ])
                ->add('numero', null, [
                    'required' => true,
                    'attr' => [
                        //'autofocus' => 'autofocus',
                    ],
                ])
                ->add('info', TextareaType::class, [
                    'required' => true,
                    'attr' => [
                        //'autofocus' => 'autofocus',
                    ],
                ])
        ;
        
        $builder->get('usuario')->addEventSubscriber($this->usuariosEventListener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidatos::class,
        ]);
    }
}
