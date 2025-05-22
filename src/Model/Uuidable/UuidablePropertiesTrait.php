<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Uuidable;

use Ramsey\Uuid\UuidInterface;

trait UuidablePropertiesTrait
{
    /**
     * @var UuidInterface|string|null
     */
    protected string|null|UuidInterface $uuid = null;
}
