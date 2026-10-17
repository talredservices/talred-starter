<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

final class AccountWorkspaceDefaultRecord extends Model
{
    protected $table = 'platform_account_workspace_defaults';
    protected $primaryKey = 'account_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['account_id', 'workspace_id'];
}
