<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Model\SoftDeletable;

trait SoftDeletableTrait
{
    use SoftDeletablePropertiesTrait;
    use SoftDeletableMethodsTrait;
}
