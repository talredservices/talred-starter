<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_workspace_resources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('workspace_id')->constrained('platform_workspaces')->cascadeOnDelete();
            $table->string('resource_type', 64);
            $table->string('external_resource_id', 191);
            $table->uuid('linked_by')->index();
            $table->timestamps();

            $table->unique(['resource_type', 'external_resource_id'], 'platform_workspace_resources_external_unique');
            $table->index(['workspace_id', 'resource_type'], 'platform_workspace_resources_section_index');
        });

        DB::table('platform_identity_project_links as link')
            ->join('platform_projects as project', 'project.id', '=', 'link.platform_project_id')
            ->select([
                'link.id',
                'project.workspace_id',
                'link.identity_project_id',
                'link.linked_by',
                'link.created_at',
                'link.updated_at',
            ])
            ->orderBy('link.id')
            ->chunk(200, static function ($links): void {
                foreach ($links as $link) {
                    DB::table('platform_workspace_resources')->insert([
                        'id' => (string) $link->id,
                        'workspace_id' => (string) $link->workspace_id,
                        'resource_type' => 'identity_project',
                        'external_resource_id' => (string) $link->identity_project_id,
                        'linked_by' => (string) $link->linked_by,
                        'created_at' => $link->created_at,
                        'updated_at' => $link->updated_at,
                    ]);
                }
            });

        // Keep the original containers and link rows as a rollback archive.
        // They are no longer queried by the application; down() restores them.
        Schema::rename('platform_identity_project_links', 'platform_identity_project_links_legacy');
        Schema::rename('platform_projects', 'platform_projects_legacy');
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_workspace_resources');

        Schema::rename('platform_projects_legacy', 'platform_projects');
        Schema::rename('platform_identity_project_links_legacy', 'platform_identity_project_links');
    }
};
