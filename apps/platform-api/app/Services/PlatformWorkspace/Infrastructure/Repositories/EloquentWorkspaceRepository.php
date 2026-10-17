<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Repositories;

use App\Services\PlatformWorkspace\Application\Contracts\WorkspaceReader;
use App\Services\PlatformWorkspace\Application\Projections\WorkspaceView;
use App\Services\PlatformWorkspace\Domain\Aggregates\Workspace;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceRepository;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use App\Services\PlatformWorkspace\Infrastructure\Mappers\WorkspaceMapper;
use App\Services\PlatformWorkspace\Infrastructure\Models\AccountWorkspaceDefaultRecord;
use App\Services\PlatformWorkspace\Infrastructure\Models\WorkspaceMembershipRecord;
use App\Services\PlatformWorkspace\Infrastructure\Models\WorkspaceRecord;
use Illuminate\Support\Facades\DB;

final class EloquentWorkspaceRepository implements WorkspaceRepository, WorkspaceReader
{
    public function listAccessible(AccountId $accountId): array
    {
        return WorkspaceRecord::query()
            ->join('platform_workspace_memberships', function ($join) use ($accountId): void {
                $join->on('platform_workspace_memberships.workspace_id', '=', 'platform_workspaces.id')
                    ->where('platform_workspace_memberships.account_id', '=', $accountId->toString())
                    ->where('platform_workspace_memberships.status', '=', 'active');
            })
            ->leftJoin('platform_account_workspace_defaults', function ($join) use ($accountId): void {
                $join->on('platform_account_workspace_defaults.workspace_id', '=', 'platform_workspaces.id')
                    ->where('platform_account_workspace_defaults.account_id', '=', $accountId->toString());
            })
            ->whereNull('platform_workspaces.archived_at')
            ->select('platform_workspaces.*', 'platform_workspace_memberships.role as workspace_role', 'platform_account_workspace_defaults.workspace_id as default_workspace_id')
            ->addSelect(['active_member_count' => DB::table('platform_workspace_memberships as member_count')
                ->selectRaw('COUNT(*)')
                ->whereColumn('member_count.workspace_id', 'platform_workspaces.id')
                ->where('member_count.status', 'active')])
            ->orderBy('platform_workspaces.name')
            ->get()
            ->map(fn (WorkspaceRecord $record): WorkspaceView => WorkspaceMapper::toView($record, (string) $record->workspace_role, (int) $record->active_member_count, $record->default_workspace_id !== null))
            ->all();
    }

    public function findAccessible(WorkspaceId $id, AccountId $accountId): ?WorkspaceView
    {
        $record = WorkspaceRecord::query()
            ->join('platform_workspace_memberships', function ($join) use ($accountId): void {
                $join->on('platform_workspace_memberships.workspace_id', '=', 'platform_workspaces.id')
                    ->where('platform_workspace_memberships.account_id', '=', $accountId->toString())
                    ->where('platform_workspace_memberships.status', '=', 'active');
            })
            ->leftJoin('platform_account_workspace_defaults', function ($join) use ($accountId): void {
                $join->on('platform_account_workspace_defaults.workspace_id', '=', 'platform_workspaces.id')
                    ->where('platform_account_workspace_defaults.account_id', '=', $accountId->toString());
            })
            ->whereKey($id->toString())
            ->whereNull('platform_workspaces.archived_at')
            ->select('platform_workspaces.*', 'platform_workspace_memberships.role as workspace_role', 'platform_account_workspace_defaults.workspace_id as default_workspace_id')
            ->addSelect(['active_member_count' => DB::table('platform_workspace_memberships as member_count')
                ->selectRaw('COUNT(*)')
                ->whereColumn('member_count.workspace_id', 'platform_workspaces.id')
                ->where('member_count.status', 'active')])
            ->first();

        return $record === null
            ? null
            : WorkspaceMapper::toView($record, (string) $record->workspace_role, (int) $record->active_member_count, $record->default_workspace_id !== null);
    }

    public function findDefault(AccountId $accountId): ?WorkspaceView
    {
        $record = WorkspaceRecord::query()
            ->join('platform_account_workspace_defaults', 'platform_account_workspace_defaults.workspace_id', '=', 'platform_workspaces.id')
            ->join('platform_workspace_memberships', function ($join) use ($accountId): void {
                $join->on('platform_workspace_memberships.workspace_id', '=', 'platform_workspaces.id')
                    ->where('platform_workspace_memberships.account_id', '=', $accountId->toString())
                    ->where('platform_workspace_memberships.status', '=', 'active');
            })
            ->where('platform_account_workspace_defaults.account_id', $accountId->toString())
            ->whereNull('platform_workspaces.archived_at')
            ->select('platform_workspaces.*', 'platform_workspace_memberships.role as workspace_role')
            ->addSelect(['active_member_count' => DB::table('platform_workspace_memberships as member_count')
                ->selectRaw('COUNT(*)')
                ->whereColumn('member_count.workspace_id', 'platform_workspaces.id')
                ->where('member_count.status', 'active')])
            ->first();

        return $record === null
            ? null
            : WorkspaceMapper::toView($record, (string) $record->workspace_role, (int) $record->active_member_count, true);
    }

    public function findFirstOwnedActive(AccountId $accountId): ?Workspace
    {
        $record = WorkspaceRecord::query()
            ->where('owner_id', $accountId->toString())
            ->whereNull('archived_at')
            ->orderBy('created_at')
            ->first();

        return $record === null ? null : WorkspaceMapper::toDomain($record);
    }

    public function assignDefault(AccountId $accountId, WorkspaceId $workspaceId): void
    {
        AccountWorkspaceDefaultRecord::query()->updateOrCreate(
            ['account_id' => $accountId->toString()],
            ['workspace_id' => $workspaceId->toString()],
        );
    }

    public function lockDefaultProvisioning(AccountId $accountId): void
    {
        AccountWorkspaceDefaultRecord::query()->firstOrCreate(
            ['account_id' => $accountId->toString()],
            ['workspace_id' => null],
        );

        AccountWorkspaceDefaultRecord::query()
            ->whereKey($accountId->toString())
            ->lockForUpdate()
            ->first();
    }

    public function find(WorkspaceId $id): ?Workspace
    {
        $record = WorkspaceRecord::query()->whereKey($id->toString())->first();
        return $record === null ? null : WorkspaceMapper::toDomain($record);
    }

    public function slugExists(string $slug, ?WorkspaceId $except = null): bool
    {
        return WorkspaceRecord::query()->where('slug', $slug)->when($except, fn ($query) => $query->where('id', '!=', $except->toString()))->exists();
    }

    public function save(Workspace $workspace, string $role = 'owner'): void
    {
        DB::transaction(function () use ($workspace, $role): void {
            $record = WorkspaceRecord::query()->firstOrNew(['id' => $workspace->id()->toString()]);
            $record->fill(WorkspaceMapper::attributes($workspace));
            if ($workspace->status()->value === 'archived') {
                $record->archived_at ??= now();
            } else {
                $record->archived_at = null;
            }
            $record->save();
            WorkspaceMembershipRecord::query()->updateOrCreate(
                ['workspace_id' => $workspace->id()->toString(), 'account_id' => $workspace->ownerId()->toString()],
                ['role' => $role, 'status' => 'active'],
            );
        });
    }
}
