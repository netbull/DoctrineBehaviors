<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Contract\Entity;

interface BlameableInterface
{
    /**
     * @param object|int|string|null $user
     */
    public function setCreatedBy(object|int|string|null $user): void;

    /**
     * @param object|int|string|null $user
     */
    public function setUpdatedBy(object|int|string|null $user): void;

    /**
     * @param object|int|string|null $user
     */
    public function setDeletedBy(object|int|string|null $user): void;

    /**
     * @return int|object|string|null
     */
    public function getCreatedBy(): object|int|string|null;

    /**
     * @return int|object|string|null
     */
    public function getUpdatedBy(): object|int|string|null;

    /**
     * @return int|object|string|null
     */
    public function getDeletedBy(): object|int|string|null;
}
