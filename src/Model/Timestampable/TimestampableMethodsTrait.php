<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Timestampable;

use DateInvalidTimeZoneException;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use NetBull\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait TimestampableMethodsTrait
{
    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     * @return void
     */
    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param DateTimeInterface $updatedAt
     * @return void
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     * @throws DateInvalidTimeZoneException
     * @throws ShouldNotHappenException
     */
    public function updateTimestamps(): void
    {
        // Create a datetime with microseconds
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        if ($this->createdAt === null) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }
}
