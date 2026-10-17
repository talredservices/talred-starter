<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class WorkspaceMembershipRecord extends Model
{
    use HasUuids;

    protected $table = 'platform_workspace_memberships';
    protected $fillable = ['id', 'workspace_id', 'account_id', 'role', 'status'];
}
