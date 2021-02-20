<?php

namespace App\Form;

use App\Entity\Logins;
use App\Entity\Eleicoes;
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

class EleicoesType extends AbstractType
{
    //private $loginsEventListener;
    //private $urlGenerator;
    //private $intToCpfCnpjTransformer;
    
    public function __construct(LoginsEventListener $loginsEventListener, UrlGeneratorInterface $urlGenerator, IntToCpfCnpjTransformer $intToCpfCnpj) {
        //$this->loginsEventListener = $loginsEventListener;
        //$this->urlGenerator = $urlGenerator;
        //$this->intToCpfCnpjTransformer = $intToCpfCnpj;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('ano', null, [
                    'required' => true,
                    'attr' => [
                        'autofocus' => 'autofocus',
                    ],
                ])
                ->add('descricao', null, [
                    'required' => true,
                    'label' => 'Descrição',
                    
                ])
                ->add('votacao_inicio', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'label' => 'Início das Votações',
                    'attr' => [
                        'class' => 'maskAsDate',
                    ],
                ])
                ->add('votacao_fim', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'label' => 'Término das Votações',
                    'attr' => [
                        'class' => 'maskAsDate',
                    ],
                ])
                ->add('apuracao_data', DateType::class, [
                    'required' => true,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'label' => 'Data de Apuração',
                    'attr' => [
                        'class' => 'maskAsDate',
                    ],
                ])
                ->add('ativo', null, [
                    'label' => 'Eleição ativa',
                    'attr' => [
                        //'class' => 'maskAsDate',
                    ],
                ])
        ;
        
        //$builder->get('login')->addEventSubscriber($this->loginsEventListener);
        //$builder->get('doc')->addModelTransformer($this->intToCpfCnpjTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Eleicoes::class,
        ]);
    }
}
