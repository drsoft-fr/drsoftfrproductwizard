<?php

namespace DrSoftFr\Module\ProductWizard\Application\Dto;

final class CartItemDto
{
    public function __construct(
        public int $productChoiceId,
        public int $stepid,
        public int $productId,
        public int $combinationId,
        public int $quantity,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['productChoiceId'],
            (int)$data['stepid'],
            (int)$data['productId'],
            (int)$data['combinationId'],
            (int)$data['quantity'],
        );
    }
}
