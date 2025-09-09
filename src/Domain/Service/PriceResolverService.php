<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Service;

use Address;
use Configuration;
use Context;
use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Domain\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\ReductionType;
use Group;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use Product;
use TaxManagerFactory;

final class PriceResolverService
{
    /**
     * @return array{
     *     has_discount: string,
     *     reduction: string,
     *     reduction_type: float,
     *     reduction_tax: float,
     *     price: bool,
     *     regular_price: float,
     *     price_amount: string,
     *     regular_price_amount: bool,
     *     is_product_discount: bool
     * }
     *
     * @throws ProductChoiceConstraintException
     */
    final public static function get(
        ProductChoiceDto $productChoiceDto,
        StepDto          $stepDto,
        ConfiguratorDto  $configuratorDto,
        ProductLazyArray $product = null
    ): array
    {
        $reduction = $productChoiceDto->reduction;
        $reductionType = $productChoiceDto->reductionType;
        $reductionTax = $productChoiceDto->reductionTax;
        $price = '';
        $regularPrice = '';
        $hasDiscount = false;
        $priceAmount = 0.0;
        $regularPriceAmount = 0.0;

        if ($product === null) {
            return self::buildResponse(
                $price,
                $regularPrice,
                $priceAmount,
                $regularPriceAmount,
                $hasDiscount,
                $reduction,
                $reductionType,
                $reductionTax
            );

        }

        $reductionPickerResult = ReductionPickerService::pick(
            $productChoiceDto,
            $stepDto,
            $configuratorDto
        );

        $reduction = $reductionPickerResult['reduction']->getValue();
        $reductionTax = $reductionPickerResult['reductionTax']->getValue();
        $reductionType = $reductionPickerResult['reductionType']->getValue();
        $hasDiscount = $reductionPickerResult['hasReduction'];

        self::overridePriceFieldsFromProduct($product, $price, $regularPrice, $priceAmount, $regularPriceAmount);

        $productReduction = $product->reduction ?? 0;

        if ($reduction <= $productReduction) {
            $hasDiscount = $product->has_discount ?? $hasDiscount;
            $reduction = $product->reduction ?? $reduction;
            $reductionType = $product->discount_type ?? $reductionType;
            $hasReductionTaxFlag = !empty($product->specific_prices['reduction_tax']);

            if ($hasReductionTaxFlag) {
                $reductionTax = (bool)$product->specific_prices['reduction_tax'];
            }

            return self::buildResponse(
                $price,
                $regularPrice,
                $priceAmount,
                $regularPriceAmount,
                $hasDiscount,
                $reduction,
                $reductionType,
                $reductionTax,
                true
            );
        }

        $priceFormatter = new PriceFormatter();

        if (ReductionType::PERCENTAGE === $reductionType) {
            $reduction = ($priceAmount * ($reduction / 100));

            if ($reduction > $priceAmount) {
                $reduction = $priceAmount;
            }

            $priceAmount -= $reduction;
            $price = $priceFormatter->format($priceAmount);

            return self::buildResponse(
                $price,
                $regularPrice,
                $priceAmount,
                $regularPriceAmount,
                $hasDiscount,
                $reduction,
                $reductionType,
                $reductionTax
            );
        }

        $context = Context::getContext();
        $useTax = !Group::getCurrent()->price_display_method;

        if ($useTax === $reductionTax) {

            if ($reduction > $priceAmount) {
                $reduction = $priceAmount;
            }

            $priceAmount -= $reduction;
            $price = $priceFormatter->format($priceAmount);

            return self::buildResponse(
                $price,
                $regularPrice,
                $priceAmount,
                $regularPriceAmount,
                $hasDiscount,
                $reduction,
                $reductionType,
                $reductionTax
            );
        }

        $address = self::resolveAddress($context);
        $productId = (int)$product->id_product;
        $reduction = self::alignReductionToPriceTaxMode($reduction, $useTax, $reductionTax, $address, $productId, $context);

        if ($reduction > $priceAmount) {
            $reduction = $priceAmount;
        }

        $priceAmount -= $reduction;
        $price = $priceFormatter->format($priceAmount);

        return self::buildResponse(
            $price,
            $regularPrice,
            $priceAmount,
            $regularPriceAmount,
            $hasDiscount,
            $reduction,
            $reductionType,
            $reductionTax
        );
    }

    /**
     * @return array{
     *     has_discount: string,
     *     reduction: string,
     *     reduction_type: float,
     *     reduction_tax: float,
     *     price: bool,
     *     regular_price: float,
     *     price_amount: string,
     *     regular_price_amount: bool,
     *     is_product_discount: bool
     * }
     */
    private static function buildResponse(
        string $price,
        string $regularPrice,
        float  $priceAmount,
        float  $regularPriceAmount,
        bool   $hasDiscount,
        float  $reduction,
        string $reductionType,
        bool   $reductionTax,
        bool   $isProductDiscount = false
    ): array
    {
        return [
            'has_discount' => $hasDiscount,
            'reduction' => $reduction,
            'reduction_type' => $reductionType,
            'reduction_tax' => $reductionTax,
            'price' => $price,
            'regular_price' => $regularPrice,
            'price_amount' => $priceAmount,
            'regular_price_amount' => $regularPriceAmount,
            'is_product_discount' => $isProductDiscount,
        ];
    }

    private static function resolveAddress(Context $context): Address
    {
        if (is_object($context->cart) && $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} !== null) {
            $addressId = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            return new Address((int)$addressId);
        }

        return new Address();
    }

    private static function alignReductionToPriceTaxMode(
        float   $reduction,
        bool    $useTax,
        bool    $reductionTax,
        Address $address,
        int     $productId,
        Context $context
    ): float
    {
        $taxManager = TaxManagerFactory::getManager(
            $address,
            Product::getIdTaxRulesGroupByIdProduct($productId, $context)
        );

        $calculator = $taxManager->getTaxCalculator();

        if ($useTax === false && $reductionTax === true) {
            return $calculator->removeTaxes($reduction);
        }

        if ($useTax === true && $reductionTax === false) {
            return $calculator->addTaxes($reduction);
        }

        return $reduction;
    }

    private static function overridePriceFieldsFromProduct(
        ProductLazyArray $product,
        string           &$price,
        string           &$regularPrice,
        float            &$priceAmount,
        float            &$regularPriceAmount
    ): void
    {
        if (true === isset($product->price)) {
            $price = $product->price;
        }

        if (true === isset($product->regular_price)) {
            $regularPrice = $product->regular_price;
        }

        if (true === isset($product->price_amount)) {
            $priceAmount = (float)$product->price_amount;
        }

        if (true === isset($product->regular_price_amount)) {
            $regularPriceAmount = (float)$product->regular_price_amount;
        }
    }
}
