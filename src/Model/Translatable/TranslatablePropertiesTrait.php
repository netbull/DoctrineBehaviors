<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Translatable;

use Doctrine\Common\Collections\Collection;
use NetBull\DoctrineBehaviors\Contract\Entity\TranslationInterface;

trait TranslatablePropertiesTrait
{
    /**
     * @var Collection<TranslationInterface>
     */
    protected Collection $translations;

    /**
     * @see mergeNewTranslations
     * @var Collection<TranslationInterface>
     */
    protected Collection $newTranslations;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     *
     * @var string|null
     */
    protected ?string $currentLocale = null;

    /**
     * @var string
     */
    protected string $defaultLocale = 'en';
}
