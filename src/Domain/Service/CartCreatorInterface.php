<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Service;

use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;

interface CartCreatorInterface
{
    public function create(CartDto $cartDto): int;
}
