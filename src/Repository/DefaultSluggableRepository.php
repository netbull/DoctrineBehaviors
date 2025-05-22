<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use NetBull\DoctrineBehaviors\Contract\Entity\SluggableInterface;

final class DefaultSluggableRepository
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param SluggableInterface $sluggable
     * @param string $uniqueSlug
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function isSlugUniqueFor(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $entityClass = $sluggable::class;

        $qb = $this->entityManager->getRepository($sluggable::class)->createQueryBuilder('e');
        $qb->select($qb->expr()->count('e.id'))
            ->where($qb->expr()->eq('e.slug', ':slug'))
            ->setParameter('slug', $uniqueSlug);

        $identifiers = $this->entityManager->getClassMetadata($entityClass)
            ->getIdentifierValues($sluggable);

        foreach ($identifiers as $field => $value) {
            if ($value === null || $field === 'slug') {
                continue;
            }

            $normalizedField = str_replace('.', '_', $field);

            $qb->andWhere($qb->expr()->neq('e.'.$field, $normalizedField))
                ->setParameter($normalizedField, $value);
        }

        return !$qb->getQuery()->getSingleScalarResult();
    }
}
