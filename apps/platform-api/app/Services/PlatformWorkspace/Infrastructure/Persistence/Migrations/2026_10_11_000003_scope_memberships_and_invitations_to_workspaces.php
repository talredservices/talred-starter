<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_workspace_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('platform_workspaces')->cascadeOnDelete();
            $table->string('email', 254)->index();
            $table->string('role', 32)->default('member');
            $table->string('token_hash', 64)->unique();
            $table->text('identity_invitation_token');
            $table->uuid('created_by')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        DB::table('platform_workspaces')->orderBy('id')->chunk(200, function ($workspaces): void {
            foreach ($workspaces as $workspace) {
                $now = now();
                $teamMembers = DB::table('platform_team_memberships')
                    ->where('team_id', $workspace->team_id)
                    ->where('status', 'active')
                    ->get();

                foreach ($teamMembers as $teamMember) {
                    $accountId = (string) $teamMember->account_id;
                    $existing = DB::table('platform_workspace_memberships')
                        ->where('workspace_id', $workspace->id)
                        ->where('account_id', $accountId)
                        ->first();

                    $role = $accountId === (string) $workspace->owner_id
                        ? 'owner'
                        : (in_array($teamMember->role, ['owner', 'admin'], true)
                            || ($existing !== null && $existing->role === 'admin')
                            ? 'admin'
                            : 'member');

                    if ($existing === null) {
                        DB::table('platform_workspace_memberships')->insert([
                            'id' => (string) Str::uuid(),
                            'workspace_id' => $workspace->id,
                            'account_id' => $accountId,
                            'role' => $role,
                            'status' => 'active',
                            'created_at' => $teamMember->created_at ?? $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('platform_workspace_memberships')->where('id', $existing->id)->update([
                            'role' => $role,
                            'status' => 'active',
                            'updated_at' => $now,
                        ]);
                    }
                }

                $ownerMembership = DB::table('platform_workspace_memberships')
                    ->where('workspace_id', $workspace->id)
                    ->where('account_id', $workspace->owner_id)
                    ->first();

                if ($ownerMembership === null) {
                    DB::table('platform_workspace_memberships')->insert([
                        'id' => (string) Str::uuid(),
                        'workspace_id' => $workspace->id,
                        'account_id' => $workspace->owner_id,
                        'role' => 'owner',
                        'status' => 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('platform_workspace_memberships')->where('id', $ownerMembership->id)->update([
                        'role' => 'owner',
                        'status' => 'active',
                        'updated_at' => $now,
                    ]);
                }
            }
        });

        Schema::table('platform_workspaces', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropIndex(['team_id']);
            $table->dropColumn('team_id');
        });

        // A pending Team invite had no single Workspace scope. It cannot safely
        // become an invitation to every Workspace, so it is invalidated here.
        // Any linked Identity login invitation grants login-project admission
        // only; it does not create Platform Workspace access.
        Schema::dropIfExists('platform_team_invitations');
        Schema::dropIfExists('platform_team_memberships');
        Schema::dropIfExists('platform_teams');
    }

    public function down(): void
    {
        throw new RuntimeException('This migration removes Team-scoped access and invitations and cannot be safely reversed.');
    }
};
