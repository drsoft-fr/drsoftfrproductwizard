<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Service;

use Address;
use Configuration;
use Context;
use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\ReductionType;
use Group;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use Product;
use TaxManagerFactory;

final class PriceResolverService
{
    final public static function get(ProductChoiceDto $productChoiceDto, StepDto $stepDto, ConfiguratorDto $configuratorDto, ProductLazyArray $product = null): array
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

        [$reduction, $reductionTax, $reductionType, $hasDiscount] = self::pickReduction(
            $productChoiceDto,
            $stepDto,
            $configuratorDto
        );

        $price = $product->price;
        $regularPrice = $product->regular_price;
        $priceAmount = $product->price_amount;
        $regularPriceAmount = $product->regular_price_amount;

        if ($reduction <= $product->reduction) {
            return self::buildResponse(
                $price,
                $regularPrice,
                $priceAmount,
                $regularPriceAmount,
                $product->has_discount,
                $product->reduction,
                $product->discount_type,
                !!$product->specific_prices['reduction_tax']
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

    private static function pickReduction(
        ProductChoiceDto $productChoiceDto,
        StepDto          $stepDto,
        ConfiguratorDto  $configuratorDto
    ): array
    {
        if ($productChoiceDto->reduction > 0) {
            return [$productChoiceDto->reduction, $productChoiceDto->reductionTax, $productChoiceDto->reductionType, true];
        }

        if ($stepDto->reduction > 0) {
            return [$stepDto->reduction, $stepDto->reductionTax, $stepDto->reductionType, true];
        }

        if ($configuratorDto->reduction > 0) {
            return [$configuratorDto->reduction, $configuratorDto->reductionTax, $configuratorDto->reductionType, true];
        }

        return [$productChoiceDto->reduction, $productChoiceDto->reductionTax, $productChoiceDto->reductionType, false];
    }


    private static function buildResponse(
        string $price,
        string $regularPrice,
        float  $priceAmount,
        float  $regularPriceAmount,
        bool   $hasDiscount,
        float  $reduction,
        string $reductionType,
        bool   $reductionTax
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
}
