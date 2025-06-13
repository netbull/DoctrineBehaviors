<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampablePropertiesTrait
{
    /**
     * @var DateTimeInterface|null
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @var DateTimeInterface|null
     */
    protected ?DateTimeInterface $updatedAt = null;
}
