<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Application\CommandHandler;

use DrSoftFr\Module\ProductWizard\Application\Command\CreateCartCommand;
use DrSoftFr\Module\ProductWizard\Domain\Service\CartCreatorInterface;

final class CreateCartCommandHandler
{
    public function __construct(
        private readonly CartCreatorInterface $cartCreator,
    )
    {
    }

    public function __invoke(CreateCartCommand $command): int
    {
        return $this->cartCreator->create($command->cartDto);
    }
}
