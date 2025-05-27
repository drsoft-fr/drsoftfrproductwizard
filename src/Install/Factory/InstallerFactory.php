<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Install\Factory;

use DrSoftFr\Module\ProductWizard\Install\Installer;

/**
 * The InstallerFactory class is responsible for creating instances of the Installer class.
 */
final class InstallerFactory
{
    /**
     * Create a new Installer instance with a FixturesInstaller and SettingConfiguration.
     *
     * @return Installer A new instance of the Installer class.
     */
    public static function create(): Installer
    {
        return new Installer();
    }
}
