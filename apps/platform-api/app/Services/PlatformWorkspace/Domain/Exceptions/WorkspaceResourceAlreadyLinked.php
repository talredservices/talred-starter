<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Exceptions;

use RuntimeException;
use Throwable;

final class WorkspaceResourceAlreadyLinked extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('This resource is already linked to a Workspace.', 0, $previous);
    }
}
