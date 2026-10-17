<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Exceptions;

use RuntimeException;

final class WorkspaceNotFound extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Workspace was not found for the authenticated account.');
    }
}
