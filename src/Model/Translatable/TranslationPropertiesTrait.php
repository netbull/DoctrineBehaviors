<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\Translatable;

use NetBull\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

trait TranslationPropertiesTrait
{
    /**
     * @var string
     */
    protected string $locale = 'en';

    /**
     * Will be mapped to translatable entity by TranslatableSubscriber
     *
     * @var TranslatableInterface
     */
    protected TranslatableInterface $translatable;
}
