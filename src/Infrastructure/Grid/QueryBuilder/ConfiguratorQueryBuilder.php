<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Grid\QueryBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use DrSoftFr\PrestaShopModuleHelper\Traits\QueryBuilderFiltersPreparerTrait;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Class ConfiguratorQueryBuilder builds search & count queries for Configurator grid.
 */
final class ConfiguratorQueryBuilder extends AbstractDoctrineQueryBuilder
{
    use QueryBuilderFiltersPreparerTrait;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
     */
    public function __construct(
        Connection                                                 $connection,
        string                                                     $dbPrefix,
        private readonly DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
    )
    {
        parent::__construct($connection, $dbPrefix);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria);

        $this->searchCriteriaApplicator
            ->applySorting($searchCriteria, $qb)
            ->applyPagination($searchCriteria, $qb);

        return $qb;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        return $this->getQueryBuilder($searchCriteria)
            ->select('COUNT(DISTINCT dfrpwc.`id`)');
    }

    /**
     * Get generic query builder.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     *
     * @throws Exception
     */
    private function getQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->connection
            ->createQueryBuilder()
            ->select(['dfrpwc.*'])
            ->addSelect("CONCAT('[drsoft-fr-product-wizard id=\"', dfrpwc.id, '\"]') AS shortcode")
            ->from($this->dbPrefix . 'drsoft_fr_product_wizard_configurator', 'dfrpwc');

        $this->applyFilters($qb, $searchCriteria->getFilters());

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     *
     * @return void
     *
     * @throws Exception
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        $allowedFilters = [
            'id' => [
                'alias' => 'dfrpwc',
                'operator' => '=',
                'type' => 'INT'
            ],
            'name' => [
                'alias' => 'dfrpwc',
                'operator' => 'LIKE',
                'type' => 'STRING'
            ],
            'active' => [
                'alias' => 'dfrpwc',
                'operator' => '=',
                'type' => 'INT'
            ],
            'date_add' => [
                'alias' => 'dfrpwc',
                'operator' => null,
                'type' => 'DATE'
            ],
            'date_upd' => [
                'alias' => 'dfrpwc',
                'operator' => null,
                'type' => 'DATE'
            ],
        ];

        $this->handle($qb, $filters, $allowedFilters);
    }
}
