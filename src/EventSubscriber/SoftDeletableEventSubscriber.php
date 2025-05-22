<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\MappingException;
use NetBull\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
#[AsDoctrineListener(event: Events::onFlush)]
final class SoftDeletableEventSubscriber
{
    /**
     * @var string
     */
    private const DELETED_AT = 'deletedAt';

    /**
     * @param LoadClassMetadataEventArgs $loadClassMetadataEventArgs
     * @return void
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (!is_a($classMetadata->reflClass->getName(), SoftDeletableInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField(self::DELETED_AT)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::DELETED_AT,
            'type' => 'datetime',
            'nullable' => true,
        ]);
    }

    /**
     * @param OnFlushEventArgs $onFlushEventArgs
     * @return void
     */
    public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
    {
        $objectManager = $onFlushEventArgs->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $object) {
            if (!$object instanceof SoftDeletableInterface) {
                continue;
            }

            $oldValue = $object->getDeletedAt();

            $object->delete();
            $objectManager->persist($object);

            $unitOfWork->propertyChanged($object, self::DELETED_AT, $oldValue, $object->getDeletedAt());
            $unitOfWork->scheduleExtraUpdate($object, [
                self::DELETED_AT => [$oldValue, $object->getDeletedAt()],
            ]);
        }
    }
}
