<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Contract\Provider;

interface UserProviderInterface
{
    /**
     * @return object|string|null
     */
    public function provideUser(): object|string|null;

    public function provideUserEntity(): ?string;
}
