<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Tree;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use NetBull\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use NetBull\DoctrineBehaviors\Exception\ShouldNotHappenException;
use NetBull\DoctrineBehaviors\Exception\TreeException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

trait TreeNodeMethodsTrait
{
    /**
     * @return int|string|null
     */
    public function getNodeId(): int|string|null
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public static function getMaterializedPathSeparator(): string
    {
        return '/';
    }

    /**
     * @return string
     */
    public function getRealMaterializedPath(): string
    {
        if ($this->getMaterializedPath() === self::getMaterializedPathSeparator()) {
            return $this->getMaterializedPath() . $this->getNodeId();
        }

        return $this->getMaterializedPath() . self::getMaterializedPathSeparator() . $this->getNodeId();
    }

    /**
     * @return string
     */
    public function getMaterializedPath(): string
    {
        return $this->materializedPath;
    }

    /**
     * @param string $path
     * @return void
     */
    public function setMaterializedPath(string $path): void
    {
        $this->materializedPath = $path;
        $this->setParentMaterializedPath($this->getParentMaterializedPath());
    }

    /**
     * @return string
     * @throws ShouldNotHappenException
     */
    public function getParentMaterializedPath(): string
    {
        $path = $this->getExplodedPath();
        array_pop($path);

        return static::getMaterializedPathSeparator() . implode(static::getMaterializedPathSeparator(), $path);
    }

    /**
     * @param string $path
     * @return void
     */
    public function setParentMaterializedPath(string $path): void
    {
        $this->parentNodePath = $path;
    }

    /**
     * @return string
     * @throws ShouldNotHappenException
     */
    public function getRootMaterializedPath(): string
    {
        $explodedPath = $this->getExplodedPath();

        return static::getMaterializedPathSeparator() . array_shift($explodedPath);
    }

    /**
     * @return int
     * @throws ShouldNotHappenException
     */
    public function getNodeLevel(): int
    {
        return count($this->getExplodedPath());
    }

    /**
     * @return bool
     * @throws ShouldNotHappenException
     */
    public function isRootNode(): bool
    {
        return self::getMaterializedPathSeparator() === $this->getParentMaterializedPath();
    }

    /**
     * @return bool
     */
    public function isLeafNode(): bool
    {
        return $this->getChildNodes()
            ->count() === 0;
    }

    /**
     * @return Collection<TreeNodeInterface>
     */
    public function getChildNodes(): Collection
    {
        // set default value as in entity constructors
        if ($this->childNodes === null) {
            $this->childNodes = new ArrayCollection();
        }

        return $this->childNodes;
    }

    /**
     * @param TreeNodeInterface $treeNode
     * @return void
     */
    public function addChildNode(TreeNodeInterface $treeNode): void
    {
        $this->getChildNodes()
            ->add($treeNode);
    }

    /**
     * @param TreeNodeInterface $treeNode
     * @return bool
     */
    public function isIndirectChildNodeOf(TreeNodeInterface $treeNode): bool
    {
        return $this->getRealMaterializedPath() !== $treeNode->getRealMaterializedPath()
            && str_starts_with($this->getRealMaterializedPath(), $treeNode->getRealMaterializedPath());
    }

    /**
     * @param TreeNodeInterface $treeNode
     * @return bool
     * @throws ShouldNotHappenException
     */
    public function isChildNodeOf(TreeNodeInterface $treeNode): bool
    {
        return $this->getParentMaterializedPath() === $treeNode->getRealMaterializedPath();
    }

    /**
     * @param TreeNodeInterface|null $treeNode
     * @return void
     * @throws TreeException
     */
    public function setChildNodeOf(?TreeNodeInterface $treeNode = null): void
    {
        $id = $this->getNodeId();
        if ($id === '' || $id === null) {
            throw new TreeException('You must provide an id for this node if you want it to be part of a tree.');
        }

        $path = $treeNode !== null
            ? rtrim($treeNode->getRealMaterializedPath(), static::getMaterializedPathSeparator())
            : static::getMaterializedPathSeparator();
        $this->setMaterializedPath($path);

        if ($this->parentNode !== null) {
            $this->parentNode->getChildNodes()
                ->removeElement($this);
        }

        $this->parentNode = $treeNode;

        if ($treeNode !== null) {
            $this->parentNode->addChildNode($this);
        }

        foreach ($this->getChildNodes() as $childNode) {
            /** @var TreeNodeInterface $this */
            $childNode->setChildNodeOf($this);
        }
    }

