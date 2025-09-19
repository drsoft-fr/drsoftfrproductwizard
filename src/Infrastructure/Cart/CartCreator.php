<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Cart;

use Address;
use Cart;
use Context;
use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;
use DrSoftFr\Module\ProductWizard\Domain\Service\CartCreatorInterface;
use PrestaShop\PrestaShop\Adapter\Validate;
use PrestaShopDatabaseException;
use PrestaShopException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CartCreator implements CartCreatorInterface
{
    public function __construct(
        private readonly Context             $context,
        private readonly DiscountApplier     $discountApplier,
        private readonly TranslatorInterface $translator,
    )
    {
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function create(
        CartDto $cartDto
    ): int
    {
        $cart = $this->context->cart;

        if (false === Validate::isLoadedObject($cart)) {
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
        foreach ($cartDto->items as $itemDto) {
            $productId = $itemDto->productId;
            $combinationId = $itemDto->combinationId;
            $quantity = max(1, $itemDto->quantity);

            $result = $cart->updateQty(
                $quantity,
                $productId,
                $combinationId
            );

            if (false === $result) {
                throw new \RuntimeException(
                    $this->translator->trans(
                        sprintf(
                            'Product "%s" is currently unavailable and could not be added to your cart.',
                            var_export($itemDto->productName, true)
                        ),
                        [],
                        'Modules.Drsoftfrproductwizard.Error'
                    )
                );
            }
        }

        $this->discountApplier->apply($cart, $cartDto);
        $this->context->cookie->__set('id_cart', (int)$cart->id);
        $this->context->cookie->write();

        return (int)$cart->id;
    }
}
