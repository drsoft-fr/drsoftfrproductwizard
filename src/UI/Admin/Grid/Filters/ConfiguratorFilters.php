<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\UI\Admin\Grid\Filters;

use DrSoftFr\Module\ProductWizard\UI\Admin\Grid\Definition\Factory\ConfiguratorGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

/**
 * Class ConfiguratorFilters is responsible for providing default filters for Configurator grid.
 */
final class ConfiguratorFilters extends Filters
{
    protected $filterId = ConfiguratorGridDefinitionFactory::GRID_ID;

    /**
     * {@inheritdoc}
     */
    public static function getDefaults(): array
    {
        return [
            'limit' => 10,
            'offset' => 0,
            'orderBy' => 'id',
            'sortOrder' => 'asc',
            'filters' => [],
        ];
    }
}
