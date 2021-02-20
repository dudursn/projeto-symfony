<?php

namespace App\Form;

use App\Entity\Logins;
use App\Mapper\LoginsMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginsType extends AbstractType
{
    public function __construct() {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('email', null, [
                    'label' => 'E-mail',
                ])
                ->add('nome')
                ->add('pass', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'required' => false,
                    'data' => '',
                    'invalid_message' => 'O valor do campo Confirme a Senha precisar ser igual ao valor do campo Senha.',
                    'first_options'  => [
                        'label' => 'Senha',
                        'help' => 'Deixe em branco para NÃO alterar a senha anterior',
                    ],
                    'second_options' => ['label' => 'Confirme a Senha'],                    
                    //'options' => ['attr' => ['class' => 'password-field']],
                ])
                ->add('role', ChoiceType::class, [
                    'choices' => LoginsMapper::rolesChoiceArray('Selecione um nível de acesso')
                ])
                ->add('confirmado')
                ->add('ativo');
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Logins::class,
        ]);
    }
}
