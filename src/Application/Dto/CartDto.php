<?php

namespace DrSoftFr\Module\ProductWizard\Application\Dto;

final class CartDto
{
    public function __construct(
        public int   $configuratorId,
        /** @var CartItemDto[] */
        public array $items = [],
    )
    {
    }

    public static function fromArray(array $data): self
    {
        $arr = [];

        foreach ($data['items'] as $item) {
            $arr[] = CartItemDto::fromArray($item);
        }

        return new self(
            (int)$data['configuratorId'],
            $arr,
        );
    }
}
