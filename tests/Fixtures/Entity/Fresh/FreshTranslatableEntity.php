<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Tests\Fixtures\Entity\Fresh;

use NetBull\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use NetBull\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * Deliberately has no constructor: it relies on the trait's lazy collection initialization,
 * exactly like entities that are created with "new" by Symfony Forms (empty_data) or controllers.
 */
final class FreshTranslatableEntity implements TranslatableInterface
{
    use TranslatableTrait;
}
