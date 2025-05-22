<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\SoftDeletable;

use DateInvalidTimeZoneException;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use NetBull\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait SoftDeletableMethodsTrait
{
    /**
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws ShouldNotHappenException
     */
    public function delete(): void
    {
        $this->deletedAt = $this->currentDateTime();
    }

    /**
     * Restore entity by undeleting it
     */
    public function restore(): void
    {
        $this->deletedAt = null;
    }

    /**
     * @return bool
     * @throws DateInvalidTimeZoneException
     * @throws ShouldNotHappenException
     */
    public function isDeleted(): bool
    {
        if ($this->deletedAt !== null) {
            return $this->deletedAt <= $this->currentDateTime();
        }

        return false;
    }

    /**
     * @param DateTimeInterface|null $deletedAt
     * @return bool
     */
    public function willBeDeleted(?DateTimeInterface $deletedAt = null): bool
    {
        if ($this->deletedAt === null) {
            return false;
        }

        if ($deletedAt === null) {
            return true;
        }

        return $this->deletedAt <= $deletedAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTimeInterface|null $deletedAt
     * @return void
     */
    public function setDeletedAt(?DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return DateTimeInterface
     * @throws ShouldNotHappenException
     * @throws DateInvalidTimeZoneException
     */
    private function currentDateTime(): DateTimeInterface
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }
}
