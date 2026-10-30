<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Tests\Fixtures\Entity\Fresh;

use NetBull\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use NetBull\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

/**
 * Deliberately has no constructor: it relies on the trait's lazy child-node collection initialization.
 */
final class FreshTreeNodeEntity implements TreeNodeInterface
{
    use TreeNodeTrait;

    private ?int $id = null;

    public function __toString(): string
    {
        return 'fresh';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
