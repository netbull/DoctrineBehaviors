<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\UnitOfWork;
use NetBull\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use NetBull\DoctrineBehaviors\Contract\Provider\UserProviderInterface;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final class BlameableEventSubscriber
{
    /**
     * @var string
     */
    private const DELETED_BY = 'deletedBy';

    /**
     * @var string
     */
    private const UPDATED_BY = 'updatedBy';

    /**
     * @var string
     */
    private const CREATED_BY = 'createdBy';

    public function __construct(
        private UserProviderInterface $userProvider,
        private EntityManagerInterface $entityManager,
        private ?string $blameableUserEntity = null
    ) {
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on user entity
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (! is_a($classMetadata->reflClass->getName(), BlameableInterface::class, true)) {
            return;
        }

        $this->mapEntity($classMetadata);
    }

    /**
     * Stores the current user into createdBy and updatedBy properties
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof BlameableInterface) {
            return;
        }

        $user = $this->userProvider->provideUser();
        // no user set → skip
        if ($user === null) {
            return;
        }

        if (!$object->getCreatedBy()) {
            $object->setCreatedBy($user);

            $this->getUnitOfWork()
                ->propertyChanged($object, self::CREATED_BY, null, $user);
        }

        if (!$object->getUpdatedBy()) {
            $object->setUpdatedBy($user);

            $this->getUnitOfWork()
                ->propertyChanged($object, self::UPDATED_BY, null, $user);
        }
    }

    /**
     * Stores the current user into updatedBy property
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof BlameableInterface) {
            return;
        }

        if (!$user = $this->userProvider->provideUser()) {
            return;
        }

        $oldValue = $object->getUpdatedBy();
        $object->setUpdatedBy($user);

        $this->getUnitOfWork()
            ->propertyChanged($object, self::UPDATED_BY, $oldValue, $user);
    }

    /**
     * Stores the current user into deletedBy property
     */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $object = $args->getObject();
        if (!$object instanceof BlameableInterface) {
            return;
        }

        if (!$user = $this->userProvider->provideUser()) {
            return;
        }

        $oldDeletedBy = $object->getDeletedBy();
        $object->setDeletedBy($user);

        $this->getUnitOfWork()
            ->propertyChanged($object, self::DELETED_BY, $oldDeletedBy, $user);
    }

    /**
     * @param ClassMetadataInfo $classMetadataInfo
     * @return void
     * @throws MappingException
     */
    private function mapEntity(ClassMetadataInfo $classMetadataInfo): void
    {
        if ($this->blameableUserEntity !== null && class_exists($this->blameableUserEntity)) {
            $this->mapManyToOneUser($classMetadataInfo);
        } else {
            $this->mapStringUser($classMetadataInfo);
        }
    }

    /**
     * @return UnitOfWork
     */
    private function getUnitOfWork(): UnitOfWork
    {
        return $this->entityManager->getUnitOfWork();
    }

    /**
     * @param ClassMetadataInfo $classMetadataInfo
     * @return void
     */
    private function mapManyToOneUser(ClassMetadataInfo $classMetadataInfo): void
    {
        $this->mapManyToOneWithTargetEntity($classMetadataInfo, self::CREATED_BY);
        $this->mapManyToOneWithTargetEntity($classMetadataInfo, self::UPDATED_BY);
        $this->mapManyToOneWithTargetEntity($classMetadataInfo, self::DELETED_BY);
    }

    /**
     * @param ClassMetadataInfo $classMetadataInfo
     * @return void
     * @throws MappingException
     */
    private function mapStringUser(ClassMetadataInfo $classMetadataInfo): void
    {
        $this->mapStringNullableField($classMetadataInfo, self::CREATED_BY);
        $this->mapStringNullableField($classMetadataInfo, self::UPDATED_BY);
        $this->mapStringNullableField($classMetadataInfo, self::DELETED_BY);
    }

    /**
     * @param ClassMetadataInfo $classMetadataInfo
     * @param string $fieldName
     * @return void
     */
    private function mapManyToOneWithTargetEntity(ClassMetadataInfo $classMetadataInfo, string $fieldName): void
    {
        if ($classMetadataInfo->hasAssociation($fieldName)) {
            return;
        }

        $classMetadataInfo->mapManyToOne([
            'fieldName' => $fieldName,
            'targetEntity' => $this->blameableUserEntity,
            'joinColumns' => [
                [
                    'onDelete' => 'SET NULL',
                ],
            ],
        ]);
    }

    /**
     * @param ClassMetadataInfo $classMetadataInfo
     * @param string $fieldName
     * @return void
     * @throws MappingException
     */
    private function mapStringNullableField(ClassMetadataInfo $classMetadataInfo, string $fieldName): void
    {
        if ($classMetadataInfo->hasField($fieldName)) {
            return;
        }

        $classMetadataInfo->mapField([
            'fieldName' => $fieldName,
            'type' => 'string',
            'nullable' => true,
        ]);
    }
}
