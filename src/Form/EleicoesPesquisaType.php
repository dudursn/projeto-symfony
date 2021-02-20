<?php

namespace App\Form;

use App\Mapper\EleicoesMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EleicoesPesquisaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nome', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nome'
                ]
            ])
            ->add('doc', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'CPF'
                ]
            ])
            ->add('whatsapp', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Whatsapp',
                    'class' => 'maskAsTel',
                ]
            ])
            /*
            ->add('role', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices' => EleicoesMapper::rolesChoiceArray('Todos os Níveis de Acesso')
                ])
            ->add('ativo', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'choices' => ['Todos' => null, 'Ativo' => 1, 'Desativados' => 2]
                ])
            ->add('confirmado', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'choices' => ['Todos' => null, 'Confirmados' => 1, 'Não Confirmados' => 2]
                ])
                */
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Outras opções aqui!!!
        ]);
    }
    
}
