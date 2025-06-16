<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Blameable;

trait BlameableMethodsTrait
{
    /**
     * @param object|int|string|null $user
     * @return void
     */
    public function setCreatedBy(object|int|string|null $user): void
    {
        $this->createdBy = $user;
    }

    /**
     * @param object|int|string|null $user
     * @return void
     */
    public function setUpdatedBy(object|int|string|null $user): void
    {
        $this->updatedBy = $user;
    }

    /**
     * @param object|int|string|null $user
     * @return void
     */
    public function setDeletedBy(object|int|string|null $user): void
    {
        $this->deletedBy = $user;
    }

    /**
     * @return int|object|string|null
     */
    public function getCreatedBy(): object|int|string|null
    {
        return $this->createdBy;
    }

    /**
     * @return object|int|string|null
     */
    public function getUpdatedBy(): object|int|string|null
    {
        return $this->updatedBy;
    }

    /**
     * @return object|int|string|null
     */
    public function getDeletedBy(): object|int|string|null
    {
        return $this->deletedBy;
    }
}
