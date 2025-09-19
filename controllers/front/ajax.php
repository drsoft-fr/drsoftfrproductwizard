<?php

declare(strict_types=1);

use DrSoftFr\Module\ProductWizard\Application\Command\CreateCartCommand;
use DrSoftFr\Module\ProductWizard\Application\CommandHandler\CreateCartCommandHandler;
use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;
use DrSoftFr\Module\ProductWizard\Application\Exception\Configurator\ConfiguratorNotFoundException;
use DrSoftFr\Module\ProductWizard\Domain\Exception\Configurator\ConfiguratorConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\Configurator\ConfiguratorId;
use DrSoftFr\Module\ProductWizard\UI\Front\Validation\CartValidator;
use DrSoftFr\Module\ProductWizard\UI\Hook\Presenter\ConfiguratorPresenter;

// @TODO il faut aussi que le client est le droit de voir le produit, ou sinon on masque le choix ?

final class DrsoftfrproductwizardAjaxModuleFrontController extends ModuleFrontController
{
    private const CART_CREATOR_COMMAND_HANDLER = 'drsoft_fr.module.product_wizard.application.command_handler.create_cart_command_handler';
    private const PRESENTER_SERVICE = 'drsoft_fr.module.product_wizard.service.configurator_presenter';
    private const VALIDATOR_SERVICE = 'drsoft_fr.module.product_wizard.ui.front.validation.cart_validator';

    /**
     * @var null|string
     */
    private $action = null;

    /**
     * Process the POST request and validate the reCAPTCHA token.
     *
     * @return void
     */
    public function postProcess(): void
    {
        try {
            parent::postProcess();

            $this->action = Tools::getValue('action', null);

            if (true === empty($this->action)) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'Action does not exist.',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred during Ajax processing. When retrieving the action parameter.');
        }
    }

    /**
     * Displays the response for an AJAX request.
     *
     * @return void
     */
    public function displayAjax(): void
    {
        try {
            switch ($this->action) {
                case 'add-to-cart':
                    $this->addToCartAction();

                    break;
                case 'get-configurator':
                    $this->getConfiguratorAction();

                    break;
                default:
                    $this->sendErrorResponse(
                        $this
                            ->context
                            ->getTranslator()
                            ->trans(
                                'Invalid action.',
                                [],
                                'Modules.Drsoftfrproductwizard.Error'
                            )
                    );
            }
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred during Ajax processing. When routing the action');
        }
    }

    /**
     * Redirects to the 404 error page.
     *
     * @return void
     */
    public function display(): void
    {
        Tools::redirect('/index.php?controller=404');
    }

    private function addToCartAction(): void
    {
        try {
            $data = json_decode(Tools::getValue('data', '{}'), true);
            $dto = CartDto::fromArray($data);

            try {
                $this
                    ->getValidator()
                    ->validate($dto);
            } catch (Throwable $t) {
                $this->sendErrorResponse($t->getMessage());
            }

            $cartId = $this->getCreateCartCommandHandler()(new CreateCartCommand($dto));

            if (true === empty($cartId)) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'An error occurred while creating the cart.',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->trans('Products added to cart successfully', [], 'Modules.Drsoftfrproductwizard.Shop'),
                'cartUrl' => $this->context->link->getPageLink('cart', null, null, ['action' => 'show']),
            ]));
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred while adding products to cart: ' . $t->getMessage());
        }
    }

    private function getConfiguratorAction(): void
    {
        try {
            $configuratorId = (int)Tools::getValue('slug', 0);

            if (true === empty($configuratorId)) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'Invalid configurator ID.',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }

            try {
                $presenter = $this->getPresenter();

                $this->ajaxRender(json_encode(
                    $presenter->present(new ConfiguratorId($configuratorId))
                ));

                return;
            } catch (ConfiguratorConstraintException $e) {
                $this->sendErrorResponse($e->getMessage());
            } catch (ConfiguratorNotFoundException $e) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'Configurator not found',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }
        } catch (Throwable $t) {
            $this->sendErrorResponse('Error retrieving configurator information.');
        }
    }

    /**
     * Sends an error response for an AJAX request.
     *
     * @param string $message The message key.
     *
     * @return void
     */
    private function sendErrorResponse(string $message): void
    {
        try {
            http_response_code(400);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $message,
            ]));
        } catch (Throwable $t) {
            http_response_code(400);
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during Ajax processing.'
            ]);
        } finally {
            die;
        }
    }

    /**
     * @throws Exception
     */
    private function getCreateCartCommandHandler(): CreateCartCommandHandler
    {
        /** @type CreateCartCommandHandler */
        return $this->get(self::CART_CREATOR_COMMAND_HANDLER);
    }

    /**
     * @throws Exception
     */
    private function getPresenter(): ConfiguratorPresenter
    {
        /** @type ConfiguratorPresenter */
        return $this->get(self::PRESENTER_SERVICE);
    }

    /**
     * @throws Exception
     */
    private function getValidator(): CartValidator
    {
        /** @type CartValidator */
        return $this->get(self::VALIDATOR_SERVICE);
    }
}
