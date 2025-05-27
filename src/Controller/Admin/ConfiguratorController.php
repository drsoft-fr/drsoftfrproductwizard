<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Admin;

use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use DrsoftFrProductWizard;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConfiguratorController.
 *
 * @ModuleActivated(moduleName="drsoftfrproductwizard", redirectRoute="admin_module_manage")
 */
final class ConfiguratorController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminDrSoftFrProductWizardConfigurator';
    const PAGE_INDEX_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_index';
    const TEMPLATE_FOLDER = '@Modules/drsoftfrproductwizard/views/templates/admin/configurator/';

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        return $this->render(self::TEMPLATE_FOLDER . 'index.html.twig', [
            'configurators' => $this->getRepository()->findAll(),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'module' => $this->getModule(),
        ]);
    }

    /**
     * @return DrsoftFrProductWizard
     */
    protected function getModule(): DrsoftFrProductWizard
    {
        /** @type DrsoftFrProductWizard */
        return $this->get('drsoft_fr.module.product_wizard.module');
    }

    /**
     * @return ConfiguratorRepository
     */
    private function getRepository(): ConfiguratorRepository
    {
        /** @type ConfiguratorRepository */
        return $this->get('drsoft_fr.module.product_wizard.repository.configurator_repository');
    }
}
