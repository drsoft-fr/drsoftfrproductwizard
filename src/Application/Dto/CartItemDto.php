<?php

namespace DrSoftFr\Module\ProductWizard\Application\Dto;

final class CartItemDto
{
    public function __construct(
        public int $productChoiceId,
        public int $stepid,
        public int $productId,
        public int $quantity,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['productChoiceId'],
            $data['stepid'],
            $data['productId'],
            $data['quantity'],
        );
    }
}
