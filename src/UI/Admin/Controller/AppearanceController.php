<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Admin\Controller;

use DrSoftFr\Module\ProductWizard\Infrastructure\Configuration\AppearanceConfiguration;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use drsoftfrproductwizard;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @ModuleActivated(moduleName="drsoftfrproductwizard", redirectRoute="admin_module_manage")
 */
final class AppearanceController extends FrameworkBundleAdminController
{
    public const TAB_CLASS_NAME = 'AdminDrSoftFrProductWizardAppearance';
    private const PAGE_INDEX_ROUTE = 'admin_drsoft_fr_product_wizard_appearance_index';
    private const TEMPLATE_FOLDER = '@Modules/drsoftfrproductwizard/src/UI/Admin/View/appearance/';
    private const FORM_HANDLER = 'drsoft_fr.module.product_wizard.form.handler.appearance_handler';
    private const APPEARANCE_CONFIGURATION = 'drsoft_fr.module.product_wizard.infrastructure.configuration.appearance_configuration';
    private Package $manifest;

    public function __construct(
        private readonly drsoftfrproductwizard $module
    )
    {
        parent::__construct();

        $this->manifest = new Package(
            new JsonManifestVersionStrategy(
                _PS_MODULE_DIR_ . '/drsoftfrproductwizard/views/.vite/manifest.json'
            )
        );
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read','update'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="Access denied."
     * )
     */
    public function indexAction(Request $request): Response
    {
        $handler = $this->getFormHandler();
        $form = $handler->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $errors = $handler->save($form->getData());

                if (false === empty($errors)) {
                    $this->flashErrors($errors);
                } else {
                    $this->addFlash(
                        'success',
                        $this->trans(
                            'The settings have been successfully updated.',
                            'Modules.Drsoftfrproductwizard.Success'
                        )
                    );

                    $this->createCssFile();
                }
            } catch (Throwable $t) {
                $this->addFlash(
                    'error',
                    $this->trans(
                        'Cannot save the setting. Throwable: #%code% - %message%',
                        'Modules.Drsoftfrproductwizard.Error',
                        [
                            '%code%' => $t->getCode(),
                            '%message%' => $t->getMessage(),
                        ]
                    )
                );
            }

            return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
        }

        return $this->render(self::TEMPLATE_FOLDER . 'index.html.twig', [
            'enableSidebar' => true,
            'drsoft_fr_product_wizard_appearance_form' => $form->createView(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'module' => $this->module,
            'manifest' => $this->manifest,
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to reset this."
     * )
     *
     * @return RedirectResponse
     */
    public function resetAction(): RedirectResponse
    {
        try {
            $this
                ->getAppearanceConfiguration()
                ->initConfiguration();

            $this->addFlash(
                'success',
                $this->trans(
                    'The default setting are reset.',
                    'Modules.Drsoftfrproductwizard.Admin'
                )
            );

            $this->createCssFile();
        } catch (Throwable $t) {
            $this->addFlash(
                'error',
                $this->trans(
                    'Cannot reset the setting. Exception: #%code% - %message%',
                    'Modules.Drsoftfrproductwizard.Error',
                    [
                        '%code%' => $t->getCode(),
                        '%message%' => $t->getMessage(),
                    ]
                )
            );
        }

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    private function createCssFile(): void
    {
        $configuration = $this->getAppearanceConfiguration()->getConfiguration();

        $configuration['depth'] = (int)$configuration['depth'];
        $configuration['noise'] = (int)$configuration['noise'];

        $cssFile = _PS_MODULE_DIR_ . $this->module->name . '/views/css/custom.css';

        $text = "
            .product-wizard-container {
                --color-base-100: {$configuration['color_base_100']};
                --color-base-200: {$configuration['color_base_200']};
                --color-base-300: {$configuration['color_base_300']};
                --color-base-content: {$configuration['color_base_content']};
                --color-primary: {$configuration['color_primary']};
                --color-primary-content: {$configuration['color_primary_content']};
                --color-secondary: {$configuration['color_secondary']};
                --color-secondary-content: {$configuration['color_secondary_content']};
                --color-accent: {$configuration['color_accent']};
                --color-accent-content: {$configuration['color_accent_content']};
                --color-neutral: {$configuration['color_neutral']};
                --color-neutral-content: {$configuration['color_neutral_content']};
                --color-info: {$configuration['color_info']};
                --color-info-content: {$configuration['color_info_content']};
                --color-success: {$configuration['color_success']};
                --color-success-content: {$configuration['color_success_content']};
                --color-warning: {$configuration['color_warning']};
                --color-warning-content: {$configuration['color_warning_content']};
                --color-error: {$configuration['color_error']};
                --color-error-content: {$configuration['color_error_content']};
                --radius-selector: {$configuration['radius_selector']};
                --radius-field: {$configuration['radius_field']};
                --radius-box: {$configuration['radius_box']};
                --size-selector: {$configuration['size_selector']};
                --size-field: {$configuration['size_field']};
                --border: {$configuration['border']};
                --depth: {$configuration['depth']};
                --noise: {$configuration['noise']};
            }
        ";

        file_put_contents($cssFile, $text);
    }

    protected function getAppearanceConfiguration(): AppearanceConfiguration
    {
        /** @type AppearanceConfiguration */
        return $this->get(self::APPEARANCE_CONFIGURATION);
    }

    protected function getFormHandler(): FormHandlerInterface
    {
        /** @type FormHandlerInterface */
        return $this->get(self::FORM_HANDLER);
    }
}
