<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Queries;

use App\Services\PlatformWorkspace\Application\Contracts\WorkspaceReader;
use Zolta\Cqrs\Attributes\HandlesQuery;
use Zolta\Cqrs\Services\Option;

#[HandlesQuery(ListWorkspacesQuery::class)]
final readonly class ListWorkspacesQueryHandler
{
    public function __construct(private WorkspaceReader $workspaces) {}
    public function __invoke(ListWorkspacesQuery $query): Option
    {
        return Option::some(['workspaces' => $this->workspaces->listAccessible($query->accountId)]);
    }
}
