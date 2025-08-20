<?php

namespace DrSoftFr\Module\ProductWizard\Dto;

final class DisplayConditionDto
{
    public function __construct(
        public ?string $step = null,
        public ?string $choice = null,
    )
    {
    }

    public static function fromArray(array $arr): self
    {
        return new self(
            $arr['step'],
            $arr['choice'],
        );
    }
}
