<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Admin\Form\DataProvider;

use DrSoftFr\Module\ProductWizard\Infrastructure\Configuration\AppearanceConfiguration;
use Exception;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

final class AppearanceDataProvider implements FormDataProviderInterface
{
    public function __construct(
        private readonly AppearanceConfiguration $configuration
    )
    {
    }

    /**
     * @return array the form data as an associative array
     */
    public function getData(): array
    {
        return $this->configuration->getConfiguration();
    }

    /**
     * Persists form Data in Database and Filesystem.
     *
     * @return array $errors if data can't persisted an array of errors messages
     *
     * @throws Exception
     */
    public function setData(array $data): array
    {
        return $this->configuration->updateConfiguration($data);
    }
}
