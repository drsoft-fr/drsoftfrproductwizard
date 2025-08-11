<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use DrSoftFr\Module\ProductWizard\Entity\Step;
use DrSoftFr\Module\ProductWizard\Form\ProductChoiceType;
use DrSoftFr\Module\ProductWizard\Form\StepType;
use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\Package;
use DrSoftFr\PrestaShopModuleHelper\Domain\Asset\VersionStrategy\JsonManifestVersionStrategy;
use drsoftfrproductwizard;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @var Package
     */
    private $manifest;

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
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_module_manage",
     *     message="Access denied."
     * )
     *
     * @param Request $request
     * @param ConfiguratorRepository $repository
     *
     * @return Response
     */
    public function indexAction(
        Request                $request,
        ConfiguratorRepository $repository
    ): Response
    {
        return $this->render(self::TEMPLATE_FOLDER . 'home/index.html.twig', [
            'configurators' => $repository->findAll(),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
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
     *
     * @return Response
     */
    public function newAction(): Response
    {
        $configurator = new Configurator();

        $this->defineJsProps();

        return $this->render(self::TEMPLATE_FOLDER . 'form/index.html.twig', [
            'configurator_id' => null,
            'return_url' => $this->generateUrl(self::PAGE_INDEX_ROUTE),
            'module' => $this->module,
            'manifest' => $this->manifest,
        ]);
    }

    /**
     * Edit Configurator
     *
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to edit this."
     * )
     *
     * @param Configurator $configurator
     *
     * @return Response
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
     * Delete Configurator
     *
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to delete this."
     * )
     *
     * @param Configurator $configurator
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteAction(Configurator $configurator, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $configurator->getId(), $request->request->get('_token'))) {
            $em->remove($configurator);
            $em->flush();

            $this->addFlash('success', $this->trans('Deleted scenario', 'Modules.Drsoftfrproductwizard.Success'));
        }

        return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to delete this."
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function stepFragmentAction(Request $request): Response
    {
        $index = (int)$request->query->get('index');
        $step = new Step();
        $stepForm = $this->createForm(StepType::class, $step, [
            'block_name' => 'steps[' . $index . ']'
        ]);
        return $this->render(self::TEMPLATE_FOLDER . 'form/partial/_step_form.html.twig', [
            'form' => $stepForm->createView(),
            'index' => $index,
        ]);
    }

    /**
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to delete this."
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function productChoiceFragmentAction(Request $request): Response
    {
        $index = (int)$request->query->get('index');
        $choice = new ProductChoice();
        $choiceForm = $this->createForm(ProductChoiceType::class, $choice, [
            'block_name' => 'productChoices[' . $index . ']'
        ]);
        return $this->render(self::TEMPLATE_FOLDER . 'form/partial/_product_choice_form.html.twig', [
            'form' => $choiceForm->createView(),
            'index' => $index,
        ]);
    }

    private function defineJsProps(): void
    {
        \Media::addJsDef([
            'drsoftfrproductwizard' => [
                'routes' => [
                    'home' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_index'),
                    'get' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_api_get'),
                    'save' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_api_save'),
                    'product_search' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_api_product_search'),
                    'product' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_api_product'),
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
                    ],
                    'Modules.Drsoftfrproductwizard.Error' => [
                        'Error loading the configurator' => $this->trans('Error loading the configurator', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error fetching configurator:' => $this->trans('Error fetching configurator:', 'Modules.Drsoftfrproductwizard.Error'),
                        'An error occurred while loading the configurator.' => $this->trans('An error occurred while loading the configurator.', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error while saving the configurator' => $this->trans('Error while saving the configurator', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error saving configurator:' => $this->trans('Error saving configurator:', 'Modules.Drsoftfrproductwizard.Error'),
                        'An error occurred while saving the configurator.' => $this->trans('An error occurred while saving the configurator.', 'Modules.Drsoftfrproductwizard.Error'),
                        'Error' => $this->trans('Error', 'Modules.Drsoftfrproductwizard.Error'),
                    ],
                    'Modules.Drsoftfrproductwizard.Success' => [
                        'Configurator successfully saved' => $this->trans('Configurator successfully saved', 'Modules.Drsoftfrproductwizard.Success'),
                    ],
                ],
            ],
        ]);
    }
}
