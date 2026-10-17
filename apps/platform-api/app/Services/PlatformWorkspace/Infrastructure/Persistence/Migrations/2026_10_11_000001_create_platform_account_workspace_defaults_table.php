<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_account_workspace_defaults', function (Blueprint $table): void {
            $table->uuid('account_id')->primary();
            $table->uuid('workspace_id')->nullable()->index();
            $table->foreign('workspace_id')->references('id')->on('platform_workspaces')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_account_workspace_defaults');
    }
};
