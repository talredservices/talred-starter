<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_workspaces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('owner_id')->index();
            $table->string('name', 120);
            $table->string('slug', 64)->unique();
            $table->string('status', 32)->default('active')->index();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_workspace_memberships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->index();
            $table->uuid('account_id')->index();
            $table->string('role', 32)->default('member');
            $table->string('status', 32)->default('active')->index();
            $table->timestamps();
            $table->unique(['workspace_id', 'account_id']);
            $table->foreign('workspace_id')->references('id')->on('platform_workspaces')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_workspace_memberships');
        Schema::dropIfExists('platform_workspaces');
    }
};
