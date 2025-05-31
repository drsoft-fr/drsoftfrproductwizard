<?php

namespace DrSoftFr\Module\ProductWizard\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class ConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('step', IntegerType::class, [
                'label' => 'Étape',
                'required' => true,
            ])
            ->add('choice', IntegerType::class, [
                'label' => 'Choix',
                'required' => true,
            ])
            ->add('remove', ButtonType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-link text-danger p-0 ms-1',
                    'title' => 'Supprimer cette condition',
                ],
            ]);
    }
}
