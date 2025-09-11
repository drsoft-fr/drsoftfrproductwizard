<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Install\Factory;

use DrSoftFr\Module\ProductWizard\Infrastructure\Configuration\AppearanceConfiguration;
use DrSoftFr\Module\ProductWizard\Infrastructure\Install\Installer;
use PrestaShop\PrestaShop\Adapter\Configuration;

/**
 * The InstallerFactory class is responsible for creating instances of the Installer class.
 */
final class InstallerFactory
{
    public static function create(): Installer
    {
        return new Installer(
            new AppearanceConfiguration(
                new Configuration()
            )
        );
    }
}
