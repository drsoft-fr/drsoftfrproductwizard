<?php

namespace DrSoftFr\Module\ProductWizard\Form;

use DrSoftFr\Module\ProductWizard\Entity\Step;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de l’étape',
                'attr' => [
                    'x-sync-label' => ''
                ],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre',
            ])
            ->add('active', SwitchType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('productChoices', CollectionType::class, [
                'entry_type' => ProductChoiceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Choix produits',
                'prototype' => true,
                'prototype_name' => '__choice__',
            ])
            ->add('remove', ButtonType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-danger btn-sm',
                    '@click' => '$el.closest(\'.step-block\').remove()',
                    'title' => 'Supprimer cette étape',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $step = $event->getData();

            if ($step === null) {
                $step = new Step();
                $step->setLabel('Nouvelle étape');
                $step->setPosition(1);
                $step->setActive(true);
                $event->setData($step);
                return;
            }

            if ($step->getId() === null) {
                if (method_exists($step, 'getLabel') && $step->getLabel() === null) {
                    $step->setLabel('Nouvelle étape');
                }

                if (method_exists($step, 'getPosition') && $step->getPosition() === null) {
                    $step->setPosition(1);
                }

                if (method_exists($step, 'getActive') && $step->getActive() === null) {
                    $step->setActive(true);
                }
            }
        });


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Step::class,
            'empty_data' => function () {
                $step = new Step();
                $step->setLabel('Nouvelle étape');
                $step->setPosition(1);
                $step->setActive(true);
                return $step;
            },

        ]);
    }
}
