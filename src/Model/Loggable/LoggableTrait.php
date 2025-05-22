<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Loggable;

use DateTimeInterface;

trait LoggableTrait
{
    /**
     * @param array $changeSets
     * @return string
     */
    public function getUpdateLogMessage(array $changeSets = []): string
    {
        $message = [];
        foreach ($changeSets as $property => $changeSet) {
            $itemCount = count($changeSet);

            for ($i = 0, $s = $itemCount; $i < $s; ++$i) {
                $item = $changeSet[$i];

                if ($item instanceof DateTimeInterface) {
                    $changeSet[$i] = $item->format('Y-m-d H:i:s.u');
                }
            }

            if ($changeSet[0] === $changeSet[1]) {
                continue;
            }

            $message[] = $this->createChangeSetMessage($property, $changeSet);
        }

        return implode("\n", $message);
    }

    /**
     * @return string
     */
    public function getCreateLogMessage(): string
    {
        return sprintf('%s #%s created', self::class, $this->getId());
    }

    /**
     * @return string
     */
    public function getRemoveLogMessage(): string
    {
        return sprintf('%s #%s removed', self::class, $this->getId());
    }

    /**
     * @param string $property
     * @param array $changeSet
     * @return string
     */
    private function createChangeSetMessage(string $property, array $changeSet): string
    {
        return sprintf(
            '%s #%s : property "%s" changed from "%s" to "%s"',
            self::class,
            $this->getId(),
            $property,
            is_array($changeSet[0]) ? 'an array' : (string) $changeSet[0],
            is_array($changeSet[1]) ? 'an array' : (string) $changeSet[1]
        );
    }
}
