<?php

namespace App\Form;

use App\Mapper\LoginsMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginsPesquisaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'E-mail'
                ]
            ])
            ->add('nome', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nome'
                ]
            ])
            ->add('role', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'choices' => LoginsMapper::rolesChoiceArray('Todos os Níveis de Acesso')
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
                
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Outras opções aqui!!!
        ]);
    }
    
}
