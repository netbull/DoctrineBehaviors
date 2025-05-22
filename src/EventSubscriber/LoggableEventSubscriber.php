<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use NetBull\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final class LoggableEventSubscriber
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param PostPersistEventArgs $args
     * @return void
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof LoggableInterface) {
            return;
        }

        $createLogMessage = $object->getCreateLogMessage();
        $this->logger->log(LogLevel::INFO, $createLogMessage);

        $this->logChangeSet($args);
    }

    /**
     * @param PostUpdateEventArgs $args
     * @return void
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof LoggableInterface) {
            return;
        }

        $this->logChangeSet($args);
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof LoggableInterface) {
            $this->logger->log(LogLevel::INFO, $object->getRemoveLogMessage());
        }
    }

    /**
     * Logs entity changeset
     */
    private function logChangeSet(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $objectManager = $lifecycleEventArgs->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $object = $lifecycleEventArgs->getObject();

        $entityClass = $object::class;
        $classMetadata = $objectManager->getClassMetadata($entityClass);

        /** @var LoggableInterface $entity */
        $unitOfWork->computeChangeSet($classMetadata, $entity);
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        $message = $entity->getUpdateLogMessage($changeSet);

        if ($message === '') {
            return;
        }

        $this->logger->log(LogLevel::INFO, $message);
    }
}
