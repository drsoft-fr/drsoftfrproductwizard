<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Application\Command;

use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;

final class CreateCartCommand
{
    public function __construct(
        public CartDto $cartDto,
    )
    {
    }
}
