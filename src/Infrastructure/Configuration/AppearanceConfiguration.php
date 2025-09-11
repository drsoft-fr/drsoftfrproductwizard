<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Configuration;

use DrSoftFr\Module\ProductWizard\Shared\Logging\ErrorLogger;
use Exception;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use Throwable;

final class AppearanceConfiguration implements DataConfigurationInterface
{
    const CONFIGURATION_KEYS = [
        'color_base_100',
        'color_base_200',
        'color_base_300',
        'color_base_content',
        'color_primary',
        'color_primary_content',
        'color_secondary',
        'color_secondary_content',
        'color_accent',
        'color_accent_content',
        'color_neutral',
        'color_neutral_content',
        'color_info',
        'color_info_content',
        'color_success',
        'color_success_content',
        'color_warning',
        'color_warning_content',
        'color_error',
        'color_error_content',
        'radius_selector',
        'radius_field',
        'radius_box',
        'size_selector',
        'size_field',
        'border',
        'depth',
        'noise',
    ];

    const CONFIGURATION_DEFAULT_VALUES = [
        'color_base_100' => '#f8f8f8',
        'color_base_200' => '#f2f2f2',
        'color_base_300' => '#e4e4e7',
        'color_base_content' => '#18181b',
        'color_primary' => '#00d390',
        'color_primary_content' => '#002c21',
        'color_secondary' => '#ed6aff',
        'color_secondary_content' => '#4a004e',
        'color_accent' => '#fdc700',
        'color_accent_content' => '#411e03',
        'color_neutral' => '#18181b',
        'color_neutral_content' => '#f8f8f8',
        'color_info' => '#0082ce',
        'color_info_content' => '#edf7fd',
        'color_success' => '#009689',
        'color_success_content' => '#effcf9',
        'color_warning' => '#df6f00',
        'color_warning_content' => '#fdf9e8',
        'color_error' => '#e50006',
        'color_error_content' => '#fef2f2',
        'radius_selector' => '2rem',
        'radius_field' => '2rem',
        'radius_box' => '0.5rem',
        'size_selector' => '0.25rem',
        'size_field' => '0.25rem',
        'border' => '1px',
        'depth' => false,
        'noise' => false,
    ];

    public function __construct(
        private readonly Configuration $configuration
    )
    {
    }

    public function getConfiguration(): array
    {
        $configuration = [];

        foreach (self::CONFIGURATION_KEYS as $key) {
            if (in_array(
                $key,
                [
                    'depth',
                    'noise',
                ],
                true
            )) {
                $configuration[$key] = $this->configuration->getBoolean(self::configKey($key), self::CONFIGURATION_DEFAULT_VALUES[$key]);

                continue;
            }

            $configuration[$key] = $this->configuration->get(self::configKey($key), self::CONFIGURATION_DEFAULT_VALUES[$key]);
        }

        return $configuration;
    }

    /**
     * Initialize the configuration.
     *
     * This method initializes the configuration by updating the current configuration
     * with the default values defined in `CONFIGURATION_DEFAULT_VALUES` constant.
     * It updates the configuration using the `updateConfiguration` method.
     *
     * @throws Exception
     */
    public function initConfiguration(): void
    {
        $this->updateConfiguration(self::CONFIGURATION_DEFAULT_VALUES);
    }

    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        try {
            foreach (self::CONFIGURATION_KEYS as $key) {
                $this->configuration->set(self::configKey($key), $configuration[$key]);
            }
        } catch (Throwable $t) {
            $errors[] = [
                'key' => ErrorLogger::fromThrowable(__METHOD__, __LINE__, $t),
                'domain' => 'Modules.Drsoftfrproductwizard.Error',
                'parameters' => [],
            ];
        }

        return $errors;
    }

    /**
     * @throws Exception
     */
    public function removeConfiguration(): void
    {
        foreach (self::CONFIGURATION_KEYS as $key) {
            $this->configuration->remove($key);
        }
    }

    public function validateConfiguration(array $configuration): void
    {
    }

    private static function configKey(string $field): string
    {
        return 'DRSOFT_FR_PRODUCT_WIZARD_APPEARANCE_' . strtoupper($field);
    }
}
