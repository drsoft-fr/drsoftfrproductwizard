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
        'color_base_100' => '#ECEEF0',
        'color_base_200' => '#D1D4D7',
        'color_base_300' => '#A5A9AE',
        'color_base_content' => '#161D22',
        'color_primary' => '#467570',
        'color_primary_content' => '#F0F6FB',
        'color_secondary' => '#687150',
        'color_secondary_content' => '#F4F9EE',
        'color_accent' => '#783967',
        'color_accent_content' => '#FAEEF2',
        'color_neutral' => '#161D22',
        'color_neutral_content' => '#ECEEF0',
        'color_info' => '#24716D',
        'color_info_content' => '#F4F9FC',
        'color_success' => '#1B6423',
        'color_success_content' => '#EEF9EF',
        'color_warning' => '#995613',
        'color_warning_content' => '#FFF5EC',
        'color_error' => '#8C4044',
        'color_error_content' => '#FDF2F3',
        'radius_selector' => '0rem',
        'radius_field' => '0rem',
        'radius_box' => '0rem',
        'size_selector' => '0.25rem',
        'size_field' => '0.25rem',
        'border' => '1px',
        'depth' => false,
        'noise' => false,
    ];

    const CSS_MATCHING_CONFIGURATION = [
        'color_base_100' => '--color-base-100',
        'color_base_200' => '--color-base-200',
        'color_base_300' => '--color-base-300',
        'color_base_content' => '--color-base-content',
        'color_primary' => '--color-primary',
        'color_primary_content' => '--color-primary-content',
        'color_secondary' => '--color-secondary',
        'color_secondary_content' => '--color-secondary-content',
        'color_accent' => '--color-accent',
        'color_accent_content' => '--color-accent-content',
        'color_neutral' => '--color-neutral',
        'color_neutral_content' => '--color-neutral-content',
        'color_info' => '--color-info',
        'color_info_content' => '--color-info-content',
        'color_success' => '--color-success',
        'color_success_content' => '--color-success-content',
        'color_warning' => '--color-warning',
        'color_warning_content' => '--color-warning-content',
        'color_error' => '--color-error',
        'color_error_content' => '--color-error-content',
        'radius_selector' => '--radius-selector',
        'radius_field' => '--radius-field',
        'radius_box' => '--radius-box',
        'size_selector' => '--size-selector',
        'size_field' => '--size-field',
        'border' => '--border',
        'depth' => '--depth',
        'noise' => '--noise',
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
