# Uuidable

Uuidable generates uuid4 for an entity. Will automatically generate on persist.

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use NetBull\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use NetBull\DoctrineBehaviors\Model\Uuidable\UuidableTrait;

/**
 * @ORM\Entity
 */
class BlogPost implements UuidableInterface
{
    use UuidableTrait;
}
```
