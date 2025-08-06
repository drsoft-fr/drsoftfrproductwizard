<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Exception\Configurator\ConfiguratorNotFoundException;
use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Language\ValueObject\LanguageId;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * Save Configurator
     *
     * @AdminSecurity(
     *     "is_granted(['update', 'create'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to save this."
     * )
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function saveAction(
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['configurator'])) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid request data',
            ], Response::HTTP_BAD_REQUEST);
        }

        $configuratorData = $data['configurator'];
        $isNew = empty($configuratorData['id']);

        try {
            $em->beginTransaction();

            if ($isNew) {
                $configurator = new Configurator();
            } else {
                $configurator = $this->getConfiguratorRepository()->find($configuratorData['id']);

                if (!$configurator) {
                    throw new ConfiguratorNotFoundException('Configurator not found');
                }
            }

            $configurator->setName($configuratorData['name']);
            $configurator->setActive($configuratorData['active']);

            if ($isNew) {
                $em->persist($configurator);
                $em->flush(); // Flush to get ID
            }

            // TODO save Steps

            $em->flush();
            $em->commit();

            return $this->json([
                'success' => true,
                'message' => $isNew ? 'Configurator created successfully' : 'Configurator updated successfully',
                'configurator' => $configurator->toArray(),
                'route' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_edit', [
                    'id' => $configurator->getId(),
                ])
            ]);
        } catch (\Throwable $t) {
            $em->rollback();

            return $this->json([
                'success' => false,
                'message' => 'Error saving configurator: ' . $t->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search products by name
     *
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to access this."
     * )
     *
     * @param Request $request
     * @param ProductRepository $repository
     *
     * @return JsonResponse
     */
    public function productSearchAction(Request $request, ProductRepository $repository): JsonResponse
    {
        try {
            $q = $request->query->get('q');

            if (empty($q)) {
                return $this->json([
                    'success' => true,
                    'items' => []
                ]);
            }

            $results = $repository->searchProducts(pSQL($q), new LanguageId($this->languageId), new ShopId($this->shopId), 20);

            return $this->json([
                'success' => true,
                'items' => array_map(function ($p) {
                    return [
                        'id' => $p['id_product'],
                        'text' => $p['name'],
                        'image_url' => $this->getProductImageUrl($p['id_product']),
                        'price' => $p['price_amount'] ?? null
                    ];
                }, $results)
            ]);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' => 'Error searching products: ' . $t->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get product image URL
     *
     * @param int $productId
     *
     * @return string|null
     */
    private function getProductImageUrl(int $productId): ?string
    {
        try {
            $link = $this->get('prestashop.link');

            return $link->getImageLink('product', $productId, 'home_default');
        } catch (\Throwable $t) {
            return null;
        }
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