    /**
     * @return TreeNodeInterface|null
     */
    public function getParentNode(): ?TreeNodeInterface
    {
        return $this->parentNode;
    }

    /**
     * @throws TreeException
     */
    public function setParentNode(TreeNodeInterface $treeNode): void
    {
        $this->parentNode = $treeNode;
        $this->setChildNodeOf($this->parentNode);
    }

    /**
     * @return TreeNodeInterface
     */
    public function getRootNode(): TreeNodeInterface
    {
        $parent = $this;
        while ($parent->getParentNode() !== null) {
            $parent = $parent->getParentNode();
        }

        return $parent;
    }

    /**
     * @param TreeNodeInterface[] $treeNodes
     */
    public function buildTree(array $treeNodes): void
    {
        $this->getChildNodes()
            ->clear();

        foreach ($treeNodes as $treeNode) {
            if ($treeNode->getMaterializedPath() !== $this->getRealMaterializedPath()) {
                continue;
            }

            $treeNode->setParentNode($this);
            $treeNode->buildTree($treeNodes);
        }
    }

    /**
     * @param Closure|null $prepare a function to prepare the node before putting into the result
     * @return string
     * @throws JsonException
     */
    public function toJson(?Closure $prepare = null): string
    {
        $tree = $this->toArray($prepare);

        return Json::encode($tree);
    }

    /**
     * @param Closure|null $prepare a function to prepare the node before putting into the result
     * @param array|null $tree
     * @return array
     */
    public function toArray(?Closure $prepare = null, ?array &$tree = null): array
    {
        if ($prepare === null) {
            $prepare = static fn (TreeNodeInterface $node): string => (string) $node;
        }

        if ($tree === null) {
            $tree = [
                $this->getNodeId() => [
                    /** @var TreeNodeInterface $this */
                    'node' => $prepare($this),
                    'children' => [],
                ],
            ];
        }

        foreach ($this->getChildNodes() as $childNode) {
            $tree[$this->getNodeId()]['children'][$childNode->getNodeId()] = [
                'node' => $prepare($childNode),
                'children' => [],
            ];

            $childNode->toArray($prepare, $tree[$this->getNodeId()]['children']);
        }

        return $tree;
    }

    /**
     * @param Closure|null $prepare a function to prepare the node before putting into the result
     * @param array|null $tree a reference to an array, used internally for recursion
     * @return array
     */
    public function toFlatArray(?Closure $prepare = null, ?array &$tree = null): array
    {
        if ($prepare === null) {
            $prepare = static function (TreeNodeInterface $treeNode) {
                $pre = $treeNode->getNodeLevel() > 1 ? implode('', array_fill(0, $treeNode->getNodeLevel(), '--')) : '';
                return $pre . $treeNode;
            };
        }

        if ($tree === null) {
            $tree = [
                $this->getNodeId() => $prepare($this),
            ];
        }

        foreach ($this->getChildNodes() as $childNode) {
            $tree[$childNode->getNodeId()] = $prepare($childNode);
            $childNode->toFlatArray($prepare, $tree);
        }

        return $tree;
    }

    /**
     * @param TreeNodeInterface $node
     */
    public function offsetSet(mixed $offset, $node): void
    {
        /** @var TreeNodeInterface $this */
        $node->setChildNodeOf($this);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->getChildNodes()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->getChildNodes()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getChildNodes()[$offset];
    }

    /**
     * @return string[]
     * @throws ShouldNotHappenException
     */
    protected function getExplodedPath(): array
    {
        $separator = static::getMaterializedPathSeparator();
        if ($separator === '') {
            throw new ShouldNotHappenException();
        }

        $path = explode($separator, $this->getRealMaterializedPath());

        return array_filter($path, static fn ($item): bool => $item !== '');
    }
}
