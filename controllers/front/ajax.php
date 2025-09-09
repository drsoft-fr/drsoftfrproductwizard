<?php

declare(strict_types=1);

use DrSoftFr\Module\ProductWizard\Application\Exception\Configurator\ConfiguratorNotFoundException;
use DrSoftFr\Module\ProductWizard\Domain\Exception\Configurator\ConfiguratorConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\Configurator\ConfiguratorId;
use DrSoftFr\Module\ProductWizard\UI\Hook\Presenter\ConfiguratorPresenter;

// @TODO il faut aussi que le client est le droit de voir le produit, ou sinon on masque le choix ?

final class DrsoftfrproductwizardAjaxModuleFrontController extends ModuleFrontController
{
    private const PRESENTER_SERVICE = 'drsoft_fr.module.product_wizard.service.configurator_presenter';

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

            if (empty($this->action)) {
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

    private function addToCartAction(): void
    {
        try {
            $data = json_decode(Tools::getValue('data', '{}'), true);

            if (!isset($data['items']) || !is_array($data['items'])) {
                $this->sendErrorResponse('Invalid data format');
            }

            $selections = array_values(array_filter((array)$data['items']));
            $configuratorId = (int)$data['configuratorId'] ?? 0;

            if (true === empty($configuratorId)) {
                $this->sendErrorResponse('Invalid configuratorId');
            }

            $validationError = $this->validateSelections($selections);

            if (null !== $validationError) {
                $this->sendErrorResponse($validationError);
            }

            // Get the cart
            $cart = $this->context->cart;

            // Create a new cart if it doesn't exist
            if (!Validate::isLoadedObject($cart)) {
                $cart = new Cart();
                $cart->id_currency = (int)$this->context->currency->id;
                $cart->id_guest = (int)$this->context->cookie->id_guest;
                $cart->id_lang = (int)$this->context->language->id;
                $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
                $cart->id_shop = $this->context->shop->id;

                if ($this->context->cookie->id_customer) {
                    $cart->id_customer = (int)$this->context->cookie->id_customer;
                    $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                    $cart->id_address_invoice = (int)$cart->id_address_delivery;
                } else {
                    $cart->id_address_delivery = 0;
                    $cart->id_address_invoice = 0;
                }

                $cart->save();

                $this->context->cart = $cart;
            }

            // Add products to cart
            $addedProducts = [];
            foreach ($selections as $selection) {
                $productId = (int)$selection['productId'];
                $combinationId = isset($selection['combinationId']) ? (int)$selection['combinationId'] : 0;
                $quantity = max(1, (int)$selection['quantity']);

                $result = $cart->updateQty(
                    $quantity,
                    $productId,
                    $combinationId
                );

                if ($result) {
                    $addedProducts[] = [
                        'id' => $productId,
                        'combinationId' => $combinationId,
                        'quantity' => $quantity,
                    ];
                }
            }

            // Update cart cookie
            $this->context->cookie->__set('id_cart', (int)$cart->id);
            $this->context->cookie->write();

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->trans('Products added to cart successfully', [], 'Modules.Drsoftfrproductwizard.Shop'),
                'cartUrl' => $this->context->link->getPageLink('cart', null, null, ['action' => 'show']),
                'addedProducts' => $addedProducts,
            ]));
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred while adding products to cart: ' . $t->getMessage());
        }
    }

    /**
     * Validate given selections (products, quantities and combinations)
     *
     * @param array $selections
     *
     * @return string|null Error message if invalid, null if ok
     */
    private function validateSelections(array $selections): ?string
    {
        if (true === empty($selections)) {
            return $this->trans('No products selected.', [], 'Modules.Drsoftfrproductwizard.Error');
        }

        foreach ($selections as $s) {
            if (false === isset($s['productId'])) {
                return $this->trans('Invalid selection format.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            $productId = (int)$s['productId'];
            $quantity = isset($s['quantity']) ? (int)$s['quantity'] : 0;

            if ($productId <= 0 || $quantity <= 0) {
                return $this->trans('Invalid product or quantity.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            $product = new Product($productId, true, (int)$this->context->language->id);

            if (!Validate::isLoadedObject($product) || !$product->active) {
                return $this->trans('A selected product is not available anymore.', [], 'Modules.Drsoftfrproductwizard.Error');
            }
        }

        return null;
    }

    private function getConfiguratorAction(): void
    {
        try {
            $configuratorId = (int)Tools::getValue('slug', 0);

            if (empty($configuratorId)) {
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
     * @throws Exception
     */
    private function getPresenter(): ConfiguratorPresenter
    {
        /** @type ConfiguratorPresenter */
        return $this->get(self::PRESENTER_SERVICE);
    }
}
