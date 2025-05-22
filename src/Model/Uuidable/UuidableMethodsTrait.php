<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Uuidable;

use NetBull\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait UuidableMethodsTrait
{
    /**
     * @param UuidInterface $uuid
     * @return void
     */
    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return UuidInterface|null
     * @throws ShouldNotHappenException
     */
    public function getUuid(): ?UuidInterface
    {
        if (is_string($this->uuid)) {
            if ($this->uuid === '') {
                throw new ShouldNotHappenException();
            }

            return Uuid::fromString($this->uuid);
        }

        return $this->uuid;
    }

    /**
     * @return void
     */
    public function generateUuid(): void
    {
        if ($this->uuid) {
            return;
        }

        $this->uuid = Uuid::uuid4();
    }
}
