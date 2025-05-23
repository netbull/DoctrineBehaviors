<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\ORM\Tree;

use ArrayAccess;
use Doctrine\ORM\QueryBuilder;
use NetBull\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

trait TreeTrait
{
    /**
     * Constructs a query builder to get all root nodes
     */
    public function getRootNodesQB(string $rootAlias = 't'): QueryBuilder
    {
        return $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias . '.materializedPath = :empty')
            ->setParameter('empty', '');
    }

    public function getRootNodes(string $rootAlias = 't'): array
    {
        return $this->getRootNodesQB($rootAlias)
            ->getQuery()
            ->execute();
    }

    /**
     * Returns a node hydrated with its children and parents
     *
     * @return TreeNodeInterface[]|ArrayAccess|null
     */
    public function getTree(string $path = '', string $rootAlias = 't', array $extraParams = []): array|ArrayAccess|null
    {
        $results = $this->getFlatTree($path, $rootAlias, $extraParams);

        return $this->buildTree($results);
    }

    /**
     * @param TreeNodeInterface $treeNode
     * @param string $rootAlias
     * @return QueryBuilder
     */
    public function getTreeExceptNodeAndItsChildrenQB(
        TreeNodeInterface $treeNode,
        string $rootAlias = 't'
    ): QueryBuilder {
        return $this->getFlatTreeQB('', $rootAlias)
            ->andWhere($rootAlias . '.materializedPath NOT LIKE :except_path')
            ->andWhere($rootAlias . '.id != :id')
            ->setParameter('except_path', $treeNode->getRealMaterializedPath() . '%')
            ->setParameter('id', $treeNode->getId());
    }

    /**
     * Extracts the root node and constructs a tree using flat resultset
     *
     * @return ArrayAccess|TreeNodeInterface[]|null
     */
    public function buildTree(array $results): array|ArrayAccess|null
    {
        if ($results === []) {
            return null;
        }

        $root = $results[0];
        $root->buildTree($results);

        return $root;
    }

    /**
     * Constructs a query builder to get a flat tree, starting from a given path
     */
    public function getFlatTreeQB(string $path = '', string $rootAlias = 't', array $extraParams = []): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias . '.materializedPath LIKE :path')
            ->addOrderBy($rootAlias . '.materializedPath', 'ASC')
            ->setParameter('path', $path . '%');

        $parentId = basename($path);
        if ($parentId !== '' && $parentId !== '0') {
            $queryBuilder->orWhere($rootAlias . '.id = :parent')
                ->setParameter('parent', $parentId);
        }

        $this->addFlatTreeConditions($queryBuilder, $extraParams);

        return $queryBuilder;
    }

    /**
     * @param string $path
     * @param string $rootAlias
     * @param array $extraParams
     * @return array
     */
    public function getFlatTree(string $path, string $rootAlias = 't', array $extraParams = []): array
    {
        return $this->getFlatTreeQB($path, $rootAlias, $extraParams)
            ->getQuery()
            ->execute();
    }

    /**
     * Manipulates the flat tree query builder before executing it. Override this method to customize the tree query
     */
    protected function addFlatTreeConditions(QueryBuilder $queryBuilder, array $extraParams): void
    {
    }
}
