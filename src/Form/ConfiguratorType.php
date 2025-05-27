<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Form;

use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents a form type for creating and editing configurator.
 *
 * @final
 */
final class ConfiguratorType extends TranslatorAwareType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du scénario',
            ])
            ->add('active', SwitchType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('steps', CollectionType::class, [
                'entry_type' => StepType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Étapes',
                'prototype' => true,
                'prototype_name' => '__step__',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configurator::class,
        ]);
    }
}
