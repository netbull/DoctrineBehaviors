<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Tests\Fixtures\Entity\Fresh;

use NetBull\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use NetBull\DoctrineBehaviors\Model\Translatable\TranslationTrait;

final class FreshTranslatableEntityTranslation implements TranslationInterface
{
    use TranslationTrait;

    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
