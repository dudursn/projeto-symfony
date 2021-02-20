<?php

namespace App\Form;

use App\Mapper\LoginsMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CpfsPermitidosPesquisaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cpf', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Número do CPF'
                ]
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
