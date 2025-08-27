<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Application\Port\Security;

interface HtmlSanitizerInterface
{
    public function sanitize(?string $html): ?string;
}