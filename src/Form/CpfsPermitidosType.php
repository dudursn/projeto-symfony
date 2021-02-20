<?php

namespace App\Form;

use App\Entity\Logins;
use App\Mapper\LoginsMapper;
use Symfony\Component\Form\AbstractType;
use App\Form\DataTransformer\IntToCpfCnpjTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CpfsPermitidosType extends AbstractType
{
    private $intToCpfCnpjTransformer;
    
    public function __construct(IntToCpfCnpjTransformer $intToCpfCnpj) {
        $this->intToCpfCnpjTransformer = $intToCpfCnpj;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('cpf', null, [
                    'required' => true,
                    'label' => 'CPF',
                    'attr' => [
                        'class' => 'maskAsCpf',
                        'autofocus' => 'autofocus'
                    ]
                ])
            ;
        
        $builder->get('cpf')->addModelTransformer($this->intToCpfCnpjTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\CpfsPermitidos::class,
        ]);
    }
}
