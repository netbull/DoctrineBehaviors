<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use NetBull\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use NetBull\DoctrineBehaviors\Repository\DefaultSluggableRepository;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class SluggableEventSubscriber
{
    /**
     * @var string
     */
    private const SLUG = 'slug';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DefaultSluggableRepository $defaultSluggableRepository
    ) {
    }

    /**
     * @param LoadClassMetadataEventArgs $loadClassMetadataEventArgs
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();
        if ($this->shouldSkip($classMetadata)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::SLUG,
            'type' => 'string',
            'nullable' => true,
        ]);
    }

    /**
     * @param PrePersistEventArgs $args
     * @return void
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->processLifecycleEventArgs($args);
    }

    /**
     * @param PreUpdateEventArgs $args
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->processLifecycleEventArgs($args);
    }

    /**
     * @param ClassMetadataInfo $classMetadataInfo
     * @return bool
     */
    private function shouldSkip(ClassMetadataInfo $classMetadataInfo): bool
    {
        if (! is_a($classMetadataInfo->getName(), SluggableInterface::class, true)) {
            return true;
        }

        return $classMetadataInfo->hasField(self::SLUG);
    }

    /**
     * @param LifecycleEventArgs $lifecycleEventArgs
     * @return void
     */
    private function processLifecycleEventArgs(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (!$object instanceof SluggableInterface) {
            return;
        }

        $object->generateSlug();

        if ($object->shouldGenerateUniqueSlugs()) {
            $this->generateUniqueSlugFor($object);
        }
    }

    /**
     * @param SluggableInterface $sluggable
     * @return void
     */
    private function generateUniqueSlugFor(SluggableInterface $sluggable): void
    {
        $i = 0;
        $slug = $sluggable->getSlug();

        $uniqueSlug = $slug;

        while (! (
            $this->defaultSluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug)
            && $this->isSlugUniqueInUnitOfWork($sluggable, $uniqueSlug)
        )) {
            $uniqueSlug = $slug . '-' . ++$i;
        }

        $sluggable->setSlug($uniqueSlug);
    }

    /**
     * @param SluggableInterface $sluggable
     * @param string $uniqueSlug
     * @return bool
     */
    private function isSlugUniqueInUnitOfWork(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $scheduledEntities = $this->getOtherScheduledEntities($sluggable);
        foreach ($scheduledEntities as $scheduledEntity) {
            if ($scheduledEntity->getSlug() === $uniqueSlug) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return SluggableInterface[]
     */
    private function getOtherScheduledEntities(SluggableInterface $sluggable): array
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        $uowScheduledEntities = [
            ...$unitOfWork->getScheduledEntityInsertions(),
            ...$unitOfWork->getScheduledEntityUpdates(),
            ...$unitOfWork->getScheduledEntityDeletions(),
        ];

        $scheduledEntities = [];
        foreach ($uowScheduledEntities as $uowScheduledEntity) {
            if ($uowScheduledEntity instanceof SluggableInterface && $sluggable !== $uowScheduledEntity) {
                $scheduledEntities[] = $uowScheduledEntity;
            }
        }

        return $scheduledEntities;
    }
}
