<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Admin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Service\ConfiguratorFactory;
use DrSoftFr\Module\ProductWizard\Service\ConfiguratorValidatorService;
use DrSoftFr\Module\ProductWizard\UI\Admin\Normalizer\ConfiguratorNormalizer;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Language\ValueObject\LanguageId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
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

    public function __construct(
        private readonly int $languageId,
        private readonly int $shopId
    )
    {
        parent::__construct();
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
     * @param ConfiguratorRepositoryInterface $repository
     *
     * @return JsonResponse
     */
    public function getAction(
        Request                         $request,
        ConfiguratorNormalizer          $normalizer,
        ConfiguratorRepositoryInterface $repository
    ): JsonResponse
    {
        $configuratorId = $request->query->get('configuratorId');

        if (empty($configuratorId)) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid request data',
            ]);
        }

        /** @var Configurator $configurator */
        $configurator = $repository->find($configuratorId);

        if (null === $configurator) {
            return $this->json([
                'success' => false,
                'message' => 'Configurator not found',
            ]);
        }

        $dto = ConfiguratorDto::fromEntity($configurator);

        return $this->json([
            'success' => true,
            'configurator' => $normalizer->normalize($dto),
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
     * @param ConfiguratorNormalizer $normalizer
     * @param ConfiguratorFactory $factory
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function saveAction(
        Request                $request,
        ConfiguratorNormalizer $normalizer,
        ConfiguratorFactory    $factory,
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

        $dto = $normalizer->denormalize($data['configurator']);

        try {
            $validator = new ConfiguratorValidatorService();

            $validator->validate($dto);
        } catch (\Throwable $t) {
            return $this->json([
                'success' => false,
                'message' => $t->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $em->beginTransaction();

            $configurator = $factory->createOrUpdateFromDto($dto);

            $em->flush();
            $em->commit();

            $dto = ConfiguratorDto::fromEntity($configurator);

            return $this->json([
                'success' => true,
                'message' => $dto->id ? 'Configurator created successfully' : 'Configurator updated successfully',
                'configurator' => $normalizer->normalize($dto),
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
                        'name' => $p['name'],
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
     * Get product
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
    public function getProductAction(Request $request, ProductRepository $repository): JsonResponse
    {
        try {
            $productId = $request->query->getInt('product-id');
            $product = $repository->get(new ProductId($productId), new ShopId($this->shopId));
            $productData = [
                'id' => $product->id,
                'name' => $product->name[$this->languageId],
            ];

            return $this->json([
                'success' => true,
                'product' => $productData
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error fetching product: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
