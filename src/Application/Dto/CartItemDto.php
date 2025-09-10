<?php

namespace DrSoftFr\Module\ProductWizard\Application\Dto;

final class CartItemDto
{
    public function __construct(
        public int    $productChoiceId,
        public int    $stepId,
        public int    $productId,
        public int    $combinationId,
        public int    $quantity,
        public string $productName,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['productChoiceId'],
            (int)$data['stepId'],
            (int)$data['productId'],
            (int)$data['combinationId'],
            (int)$data['quantity'],
            strip_tags((string)$data['productName']),
        );
    }
}
