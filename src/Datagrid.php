<?php

namespace AceDatagrid;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class Datagrid
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $datagridSpec = [];

    /**
     * @param EntityManager $entityManager
     * @param array $datagridSpec
     */
    public function __construct(EntityManager $entityManager, array $datagridSpec)
    {
        $this->entityManager = $entityManager;
        $this->datagridSpec = $datagridSpec;
    }

    /**
     * @param string $searchParam
     * @param string $sortParam
     * @return QueryBuilder
     */
    public function createSearchQueryBuilder($searchParam, &$sortParam = '')
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('entity')
            ->from($this->getClassName(), 'entity', 'entity.' . $this->getPrimaryKey());

        if (!$sortParam) {
            $sortParam = $this->getDefaultSort();
        }

        $this->addSearchWhere($queryBuilder, $this->getSearchColumns(), $searchParam);
        $this->addSortOrderBy($queryBuilder, $this->getHeaderColumns(), $sortParam);

        if ($searchParam && !$queryBuilder->getDQLPart('where')) {
            $queryBuilder->where('1=0');
        }

        return $queryBuilder;
    }

    /**
     * @param string $searchParam
     * @param int $maxResults
     * @return QueryBuilder
     */
    public function createSuggestQueryBuilder($searchParam, $maxResults = 5)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('entity')
            ->from($this->getClassName(), 'entity', 'entity.' . $this->getPrimaryKey())
            ->setMaxResults($maxResults);

        $this->addSearchWhere($queryBuilder, $this->getSuggestColumns(), $searchParam, false);
        $this->addSortOrderBy($queryBuilder, $this->getHeaderColumns(), $this->getDefaultSort());

        if (!$queryBuilder->getDQLPart('where')) {
            $queryBuilder->where('1=0');
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $columns
     * @param string $searchParam
     * @param bool $splitWords
     */
    public function addSearchWhere(QueryBuilder &$queryBuilder, array $columns, $searchParam, $splitWords = true)
    {
        $searchParam = trim(preg_replace('/[^a-z0-9! -]+/i', '', $searchParam));
        $searchParamParts = $splitWords ? explode(' ', $searchParam) : [$searchParam];
        $searchParamParts = array_filter($searchParamParts);

        $param = 1;
        foreach ($searchParamParts as $searchParamPart) {
            $searchBooleanValue = (int)(substr($searchParamPart, 0, 1) != '!');
            $searchParamPart = ltrim($searchParamPart, '!');

            $where = $queryBuilder->expr()->orX();
            foreach ($columns as $column) {
                if (strlen($searchParamPart) < $column['minLength']) {
                    continue;
                }

                $columnAlias = $this->getJoinColumnAlias($queryBuilder, $column['name']);
                $where->add($queryBuilder->expr()->like($columnAlias, '?' . $param));

                if (strpos($columnAlias, $searchParamPart) !== false) {
                    $where->add($queryBuilder->expr()->eq($columnAlias, $searchBooleanValue));
                }
            }

            if ($where->count()) {
                $queryBuilder->andWhere($where)->setParameter($param, '%' . $searchParamPart . '%');
                $param++;
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $headers
     * @param string $sortParam
     */
    public function addSortOrderBy(QueryBuilder &$queryBuilder, array $headers, $sortParam)
    {
        $sortDirection = (substr($sortParam, 0, 1) == '-');
        $sortParam = ltrim($sortParam, '-');

        foreach ($headers as $header) {
            if ($header['sortName'] == $sortParam) {
                foreach ($header['sortColumns'] as $column) {
                    $wrapper = null;
                    if (preg_match('/([a-z]+)\((.+?)\)/i', $column, $matches)) {
                        list(, $wrapper, $column) = $matches;
                    }

                    $columnSortDirection = (substr($column, 0, 1) == '-' xor $sortDirection);
                    $column = ltrim($column, '-');

                    $columnAlias = $this->getJoinColumnAlias($queryBuilder, $column);

                    if ($wrapper) {
                        $wrapperAlias = str_replace('.', '_', $columnAlias) . '_' . $wrapper;
                        $queryBuilder->addSelect($wrapper . '(' . $columnAlias . ') AS HIDDEN ' . $wrapperAlias);
                        $columnAlias = $wrapperAlias;
                    }

                    $queryBuilder->addOrderBy($columnAlias, $columnSortDirection ? 'DESC' : 'ASC');
                }
            }
        }

        $queryBuilder->groupBy('entity.' . $this->getPrimaryKey());
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $name
     * @return string
     */
    public function getJoinColumnAlias(QueryBuilder &$queryBuilder, $name)
    {
        $alias = $queryBuilder->getRootAliases()[0];
        $allAliases = $queryBuilder->getAllAliases();
        $joinParts = explode('.', $name);

        while (count($joinParts) > 1) {
            $joinName = array_shift($joinParts);
            $joinAlias = $alias . '_' . $joinName;

            if (!in_array($joinAlias, $allAliases)) {
                $queryBuilder->leftJoin($alias . '.' . $joinName, $joinAlias);
                $allAliases[] = $joinAlias;
            }

            $alias = $joinAlias;
        }

        return $alias . '.' . current($joinParts);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->datagridSpec['className'];
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->datagridSpec['primaryKey'];
    }

    /**
     * @return string
     */
    public function getSingularName()
    {
        return $this->datagridSpec['title']['singular'];
    }

    /**
     * @return string
     */
    public function getPluralName()
    {
        return $this->datagridSpec['title']['plural'];
    }

    /**
     * @return string
     */
    public function getDefaultSort()
    {
        return $this->datagridSpec['defaultSort'];
    }

    /**
     * @return array
     */
    public function getHeaderColumns()
    {
        return $this->datagridSpec['headerColumns'];
    }

    /**
     * @return array
     */
    public function getSearchColumns()
    {
        return $this->datagridSpec['searchColumns'];
    }

    /**
     * @return array
     */
    public function getSuggestColumns()
    {
        return $this->datagridSpec['suggestColumns'];
    }

}