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
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom de l’étape',
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
                'prototype_name' => '__step__',
            ])
            ->add('remove', ButtonType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-danger btn-sm',
                    '@click' => '$el.closest(\'.step-block\').remove()',
                    'title' => 'Supprimer cette étape',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Step::class,
        ]);
    }
}
