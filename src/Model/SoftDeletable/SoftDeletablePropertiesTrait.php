<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\SoftDeletable;

use DateTimeInterface;

trait SoftDeletablePropertiesTrait
{
    /**
     * @var DateTimeInterface|null
     */
    protected ?DateTimeInterface $deletedAt = null;
}
