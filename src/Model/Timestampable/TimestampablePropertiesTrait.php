<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampablePropertiesTrait
{
    /**
     * @var DateTimeInterface
     */
    protected DateTimeInterface $createdAt;

    /**
     * @var DateTimeInterface
     */
    protected DateTimeInterface $updatedAt;
}
