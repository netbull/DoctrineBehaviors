<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Blameable;

trait BlameablePropertiesTrait
{
    /**
     * @var string|int|object|null
     */
    protected string|int|object|null $createdBy = null;

    /**
     * @var string|int|object|null
     */
    protected string|int|object|null $updatedBy = null;

    /**
     * @var string|int|object|null
     */
    protected string|int|object|null $deletedBy = null;
}
