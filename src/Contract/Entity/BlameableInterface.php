<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Contract\Entity;

interface BlameableInterface
{
    /**
     * @param object|int|string $user
     */
    public function setCreatedBy(object|int|string $user): void;

    /**
     * @param object|int|string $user
     */
    public function setUpdatedBy(object|int|string $user): void;

    /**
     * @param object|int|string $user
     */
    public function setDeletedBy(object|int|string $user): void;

    /**
     * @return int|object|string
     */
    public function getCreatedBy(): object|int|string;

    /**
     * @return int|object|string
     */
    public function getUpdatedBy(): object|int|string;

    /**
     * @return int|object|string|null
     */
    public function getDeletedBy(): object|int|string|null;
}
