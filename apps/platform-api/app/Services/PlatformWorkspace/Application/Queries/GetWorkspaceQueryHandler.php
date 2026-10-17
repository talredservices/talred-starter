<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Queries;

use App\Services\PlatformWorkspace\Application\Contracts\WorkspaceReader;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceNotFound;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(GetWorkspaceQuery::class)]
final readonly class GetWorkspaceQueryHandler
{
    public function __construct(private WorkspaceReader $workspaces) {}

    public function __invoke(GetWorkspaceQuery $query): Option
    {
        $workspace = $this->workspaces->findAccessible($query->workspaceId, $query->accountId);

        return $workspace === null
            ? Option::error(new WorkspaceNotFound())
            : Option::some(['workspace' => $workspace]);
    }
}
