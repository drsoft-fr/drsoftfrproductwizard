<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Admin\Controller;

use DrSoftFr\Module\ProductWizard\Application\Port\Security\HtmlSanitizerInterface;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\DisplayCondition;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\QuantityRule;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\ReductionType;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Entity\Step;
use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use DrSoftFr\Module\ProductWizard\UI\Admin\Grid\Filters\ConfiguratorFilters;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use drsoftfrproductwizard;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShop\PrestaShop\Core\Grid\GridInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @ModuleActivated(moduleName="drsoftfrproductwizard", redirectRoute="admin_module_manage")
 */
final class ConfiguratorController extends FrameworkBundleAdminController
{
    const TAB_CLASS_NAME = 'AdminDrSoftFrProductWizardConfigurator';
    private const PAGE_INDEX_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_index';
    private const PAGE_CREATE_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_create';
    private const PAGE_API_GET_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_api_get';
    private const PAGE_API_SAVE_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_api_save';
    private const PAGE_API_PRODUCT_SEARCH_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_api_product_search';
    private const PAGE_API_PRODUCT_ROUTE = 'admin_drsoft_fr_product_wizard_configurator_api_product';
    private const TEMPLATE_FOLDER = '@Modules/drsoftfrproductwizard/src/UI/Admin/View/configurator/';
    private const BULK_PARAMETER_NAME = 'drsoft_fr_product_wizard_configurator_grid_bulk';
    private const GRID_CONFIGURATOR_FACTORY = 'drsoft_fr.module.product_wizard.grid.configurator_factory';

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
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to edit this."
     * )
     */
    public function bulkEnableAction(
        Request                         $request,
        ConfiguratorRepositoryInterface $repository
    ): RedirectResponse
    {
        $ids = $request->request->get(self::BULK_PARAMETER_NAME);;

        /** @var Configurator[] $configurators */
        $objs = $repository->findById($ids);

        if (true === empty($objs)) {
            return $this->cannotFindObjRedirect();
        }

        foreach ($objs as $obj) {
            $obj->setActive(true);
        }

        $repository->save();
        $this->addFlash(
            'success',
            $this->trans(
                'The selection has been successfully enabled.',
                'Modules.Drsoftfrproductwizard.Success'
            )
        );

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to edit this."
     * )
     */
    public function bulkDisableAction(
        Request                         $request,
        ConfiguratorRepositoryInterface $repository
    ): RedirectResponse
    {
        $ids = $request->request->get(self::BULK_PARAMETER_NAME);;

        /** @var Configurator[] $configurators */
        $objs = $repository->findById($ids);

        if (true === empty($objs)) {
            return $this->cannotFindObjRedirect();
        }

        foreach ($objs as $obj) {
            $obj->setActive(false);
        }

        $repository->save();
        $this->addFlash(
            'success',
            $this->trans(
                'The selection has been successfully disabled.',
                'Modules.Drsoftfrproductwizard.Success'
            )
        );

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to delete this."
     * )
     */
    public function bulkDeleteAction(
        Request                         $request,
        ConfiguratorRepositoryInterface $repository
    ): RedirectResponse
    {
        $ids = $request->request->get(self::BULK_PARAMETER_NAME);;

        /** @var Configurator[] $configurators */
        $objs = $repository->findById($ids);

        if (true === empty($objs)) {
            return $this->cannotFindObjRedirect();
        }

        $repository->bulkRemove($objs);
        $this->addFlash(
            'success',
            $this->trans(
                'The selection has been successfully deleted.',
                'Modules.Drsoftfrproductwizard.Success'
            )
        );

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     */
    public function indexAction(
        Request             $request,
        ConfiguratorFilters $filters
    ): Response
    {
        /** @var GridInterface $grid */
        $grid = $this
            ->getGridFactory()
            ->getGrid($filters);

        return $this->render(self::TEMPLATE_FOLDER . 'home/index.html.twig', [
            'drsoft_fr_product_wizard_configurator_grid' => $this->presentGrid($grid),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            'module' => $this->module,
            'manifest' => $this->manifest,
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['create'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to create this."
     * )
     */
    public function createAction(): Response
    {
        $this->defineJsProps();

        return $this->render(self::TEMPLATE_FOLDER . 'form/index.html.twig', [
            'configurator_id' => null,
            'return_url' => $this->generateUrl(self::PAGE_INDEX_ROUTE),
            'module' => $this->module,
            'manifest' => $this->manifest,
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to edit this."
     * )
     */
    public function editAction(Configurator $configurator): Response
    {
        $this->defineJsProps();

        return $this->render(self::TEMPLATE_FOLDER . 'form/index.html.twig', [
            'configurator_id' => $configurator->getId(),
            'return_url' => $this->generateUrl(self::PAGE_INDEX_ROUTE),
            'module' => $this->module,
            'manifest' => $this->manifest,
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to delete this."
     * )
     */
    public function deleteAction(
        Configurator                    $configurator,
        ConfiguratorRepositoryInterface $repository
    ): RedirectResponse
    {
        try {
            $repository->remove($configurator);
            $this->addFlash('success', $this->trans('Deleted scenario', 'Modules.Drsoftfrproductwizard.Success'));
        } catch (Throwable $t) {
            $this->addFlash('error', $this->trans('Error deleting scenario', 'Modules.Drsoftfrproductwizard.Error'));
        } finally {
            return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
        }
    }

    /**
     * @AdminSecurity(
     *     "is_granted('create', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to duplicate this."
     * )
     */
    public function duplicateAction(
        Configurator                    $configurator,
        ConfiguratorRepositoryInterface $repository
    ): RedirectResponse
    {
        try {
            $new = new Configurator();

            $new->setActive(false);
            $new->setName(sprintf('%s (copie)', $configurator->getName()));
            $new->setDescription($configurator->getDescription());
            $new->setReduction($configurator->getReduction());
            $new->setReductionTax($configurator->isReductionTax());
            $new->setReductionType($configurator->getReductionType());

            foreach ($configurator->getSteps() as $step) {
                $newStep = new Step();

                $newStep->setActive($step->isActive());
                $newStep->setLabel($step->getLabel());
                $newStep->setDescription($step->getDescription());
                $newStep->setPosition($step->getPosition());
                $newStep->setReduction($step->getReduction());
                $newStep->setReductionTax($step->isReductionTax());
                $newStep->setReductionType($step->getReductionType());

                $new->addStep($newStep);

                foreach ($step->getProductChoices() as $choice) {
                    $newChoice = new ProductChoice();

                    $newChoice->setActive($choice->isActive());
                    $newChoice->setLabel($choice->getLabel());
                    $newChoice->setDescription($choice->getDescription());
                    $newChoice->setProductId($choice->getProductId());
                    $newChoice->setIsDefault($choice->isDefault());
                    $newChoice->setReduction($choice->getReduction());
                    $newChoice->setReductionTax($choice->isReductionTax());
                    $newChoice->setReductionType($choice->getReductionType());

                    if (null !== $choice->getDisplayConditions()) {
                        $newChoice->setDisplayConditions($choice->getDisplayConditions());
                    }

                    if (null !== $choice->getQuantityRule()) {
                        $newChoice->setQuantityRule($choice->getQuantityRule());
                    }

                    $newStep->addProductChoice($newChoice);
                }
            }

            $repository->add($new);
            $this->addFlash('success', $this->trans('Configurator duplicated successfully', 'Modules.Drsoftfrproductwizard.Success'));
        } catch (Throwable $t) {
            $this->addFlash('error', $this->trans('Error duplicating scenario', 'Modules.Drsoftfrproductwizard.Error'));
        } finally {
            return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
        }
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     */
    public function exportAction(Configurator $configurator): Response
    {
        $data = $configurator->toArray();
        $data = $this->exportNormalizeDates($data);
        $payload = [
            'module' => $this->module->name,
            'entity' => 'configurator',
            'version' => $this->module->version,
            'exported_at' => (new \DateTimeImmutable())->format('c'),
            'data' => $data,
        ];
        $filename = sprintf('configurator-%d-%s.json', $configurator->getId(), preg_replace('/[^A-Za-z0-9_-]+/', '-', $configurator->getName()));
        $response = new JsonResponse($payload, 200, []);

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['create'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to import this."
     * )
     */
    public function importFormAction(): Response
    {
        return $this->render(self::TEMPLATE_FOLDER . 'import/index.html.twig', [
            'return_url' => $this->generateUrl(self::PAGE_INDEX_ROUTE),
            'module' => $this->module,
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted(['create'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to import this."
     * )
     */
    public function importHandleAction(
        Request                         $request,
        ConfiguratorRepositoryInterface $repository,
        HtmlSanitizerInterface          $htmlSanitizer
    ): RedirectResponse
    {
        $file = $request->files->get('import_file');

        if (!$file) {
            $this->addFlash('error', $this->trans('No file uploaded', 'Modules.Drsoftfrproductwizard.Error'));

            return $this->redirectToRoute('admin_drsoft_fr_product_wizard_configurator_import_form');
        }

        try {
            $content = file_get_contents($file->getPathname());
            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['data']) || !is_array($json['data'])) {
                throw new \RuntimeException('Invalid file content');
            }

            $data = $json['data'];

            $new = new Configurator();

            $new->setActive(false);
            $new->setName(strip_tags(($data['name'] ?? 'Imported configurator')));
            $new->setDescription($htmlSanitizer->sanitize($data['description'] ?? null));
            $new->setReduction((float)($data['reduction'] ?? 0));
            $new->setReductionTax((bool)($data['reduction_tax'] ?? true));
            $new->setReductionType((string)($data['reduction_type'] ?? ReductionType::AMOUNT));

            if (!empty($data['steps']) && is_array($data['steps'])) {
                foreach ($data['steps'] as $stepData) {
                    $step = new Step();

                    $step->setActive((bool)($stepData['active'] ?? true));
                    $step->setLabel(strip_tags((string)($stepData['label'] ?? '')));
                    $step->setDescription($htmlSanitizer->sanitize($stepData['description'] ?? null));
                    $step->setPosition((int)($stepData['position'] ?? 0));
                    $step->setReduction((float)($stepData['reduction'] ?? 0));
                    $step->setReductionTax((bool)($stepData['reduction_tax'] ?? true));
                    $step->setReductionType((string)($stepData['reduction_type'] ?? ReductionType::AMOUNT));

                    $new->addStep($step);

                    if (!empty($stepData['product_choices']) && is_array($stepData['product_choices'])) {
                        foreach ($stepData['product_choices'] as $choiceData) {
                            $choice = new ProductChoice();

                            $choice->setActive((bool)($choiceData['active'] ?? true));
                            $choice->setLabel(strip_tags((string)($choiceData['label'] ?? '')));
                            $choice->setDescription($htmlSanitizer->sanitize($choiceData['description'] ?? null));
                            $choice->setProductId(isset($choiceData['product_id']) ? (int)$choiceData['product_id'] : null);
                            $choice->setIsDefault((bool)($choiceData['is_default'] ?? false));
                            $choice->setReduction((float)($choiceData['reduction'] ?? 0));
                            $choice->setReductionTax((bool)($choiceData['reduction_tax'] ?? true));
                            $choice->setReductionType((string)($choiceData['reduction_type'] ?? ReductionType::AMOUNT));

                            // display_conditions as raw arrays to setter expecting DisplayCondition objects
                            if (!empty($choiceData['display_conditions']) && is_array($choiceData['display_conditions'])) {
                                $conditions = [];

                                foreach ($choiceData['display_conditions'] as $cond) {
                                    $conditions[] = DisplayCondition::fromArray($cond ?: []);
                                }

                                $choice->setDisplayConditions($conditions);
                            }

                            if (isset($choiceData['quantity_rule']) && is_array($choiceData['quantity_rule'])) {
                                $choice->setQuantityRule(QuantityRule::fromArray($choiceData['quantity_rule']));
                            }

                            $step->addProductChoice($choice);
                        }
                    }
                }
            }

            $repository->add($new);
            $this->addFlash('success', $this->trans('Configurator imported successfully', 'Modules.Drsoftfrproductwizard.Success'));
        } catch (\Throwable $t) {
            $this->addFlash('error', $this->trans('Error importing configurator', 'Modules.Drsoftfrproductwizard.Error'));
        }

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to toggle the active status of this item."
     * )
     */
    public function toggleActiveAction(
        Configurator                    $configurator,
        ConfiguratorRepositoryInterface $repository
    ): JsonResponse
    {
        try {
            $configurator->setActive(!$configurator->isActive());
            $repository->save();

            $response = [
                'status' => true,
                'message' => $this->trans(
                    'The status has been successfully updated.',
                    'Modules.Drsoftfrproductwizard.Success'
                ),
            ];
        } catch (Throwable $t) {
            $response = [
                'status' => false,
                'message' => $this->trans(
                    'An error occurred while updating the status.',
                    'Modules.Drsoftfrproductwizard.Error'
                ),
            ];
        } finally {
            return $this->json($response);
        }
    }

    /**
     * Display error message when obj cannot be found and redirect to index page.
     */
    private function cannotFindObjRedirect(): RedirectResponse
    {
        $this->addFlash(
            'error',
            $this->trans(
                'Cannot find obj',
                'Modules.Drsoftfrproductwizard.Error'
            )
        );

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    private function exportNormalizeDates(array $data): array
    {
        foreach (['date_add', 'date_upd'] as $k) {
            if (isset($data[$k]) && $data[$k] instanceof \DateTimeInterface) {
                $data[$k] = $data[$k]->format('c');
            }
        }

        if (isset($data['steps']) && is_array($data['steps'])) {
            foreach ($data['steps'] as $i => $step) {
                $data['steps'][$i] = $this->exportNormalizeDates($step);
            }
        }

        if (isset($data['product_choices']) && is_array($data['product_choices'])) {
            foreach ($data['product_choices'] as $i => $choice) {
                $data['product_choices'][$i] = $this->exportNormalizeDates($choice);
            }
        }

        return $data;
    }

    private function defineJsProps(): void
    {
        \Media::addJsDef([
            'drsoftfrproductwizard' => [
                'routes' => [
                    'home' => $this->generateUrl(self::PAGE_INDEX_ROUTE),
                    'get' => $this->generateUrl(self::PAGE_API_GET_ROUTE),
                    'save' => $this->generateUrl(self::PAGE_API_SAVE_ROUTE),
                    'product_search' => $this->generateUrl(self::PAGE_API_PRODUCT_SEARCH_ROUTE),
                    'product' => $this->generateUrl(self::PAGE_API_PRODUCT_ROUTE),
                ],
                'messages' => [
                    'Modules.Drsoftfrproductwizard.Admin' => [
                        'Create a scenario' => $this->trans('Create a scenario', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Edit the scenario' => $this->trans('Edit the scenario', 'Modules.Drsoftfrproductwizard.Admin'),
                        'DEV MODE - view data in real time' => $this->trans('DEV MODE - view data in real time', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Real-time data' => $this->trans('Real-time data', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Loading...' => $this->trans('Loading...', 'Modules.Drsoftfrproductwizard.Admin'),
                        'General information' => $this->trans('General information', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Name of scenario' => $this->trans('Name of scenario', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Active' => $this->trans('Active', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Steps in the scenario' => $this->trans('Steps in the scenario', 'Modules.Drsoftfrproductwizard.Admin'),
                        'No steps defined for this scenario.' => $this->trans('No steps defined for this scenario.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Add a step' => $this->trans('Add a step', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Delete' => $this->trans('Delete', 'Modules.Drsoftfrproductwizard.Admin'),
                        'The position is automatically updated when dragging and dropping.' => $this->trans('The position is automatically updated when dragging and dropping.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Position' => $this->trans('Position', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Step description' => $this->trans('Step description', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Wording' => $this->trans('Wording', 'Modules.Drsoftfrproductwizard.Admin'),
                        'New' => $this->trans('New', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Step' => $this->trans('Step', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Add a product selection' => $this->trans('Add a product selection', 'Modules.Drsoftfrproductwizard.Admin'),
                        'No product selection defined for this step.' => $this->trans('No product selection defined for this step.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Product selection' => $this->trans('Product selection', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Quantity' => $this->trans('Quantity', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Forced quantity' => $this->trans('Forced quantity', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Allow selection of quantity' => $this->trans('Allow selection of quantity', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Default choice' => $this->trans('Default choice', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Search for a product...' => $this->trans('Search for a product...', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Product' => $this->trans('Product', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Choice wording' => $this->trans('Choice wording', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Product choice' => $this->trans('Product choice', 'Modules.Drsoftfrproductwizard.Admin'),
                        'No results found' => $this->trans('No results found', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Do you want to delete this step?' => $this->trans('Do you want to delete this step?', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Step deleted successfully' => $this->trans('Step deleted successfully', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Do you want to delete this product choice?' => $this->trans('Do you want to delete this product choice?', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Product choice deleted successfully' => $this->trans('Product choice deleted successfully', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Search for a product to pair with this selection.' => $this->trans('Search for a product to pair with this selection.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'No product found with this name.' => $this->trans('No product found with this name.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Display conditions' => $this->trans('Display conditions', 'Modules.Drsoftfrproductwizard.Admin'),
                        'This product selection is new, so you cannot set conditions yet. You must register before you can configure the conditions.' => $this->trans('This product selection is new, so you cannot set conditions yet. You must register before you can configure the conditions.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'No conditions defined. This choice will always be displayed.' => $this->trans('No conditions defined. This choice will always be displayed.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Add a condition' => $this->trans('Add a condition', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Reduction' => $this->trans('Reduction', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Reduction type' => $this->trans('Reduction type', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Tax included' => $this->trans('Tax included', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Quantity rule' => $this->trans('Quantity rule', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Mode' => $this->trans('Mode', 'Modules.Drsoftfrproductwizard.Admin'),
                        'None' => $this->trans('None', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Fixed' => $this->trans('Fixed', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Expression' => $this->trans('Expression', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Locked' => $this->trans('Locked', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Quantity or Offset' => $this->trans('Quantity or Offset', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Min' => $this->trans('Min', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Max' => $this->trans('Max', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Rounded' => $this->trans('Rounded', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Floor' => $this->trans('Floor', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Ceil' => $this->trans('Ceil', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Round' => $this->trans('Round', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Sources' => $this->trans('Sources', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Add a source' => $this->trans('Add a source', 'Modules.Drsoftfrproductwizard.Admin'),
                        'No specific source.' => $this->trans('No specific source.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Choice' => $this->trans('Choice', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Description' => $this->trans('Description', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Remember to save so that you can select the newly added items.' => $this->trans('Remember to save so that you can select the newly added items.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'This product selection is new, so you cannot set quantity rule yet. You must register before you can configure the quantity rules.' => $this->trans('This product selection is new, so you cannot set quantity rule yet. You must register before you can configure the quantity rules.', 'Modules.Drsoftfrproductwizard.Admin'),
                        'Duplicate' => $this->trans('Duplicate', 'Modules.Drsoftfrproductwizard.Admin'),
                    ],
                    'Modules.Drsoftfrproductwizard.Error' => [
                        'Error loading the configurator' => $this->trans('Error loading the configurator', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error fetching configurator:' => $this->trans('Error fetching configurator:', 'Modules.Drsoftfrproductwizard.Error'),
                        'An error occurred while loading the configurator.' => $this->trans('An error occurred while loading the configurator.', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error while saving the configurator' => $this->trans('Error while saving the configurator', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error saving configurator:' => $this->trans('Error saving configurator:', 'Modules.Drsoftfrproductwizard.Error'),
                        'An error occurred while saving the configurator.' => $this->trans('An error occurred while saving the configurator.', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error' => $this->trans('Error', 'Modules.Drsoftfrproductwizard.Error'),
                        'This condition no longer points to a valid step or choice. Modify or delete it.' => $this->trans('This condition no longer points to a valid step or choice. Modify or delete it.', 'Modules.Drsoftfrproductwizard.Error'),
                    ],
                    'Modules.Drsoftfrproductwizard.Success' => [
                        'Configurator successfully saved' => $this->trans('Configurator successfully saved', 'Modules.Drsoftfrproductwizard.Success'),
                        'Success' => $this->trans('Success', 'Modules.Drsoftfrproductwizard.Success'),
                        'Step duplicated successfully' => $this->trans('Step duplicated successfully', 'Modules.Drsoftfrproductwizard.Success'),
                        'Product choice duplicated successfully' => $this->trans('Product choice duplicated successfully', 'Modules.Drsoftfrproductwizard.Success'),
                    ],
                ],
            ],
        ]);
    }

    protected function getGridFactory(): GridFactory
    {
        /** @type GridFactory */
        return $this->get(self::GRID_CONFIGURATOR_FACTORY);
    }

    private function getToolbarButtons(): array
    {
        return [
            'add' => [
                'desc' => $this->trans('Add new Configurator', 'Modules.Drsoftfrproductwizard.Admin'),
                'icon' => 'add_circle_outline',
                'href' => $this->generateUrl(self::PAGE_CREATE_ROUTE),
            ],
            'import' => [
                'desc' => $this->trans('Import', 'Modules.Drsoftfrproductwizard.Admin'),
                'icon' => 'file_upload',
                'href' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_import_form'),
            ],
        ];
    }
}
