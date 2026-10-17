<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkspaceRecord extends Model
{
    use HasUuids;

    protected $table = 'platform_workspaces';
    protected $fillable = ['id', 'owner_id', 'name', 'slug', 'status'];

    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceMembershipRecord::class, 'workspace_id');
    }
}
