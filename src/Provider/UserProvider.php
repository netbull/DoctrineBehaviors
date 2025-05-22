<?php

declare(strict_types=1);

namespace NetBull\DoctrineBehaviors\Provider;

use NetBull\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserProvider implements UserProviderInterface
{
    /**
     * @param TokenStorageInterface $tokenStorage
     * @param string|null $blameableUserEntity
     */
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private ?string $blameableUserEntity = null
    ) {
    }

    /**
     * @return object|string|null
     */
    public function provideUser(): object|string|null
    {
        if ($token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();
            if ($this->blameableUserEntity) {
                if ($user instanceof $this->blameableUserEntity) {
                    return $user;
                }
            } else {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function provideUserEntity(): ?string
    {
        if (!$user = $this->provideUser()) {
            return null;
        }

        if (is_object($user)) {
            return $user::class;
        }

        return null;
    }
}
