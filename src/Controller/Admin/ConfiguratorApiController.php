<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Admin;

use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConfiguratorApiController.
 *
 * @ModuleActivated(moduleName="drsoftfrproductwizard", redirectRoute="admin_module_manage")
 */
final class ConfiguratorApiController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminDrSoftFrProductWizardConfiguratorApi';

    /**
     * @var int
     */
    private $languageId;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(int $languageId, int $shopId)
    {
        parent::__construct();

        $this->languageId = $languageId;
        $this->shopId = $shopId;
    }

    /**
     * Get Configurator Data
     *
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to read this."
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request): JsonResponse
    {
        $configuratorId = $request->query->get('configuratorId');

        if (empty($configuratorId)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid request data',
            ]);
        }

        $configurator = $this->getConfiguratorRepository()->find($configuratorId);

        if (null === $configurator) {
            return $this->json([
                'success' => false,
                'message' => 'Configurator not found',
            ]);
        }

        return $this->json([
            'success' => true,
            'configurator' => $configurator->toArray(),
        ]);
    }

    /**
     * @return ConfiguratorRepository
     */
    private function getConfiguratorRepository(): ConfiguratorRepository
    {
        /** @type ConfiguratorRepository */
        return $this->get('drsoft_fr.module.product_wizard.repository.configurator_repository');
    }
}
