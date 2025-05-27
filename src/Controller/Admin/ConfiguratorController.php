<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Form\ConfiguratorType;
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
        $configurator = new Configurator();
        $form = $this->createForm(ConfiguratorType::class, $configurator);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($configurator);
            $em->flush();

            $this->addFlash('success', 'Scénario créé avec succès.');

            return $this->redirectToRoute(self::PAGE_INDEX_ROUTE);
        }

        return $this->render(self::TEMPLATE_FOLDER . 'form.html.twig', [
            'form' => $form->createView(),
            'module' => $this->getModule(),
        ]);
    }

//    #[Route('/{id}/edit', name: 'admin_drsoft_fr_product_wizard_configurator_edit')]
//    public function editAction(Configurator $configurator, Request $request, EntityManagerInterface $em): Response
//    {
//        $form = $this->createForm(DrSoftFrProductWizardConfiguratorType::class, $configurator);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $configurator->setDateUpd(new \DateTime());
//            $em->flush();
//
//            $this->addFlash('success', 'Scénario modifié.');
//
//            return $this->redirectToRoute('admin_drsoft_fr_product_wizard_configurator_index');
//        }
//
//        return $this->render('@Modules/drsoftfrproductwizard/admin/configurator/form.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//
//    #[Route('/{id}/delete', name: 'admin_drsoft_fr_product_wizard_configurator_delete', methods: ['POST'])]
//    public function deleteAction(Configurator $configurator, EntityManagerInterface $em, Request $request): Response
//    {
//        if ($this->isCsrfTokenValid('delete' . $configurator->getId(), $request->request->get('_token'))) {
//            $em->remove($configurator);
//            $em->flush();
//            $this->addFlash('success', 'Scénario supprimé.');
//        }
//        return $this->redirectToRoute('admin_drsoft_fr_product_wizard_configurator_index');
//    }

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
