<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Tests\Model;

use NetBull\DoctrineBehaviors\Tests\Fixtures\Entity\Fresh\FreshTranslatableEntity;
use NetBull\DoctrineBehaviors\Tests\Fixtures\Entity\Fresh\FreshTranslatableEntityTranslation;
use NetBull\DoctrineBehaviors\Tests\Fixtures\Entity\Fresh\FreshTreeNodeEntity;
use PHPUnit\Framework\TestCase;

/**
 * The typed Collection properties declared by the behaviour traits have no default value, so they are
 * uninitialized on a freshly constructed entity (Doctrine only initializes them when hydrating).
 * The lazy getters must cope with that instead of throwing "must not be accessed before initialization",
 * which is what Symfony Forms hit when they add/remove translations on an entity created via "new".
 */
final class UninitializedCollectionPropertyTest extends TestCase
{
    public function testFreshTranslatableEntityExposesEmptyCollections(): void
    {
        $entity = new FreshTranslatableEntity();

        $this->assertCount(0, $entity->getTranslations());
        $this->assertCount(0, $entity->getNewTranslations());
    }

    public function testFreshTranslatableEntityAcceptsAddedTranslation(): void
    {
        $entity = new FreshTranslatableEntity();
        $translation = new FreshTranslatableEntityTranslation();
        $translation->setLocale('en');
        $translation->setTitle('awesome');

        $entity->addTranslation($translation);

        $this->assertSame($translation, $entity->getTranslations()->get('en'));
        $this->assertSame($entity, $translation->getTranslatable());
    }

    public function testFreshTranslatableEntityCanBeTranslatedAndMerged(): void
    {
        $entity = new FreshTranslatableEntity();
        $entity->translate('en')
            ->setTitle('awesome');

        $this->assertCount(1, $entity->getNewTranslations());

        $entity->mergeNewTranslations();

        $this->assertCount(1, $entity->getTranslations());
        $this->assertCount(0, $entity->getNewTranslations());
        $this->assertSame('awesome', $entity->translate('en')->getTitle());
    }

    public function testFreshTreeNodeExposesEmptyChildNodes(): void
    {
        $treeNode = new FreshTreeNodeEntity();

        $this->assertCount(0, $treeNode->getChildNodes());
        $this->assertTrue($treeNode->isLeafNode());
    }
}
