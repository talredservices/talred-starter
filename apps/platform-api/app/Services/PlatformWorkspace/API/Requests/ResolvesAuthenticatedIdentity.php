<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Requests;

use Zolta\Http\Authorization\Identity;

trait ResolvesAuthenticatedIdentity
{
    protected function hasAuthenticatedIdentity(): bool
    {
        return Identity::current() !== null;
    }

    protected function authenticatedAccountId(): string
    {
        return (string) Identity::current()?->getId();
    }
}
