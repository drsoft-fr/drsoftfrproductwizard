<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Service;

use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;

final class PriceResolverService
{
    final public static function get(ProductChoiceDto $productChoiceDto, StepDto $stepDto, ConfiguratorDto $configuratorDto, ProductLazyArray $product = null): array
    {
        $reduction = 0;
        $reductionType = 'amount';
        $reductionTax = true;
        $price = '';
        $regularPrice = '';
        $hasDiscount = false;
        $priceAmount = 0.0;
        $regularPriceAmount = 0.0;

        if ($product === null) {
            return [
                'reduction' => $productChoiceDto->reduction,
                'reduction_type' => $productChoiceDto->reductionType,
                'reduction_tax' => $productChoiceDto->reductionTax,
                'price' => $price,
                'regular_price' => $regularPrice,
                'has_discount' => $hasDiscount,
                'price_amount' => $priceAmount,
                'regular_price_amount' => $regularPriceAmount,
            ];
        }

        $price = $product->price;
        $regularPrice = $product->regular_price;
        $priceAmount = $product->price_amount;
        $regularPriceAmount = $product->regular_price_amount;

        if (0 < $productChoiceDto->reduction) {
            $reduction = $productChoiceDto->reduction;
            $reductionType = $productChoiceDto->reductionType;
            $reductionTax = $productChoiceDto->reductionTax;
            $hasDiscount = true;
        } elseif (0 < $stepDto->reduction) {
            $reduction = $stepDto->reduction;
            $reductionType = $stepDto->reductionType;
            $reductionTax = $stepDto->reductionTax;
            $hasDiscount = true;
        } elseif (0 < $configuratorDto->reduction) {
            $reduction = $configuratorDto->reduction;
            $reductionType = $configuratorDto->reductionType;
            $reductionTax = $configuratorDto->reductionTax;
            $hasDiscount = true;
        }

        if ($reduction <= $product->reduction) {
            return [
                'has_discount' => $product->has_discount,
                'reduction' => $product->reduction,
                'reduction_type' => $product->discount_type,
                'reduction_tax' => $product->specific_prices['reduction_tax'] ?? true,
                'price' => $price,
                'regular_price' => $regularPrice,
                'price_amount' => $priceAmount,
                'regular_price_amount' => $regularPriceAmount,
            ];
        }

        // @TODO : handle discount

        return [
            'has_discount' => $hasDiscount,
            'reduction' => $reduction,
            'reduction_type' => $reductionType,
            'reduction_tax' => $reductionTax,
            'price' => $price,
            'regular_price' => $price,
            'price_amount' => $priceAmount,
            'regular_price_amount' => $regularPriceAmount,
        ];
    }
}
