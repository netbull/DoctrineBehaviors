<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Tree;

use Doctrine\Common\Collections\Collection;
use NetBull\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

trait TreeNodePropertiesTrait
{
    /**
     * @var string
     */
    protected string $materializedPath = '';

    /**
     * @var Collection<TreeNodeInterface>
     */
    private Collection $childNodes;

    /**
     * @var TreeNodeInterface|null
     */
    private ?TreeNodeInterface $parentNode = null;
}
