<?php

namespace DrSoftFr\Module\ProductWizard\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ConditionType extends AbstractType
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
            ]);
    }
}
