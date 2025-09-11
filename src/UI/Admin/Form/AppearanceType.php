<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Admin\Form;

use PrestaShop\PrestaShop\Core\ConstraintValidator\Constraints\CleanHtml;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class AppearanceType extends TranslatorAwareType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hexConstraints = [
            new Assert\NotBlank(['message' => 'This color is required.']),
            new Assert\Regex([
                'pattern' => '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
                'message' => $this->trans(
                    'Invalid format. Use a hexadecimal code (#RGB or #RRGGBB).',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
            ]),
        ];
        $cssLengthConstraints = [
            new Assert\NotBlank(['message' => 'This value is required.']),
            new Assert\Regex([
                'pattern' => '/^(?:0|(?:\d+(?:\.\d+)?)(?:px|rem|em|%))$/',
                'message' => $this->trans(
                    'Invalid CSS value. Use 0 or a positive number followed by px, rem, em, or %.',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
            ]),
            new CleanHtml([
                'message' => $this->trans(
                    'This value must contain clean HTML',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
            ]),
        ];
        $builder
            ->add('color_base_100', ColorType::class, [
                'label' => $this->trans(
                    'Base color 100',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#ffffff'],
                'constraints' => $hexConstraints,
                'help' => '--color-base-100',
            ])
            ->add('color_base_200', ColorType::class, [
                'label' => $this->trans(
                    'Base color 200',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#f5f5f5'],
                'constraints' => $hexConstraints,
                'help' => '--color-base-200',
            ])
            ->add('color_base_300', ColorType::class, [
                'label' => $this->trans(
                    'Base color 300',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#e6e6e6'],
                'constraints' => $hexConstraints,
                'help' => '--color-base-300',
            ])
            ->add('color_base_content', ColorType::class, [
                'label' => $this->trans(
                    'Base content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#222222'],
                'constraints' => $hexConstraints,
                'help' => '--color-base-content',
            ])
            ->add('color_primary', ColorType::class, [
                'label' => $this->trans(
                    'Primary color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#00a19a'],
                'constraints' => $hexConstraints,
                'help' => '--color-primary',
            ])
            ->add('color_primary_content', ColorType::class, [
                'label' => $this->trans(
                    'Primary content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#1f3a3a'],
                'constraints' => $hexConstraints,
                'help' => '--color-primary-content',
            ])
            ->add('color_secondary', ColorType::class, [
                'label' => $this->trans(
                    'Secondary color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#d34b9b'],
                'constraints' => $hexConstraints,
                'help' => '--color-secondary',
            ])
            ->add('color_secondary_content', ColorType::class, [
                'label' => $this->trans(
                    'Secondary content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#5a1f4a'],
                'constraints' => $hexConstraints,
                'help' => '--color-secondary-content',
            ])
            ->add('color_accent', ColorType::class, [
                'label' => $this->trans(
                    'Accent color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#6dd6a8'],
                'constraints' => $hexConstraints,
                'help' => '--color-accent',
            ])
            ->add('color_accent_content', ColorType::class, [
                'label' => $this->trans(
                    'Accent content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#2f4a3a'],
                'constraints' => $hexConstraints,
                'help' => '--color-accent-content',
            ])
            ->add('color_neutral', ColorType::class, [
                'label' => $this->trans(
                    'Neutral color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#222222'],
                'constraints' => $hexConstraints,
                'help' => '--color-neutral',
            ])
            ->add('color_neutral_content', ColorType::class, [
                'label' => $this->trans(
                    'Neutral content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#ffffff'],
                'constraints' => $hexConstraints,
                'help' => '--color-neutral-content',
            ])
            ->add('color_info', ColorType::class, [
                'label' => $this->trans(
                    'Info color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#3b82f6'],
                'constraints' => $hexConstraints,
                'help' => '--color-info',
            ])
            ->add('color_info_content', ColorType::class, [
                'label' => $this->trans(
                    'Info content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#e8f1ff'],
                'constraints' => $hexConstraints,
                'help' => '--color-info-content',
            ])
            ->add('color_success', ColorType::class, [
                'label' => $this->trans(
                    'Success color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#22c55e'],
                'constraints' => $hexConstraints,
                'help' => '--color-success',
            ])
            ->add('color_success_content', ColorType::class, [
                'label' => $this->trans(
                    'Success content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#f0fff4'],
                'constraints' => $hexConstraints,
                'help' => '--color-success-content',
            ])
            ->add('color_warning', ColorType::class, [
                'label' => $this->trans(
                    'Warning color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#f59e0b'],
                'constraints' => $hexConstraints,
                'help' => '--color-warning',
            ])
            ->add('color_warning_content', ColorType::class, [
                'label' => $this->trans(
                    'Warning content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#fff7e6'],
                'constraints' => $hexConstraints,
                'help' => '--color-warning-content',
            ])
            ->add('color_error', ColorType::class, [
                'label' => $this->trans(
                    'Error color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#ef4444'],
                'constraints' => $hexConstraints,
                'help' => '--color-error',
            ])
            ->add('color_error_content', ColorType::class, [
                'label' => $this->trans(
                    'Error content color',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'attr' => ['placeholder' => '#ffebeb'],
                'constraints' => $hexConstraints,
                'help' => '--color-error-content',
            ])
            ->add('radius_selector', TextType::class, [
                'label' => $this->trans(
                    'Selector radius',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'empty_data' => '2rem',
                'attr' => ['placeholder' => '2rem'],
                'constraints' => $cssLengthConstraints,
                'help' => '--radius-selector (checkbox, toggle, badge)',
            ])
            ->add('radius_field', TextType::class, [
                'label' => $this->trans(
                    'Field radius',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'empty_data' => '2rem',
                'attr' => ['placeholder' => '2rem'],
                'constraints' => $cssLengthConstraints,
                'help' => '--radius-field (button, input, select, tab)',
            ])
            ->add('radius_box', TextType::class, [
                'label' => $this->trans(
                    'Box radius',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'empty_data' => '0.5rem',
                'attr' => ['placeholder' => '0.5rem'],
                'constraints' => $cssLengthConstraints,
                'help' => '--radius-box (card, modal, alert)',
            ])
            ->add('size_selector', TextType::class, [
                'label' => $this->trans(
                    'Selector size',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'empty_data' => '0.25rem',
                'attr' => ['placeholder' => '0.25rem'],
                'constraints' => $cssLengthConstraints,
                'help' => '--size-selector (checkbox, toggle, badge)',
            ])
            ->add('size_field', TextType::class, [
                'label' => $this->trans(
                    'Field size',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'empty_data' => '0.25rem',
                'attr' => ['placeholder' => '0.25rem'],
                'constraints' => $cssLengthConstraints,
                'help' => '--size-field (button, input, select, tab)',
            ])
            ->add('border', TextType::class, [
                'label' => $this->trans(
                    'Border width',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => true,
                'empty_data' => '1px',
                'attr' => ['placeholder' => '1px'],
                'constraints' => $cssLengthConstraints,
                'help' => '--border (All components)',
            ])
            ->add('depth', SwitchType::class, [
                'label' => $this->trans(
                    'Depth Effect',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => false,
                'empty_data' => false,
                'attr' => ['min' => 0],
                'constraints' => [new Assert\Type(['type' => 'bool', 'message' => 'Cette valeur doit être booléenne.'])],
                'help' => '--depth (3D depth on fields & selectors)',
            ])
            ->add('noise', SwitchType::class, [
                'label' => $this->trans(
                    'Noise Effect',
                    'Modules.Drsoftfrproductwizard.Admin'
                ),
                'required' => false,
                'empty_data' => false,
                'constraints' => [new Assert\Type(['type' => 'bool', 'message' => 'Cette valeur doit être booléenne.'])],
                'help' => '--noise (Noise pattern on fields & selectors)',
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'drsoft_fr_product_wizard_appearance';
    }
}
