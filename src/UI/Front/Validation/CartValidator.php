<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Front\Validation;

use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\CartItemDto;
use DrSoftFr\Module\ProductWizard\Application\Exception\Cart\CartConstraintException;
use DrSoftFr\Module\ProductWizard\Application\Exception\CartItem\CartItemConstraintException;
use PrestaShop\PrestaShop\Adapter\Validate;
use Product;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CartValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly int                 $langId,
    )
    {
    }

    /**
     * @throws CartItemConstraintException
     * @throws CartConstraintException
     */
    public function validate(
        CartDto $dto
    ): void
    {
        $this->validateCartDto($dto);
        $this->validateCartItemDtos($dto->items);
    }

    /**
     * @throws CartConstraintException
     */
    private function validateCartDto(CartDto $dto): void
    {
        if (true === empty($dto->configuratorId)) {
            throw new CartConstraintException(
                $this->translator->trans('Invalid configuratorId.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartConstraintException::INVALID_CONFIGURATOR_ID
            );
        }

        if (true === empty($dto->items)) {
            throw new CartConstraintException(
                $this->translator->trans('No products selected.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartConstraintException::INVALID_CART_ITEMS_IS_EMPTY
            );
        }
    }

    /**
     * @throws CartItemConstraintException
     */
    private function validateCartItemDtos(array $dtos): void
    {
        foreach ($dtos as $dto) {
            $this->validateCartItemDto($dto);
        }
    }

    /**
     * @throws CartItemConstraintException
     */
    private function validateCartItemDto(CartItemDto $dto): void
    {
        if (true === empty($dto->productId)) {
            throw new CartItemConstraintException(
                $this->translator->trans('Invalid product ID.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartItemConstraintException::INVALID_PRODUCT_ID
            );
        }

        if (true === empty($dto->quantity)) {
            throw new CartItemConstraintException(
                $this->translator->trans('Invalid quantity.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartItemConstraintException::INVALID_QUANTITY
            );
        }

        if (true === empty($dto->productChoiceId)) {
            throw new CartItemConstraintException(
                $this->translator->trans('Invalid product choice ID.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartItemConstraintException::INVALID_PRODUCT_CHOICE_ID
            );
        }

        if (true === empty($dto->stepId)) {
            throw new CartItemConstraintException(
                $this->translator->trans('Invalid step ID.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartItemConstraintException::INVALID_STEP_ID
            );
        }

        $product = new Product($dto->productId, true, $this->langId);

        if (false === Validate::isLoadedObject($product) || !$product->active) {
            throw new CartItemConstraintException(
                $this->translator->trans('A selected product is not available anymore.', [], 'Modules.Drsoftfrproductwizard.Error'),
                CartItemConstraintException::INVALID_PRODUCT_AVAILABILITY
            );
        }

        if ($dto->combinationId > 0) {
            $attributes = $product->getAttributeCombinations($this->langId);
            $valid = false;

            foreach ($attributes as $attr) {
                if ((int)$attr['id_product_attribute'] !== $dto->combinationId) {
                    continue;
                }

                $valid = true;

                break;
            }

            if (false === $valid) {
                throw new CartItemConstraintException(
                    $this->translator->trans('Invalid product combination.', [], 'Modules.Drsoftfrproductwizard.Error'),
                    CartItemConstraintException::INVALID_COMBINATION_ID
                );
            }
        }
    }
}
