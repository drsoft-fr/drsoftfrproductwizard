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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
                'attr' => [
                    'x-model' => '$store.wizardData.data.name',
                ],
            ])
            ->add('active', SwitchType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => [
                    'x-model' => '$store.wizardData.data.active',
                ],
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

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                /** @var Configurator $configurator */
                $configurator = $event->getData();
                $form = $event->getForm();

                $stepMap = [];
                foreach ($configurator->getSteps() as $step) {
                    $stepMap[$step->getId()] = $step;
                }

                foreach ($configurator->getSteps() as $step) {
                    $currentStepPosition = $step->getPosition();

                    foreach ($step->getProductChoices() as $productChoice) {
                        $displayConditions = $productChoice->getDisplayConditions() ?: [];

                        foreach ($displayConditions as $condIdx => $cond) {
                            $condStepId = $cond['step'] ?? null;
                            $condChoiceId = $cond['choice'] ?? null;

                            if (!$condStepId || !isset($stepMap[$condStepId])) {
                                $form->addError(new FormError(
                                    "L'étape référencée dans une condition n'existe plus."
                                ));
                                continue;
                            }
                            $condStep = $stepMap[$condStepId];

                            if ($condStep->getPosition() >= $currentStepPosition) {
                                $form->addError(new FormError(
                                    "Condition invalide : l'étape référencée n'est plus antérieure à l'étape courante."
                                ));
                            }

                            $found = false;
                            foreach ($condStep->getProductChoices() as $choice) {
                                if ($choice->getId() == $condChoiceId) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $form->addError(new FormError(
                                    "Le choix référencé dans une condition n'existe plus."
                                ));
                            }
                        }
                    }
                }
            }
        );
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
