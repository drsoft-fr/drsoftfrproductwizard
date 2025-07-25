<?php

namespace DrSoftFr\Module\ProductWizard\Form;

use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductChoiceType extends AbstractType
{
    /**
     * @var int
     */
    private $languageId;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(
        int $languageId,
        int $shopId
    )
    {
        $this->languageId = $languageId;
        $this->shopId = $shopId;
    }

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
                'attr' => [
                    '@change' => 'uncheckOthers($event)'
                ],
            ])
            ->add('allowQuantity', CheckboxType::class, [
                'label' => 'Quantité modifiable par le client',
                'required' => false,
            ])
            ->add('forcedQuantity', IntegerType::class, [
                'label' => 'Quantité imposée (optionnel, vide = non imposée)',
                'required' => false,
            ])
            ->add('displayConditions', CollectionType::class, [
                'entry_type' => ConditionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'label' => 'Conditions d\'affichage',
                'prototype' => true,
                'prototype_name' => '__cond__',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('remove', ButtonType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'class' => 'btn btn-link text-danger btn-sm p-0',
                    'title' => 'Supprimer ce choix',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $productChoice = $event->getData();
            $form = $event->getForm();
            $productName = '';

            if ($productChoice && $productChoice->getProductId()) {
                $product = new \Product($productChoice->getProductId(), false, $this->languageId, $this->shopId);

                if (true === \Validate::isLoadedObject($product)) {
                    $productName = $product->name;
                }
            }

            $form->add('productId', IntegerType::class, [
                'label' => 'ID produit PrestaShop (optionnel)',
                'required' => false,
                'attr' => [
                    'data-product-name' => $productName,
                ],
            ]);

            if ($productChoice === null) {
                $productChoice = new ProductChoice();
                $productChoice->setLabel('Nouveau choix');
                $productChoice->setActive(true);
                $productChoice->setIsDefault(false);
                $productChoice->setAllowQuantity(true);
                $event->setData($productChoice);
                return;
            }

            if (method_exists($productChoice, 'getId') && $productChoice->getId() === null) {
                if (method_exists($productChoice, 'getLabel') && $productChoice->getLabel() === null) {
                    $productChoice->setLabel('Nouveau choix');
                }

                if (method_exists($productChoice, 'getActive') && $productChoice->getActive() === null) {
                    $productChoice->setActive(true);
                }

                if (method_exists($productChoice, 'getIsDefault') && $productChoice->getIsDefault() === null) {
                    $productChoice->setIsDefault(false);
                }

                if (method_exists($productChoice, 'getAllowQuantity') && $productChoice->getAllowQuantity() === null) {
                    $productChoice->setAllowQuantity(true);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductChoice::class,
            'empty_data' => function () {
                $productChoice = new ProductChoice();
                $productChoice->setLabel('Nouveau choix');
                $productChoice->setActive(true);
                $productChoice->setIsDefault(false);
                $productChoice->setAllowQuantity(true);
                return $productChoice;
            },
        ]);
    }
}
