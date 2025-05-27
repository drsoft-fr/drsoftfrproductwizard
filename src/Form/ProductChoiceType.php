<?php


namespace DrSoftFr\Module\ProductWizard\Form;

use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Nom du choix',
            ])
            ->add('productId', IntegerType::class, [
                'label' => 'ID produit PrestaShop (optionnel)',
                'required' => false,
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'Choix par défaut',
                'required' => false,
            ])
            ->add('allowQuantity', CheckboxType::class, [
                'label' => 'Quantité modifiable par le client',
                'required' => false,
            ])
            ->add('forcedQuantity', IntegerType::class, [
                'label' => 'Quantité imposée (optionnel, vide = non imposée)',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductChoice::class,
        ]);
    }
}
