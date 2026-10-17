<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Queries;

use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use Zolta\Cqrs\Queries\Query;

final class GetWorkspaceQuery extends Query
{
    public function __construct(
        public readonly WorkspaceId $workspaceId,
        public readonly AccountId $accountId,
    ) {}
}
