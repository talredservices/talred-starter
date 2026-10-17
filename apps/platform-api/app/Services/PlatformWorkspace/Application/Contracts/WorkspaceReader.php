<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Contracts;

use App\Services\PlatformWorkspace\Application\Projections\WorkspaceView;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;

interface WorkspaceReader
{
    /** @return list<WorkspaceView> */
    public function listAccessible(AccountId $accountId): array;
}
