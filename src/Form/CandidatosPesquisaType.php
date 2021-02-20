<?php

namespace App\Form;

use App\Mapper\CandidatosMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatosPesquisaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('apelido', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Apelido'
                ]
            ])
            ->add('numero', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Número'
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
