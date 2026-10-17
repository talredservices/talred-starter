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
        Schema::create('platform_teams', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('personal_owner_id')->nullable()->unique();
            $table->string('name', 120);
            $table->string('slug', 64)->unique();
            $table->string('status', 32)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('platform_team_memberships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('platform_teams')->cascadeOnDelete();
            $table->uuid('account_id')->index();
            $table->string('role', 32)->default('member');
            $table->string('status', 32)->default('active')->index();
            $table->timestamps();
            $table->unique(['team_id', 'account_id']);
        });

        Schema::table('platform_workspaces', function (Blueprint $table): void {
            $table->uuid('team_id')->nullable()->index();
            $table->foreign('team_id')->references('id')->on('platform_teams')->restrictOnDelete();
        });

        Schema::create('platform_projects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('platform_workspaces')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 64);
            $table->string('status', 32)->default('active')->index();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'slug']);
        });

        Schema::create('platform_identity_project_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('platform_project_id')->constrained('platform_projects')->cascadeOnDelete();
            $table->uuid('identity_project_id')->unique();
            $table->uuid('linked_by')->index();
            $table->timestamps();
        });

        Schema::create('platform_team_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('platform_teams')->cascadeOnDelete();
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

        $accounts = DB::table('platform_workspaces')->distinct()->pluck('owner_id')
            ->merge(DB::table('platform_workspace_memberships')->distinct()->pluck('account_id'));

        if (Schema::hasTable('platform_account_workspace_defaults')) {
            $accounts = $accounts->merge(DB::table('platform_account_workspace_defaults')->distinct()->pluck('account_id'));
        }

        $teamIdsByOwner = [];

        foreach ($accounts->filter()->map(static fn ($accountId): string => (string) $accountId)->unique() as $accountId) {
            $teamId = (string) Str::uuid();
            $suffix = substr(str_replace('-', '', $accountId), 0, 12);
            $now = now();

            DB::table('platform_teams')->insert([
                'id' => $teamId,
                'personal_owner_id' => $accountId,
                'name' => 'Personal Team',
                'slug' => 'personal-'.$suffix,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('platform_team_memberships')->insert([
                'id' => (string) Str::uuid(),
                'team_id' => $teamId,
                'account_id' => $accountId,
                'role' => 'owner',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $teamIdsByOwner[$accountId] = $teamId;
        }

        DB::table('platform_workspaces')->orderBy('id')->chunk(200, function ($workspaces) use ($teamIdsByOwner): void {
            foreach ($workspaces as $workspace) {
                $teamId = $teamIdsByOwner[(string) $workspace->owner_id] ?? null;
                if ($teamId === null) {
                    continue;
                }

                DB::table('platform_workspaces')->where('id', $workspace->id)->update(['team_id' => $teamId]);
                $now = now();

                $members = DB::table('platform_workspace_memberships')
                    ->where('workspace_id', $workspace->id)
                    ->get();

                foreach ($members as $member) {
                    if ($member->status === 'active') {
                        DB::table('platform_team_memberships')->insertOrIgnore([
                            'id' => (string) Str::uuid(),
                            'team_id' => $teamId,
                            'account_id' => $member->account_id,
                            'role' => 'member',
                            'status' => 'active',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    DB::table('platform_workspace_memberships')
                        ->where('id', $member->id)
                        ->update(['role' => $member->role === 'member' ? 'member' : 'admin']);
                }

                DB::table('platform_workspace_memberships')->insertOrIgnore([
                    'id' => (string) Str::uuid(),
                    'workspace_id' => $workspace->id,
                    'account_id' => $workspace->owner_id,
                    'role' => 'admin',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        foreach (DB::table('platform_workspace_memberships as membership')
            ->join('platform_workspaces as workspace', 'workspace.id', '=', 'membership.workspace_id')
            ->select(['membership.id', 'membership.account_id', 'workspace.owner_id', 'membership.role'])
            ->orderBy('membership.id')
            ->cursor() as $membership) {
            $role = (string) $membership->account_id === (string) $membership->owner_id
                ? 'owner'
                : ((string) $membership->role === 'admin' ? 'administrator' : (string) $membership->role);
            DB::table('platform_workspace_memberships')->where('id', $membership->id)->update(['role' => $role]);
        }
        Schema::table('platform_workspaces', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
        Schema::dropIfExists('platform_team_invitations');
        Schema::dropIfExists('platform_identity_project_links');
        Schema::dropIfExists('platform_projects');
        Schema::dropIfExists('platform_team_memberships');
        Schema::dropIfExists('platform_teams');
    }
};
