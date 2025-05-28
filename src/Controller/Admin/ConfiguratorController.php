<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use DrSoftFr\Module\ProductWizard\Entity\Step;
use DrSoftFr\Module\ProductWizard\Form\ConfiguratorType;
use DrSoftFr\Module\ProductWizard\Form\ProductChoiceType;
use DrSoftFr\Module\ProductWizard\Form\StepType;
use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use drsoftfrproductwizard;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Language\ValueObject\LanguageId;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;
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
     * @AdminSecurity(
     *     "is_granted(['create'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to create this."
     * )
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function newAction(Request $request, EntityManagerInterface $em): Response
    {
        \Media::addJsDef([
            'drsoftfrproductwizard' => [
                'routes' => [
                    'product_search' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_product_search'),
                ],
            ],
        ]);

        $configurator = new Configurator();
        $form = $this->createForm(ConfiguratorType::class, $configurator);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($configurator->getSteps() as $step) {
                $step->setConfigurator($configurator);
                foreach ($step->getProductChoices() as $choice) {
                    $choice->setStep($step);
                }
            }

            $em->persist($configurator);
            $em->flush();

            $this->addFlash('success', 'Scénario créé avec succès.');

            return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
        }

        return $this->render(self::TEMPLATE_FOLDER . 'form.html.twig', [
            'form' => $form->createView(),
            'module' => $this->getModule(),
            'steps_choices' => $this->prepareStepChoices($configurator),
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
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function editAction(Configurator $configurator, Request $request, EntityManagerInterface $em): Response
    {
        \Media::addJsDef([
            'drsoftfrproductwizard' => [
                'routes' => [
                    'product_search' => $this->generateUrl('admin_drsoft_fr_product_wizard_configurator_product_search'),
                ],
            ],
        ]);

        $form = $this->createForm(ConfiguratorType::class, $configurator);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($configurator->getSteps() as $step) {
                $step->setConfigurator($configurator);
                foreach ($step->getProductChoices() as $choice) {
                    $choice->setStep($step);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Scénario modifié.');

            return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
        }

        return $this->render(self::TEMPLATE_FOLDER . 'form.html.twig', [
            'form' => $form->createView(),
            'module' => $this->getModule(),
            'steps_choices' => $this->prepareStepChoices($configurator),
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
            $this->addFlash('success', 'Scénario supprimé.');
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
    public function stepFragmentAction(Request $request)
    {
        $index = (int)$request->query->get('index');
        $step = new Step();
        $stepForm = $this->createForm(StepType::class, $step, [
            'block_name' => 'steps[' . $index . ']'
        ]);
        return $this->render(self::TEMPLATE_FOLDER . '_step_form.html.twig', [
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
    public function productChoiceFragmentAction(Request $request)
    {
        $index = (int)$request->query->get('index');
        $choice = new ProductChoice();
        $choiceForm = $this->createForm(ProductChoiceType::class, $choice, [
            'block_name' => 'productChoices[' . $index . ']'
        ]);
        return $this->render(self::TEMPLATE_FOLDER . '_product_choice_form.html.twig', [
            'form' => $choiceForm->createView(),
            'index' => $index,
        ]);
    }

    /**
     * Search products by name
     *
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller'))",
     *     redirectRoute="admin_drsoft_fr_product_wizard_configurator_index",
     *     message="You do not have permission to access this."
     * )
     *
     * @param Request $request
     * @param ProductRepository $repository
     *
     * @return Response
     */
    public function productSearchAction(Request $request, ProductRepository $repository): Response
    {
        try {
            $q = $request->query->get('q');

            if (empty($q)) {
                return $this->json(['items' => []]);
            }

            $results = $repository->searchProducts(pSQL($q), new LanguageId($this->languageId), new ShopId($this->shopId), 20);
        } catch (\Throwable $e) {
            $results = [
                'id' => 0,
                'name' => 'Erreur: ' . $e->getMessage(),
            ];
        }

        return $this->json([
            'items' => array_map(function ($p) {
                return ['id' => $p['id_product'], 'text' => $p['name']];
            }, $results)
        ]);
    }

    /**
     * Prepare step choices for the configurator form.
     *
     * @param Configurator $configurator
     *
     * @return array
     */
    private function prepareStepChoices(Configurator $configurator): array
    {
        $stepsChoices = [];
        /**
         * @var int $stepIdx
         * @var Step $step
         */
        foreach ($configurator->getSteps() as $step) {
            $stepsChoices[$step->getId()] = [
                'idx' => $step->getId(),
                'label' => $step->getLabel(),
                'choices' => [],
                'position' => $step->getPosition(),
            ];

            /**
             * @var int $choiceIdx
             * @var ProductChoice $choice
             */
            foreach ($step->getProductChoices() as $choice) {
                $stepsChoices[$step->getId()]['choices'][] = [
                    'idx' => $choice->getId(),
                    'label' => $choice->getLabel()
                ];
            }
        }

        return $stepsChoices;
    }

    /**
     * @return drsoftfrproductwizard
     */
    protected function getModule(): drsoftfrproductwizard
    {
        /** @type drsoftfrproductwizard */
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
