<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Blameable;

trait BlameablePropertiesTrait
{
    /**
     * @var string|int|object
     */
    protected string|int|object $createdBy;

    /**
     * @var string|int|object
     */
    protected string|int|object $updatedBy;

    /**
     * @var string|int|object
     */
    protected string|int|object $deletedBy;
}
