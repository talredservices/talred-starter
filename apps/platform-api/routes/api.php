<?php

use App\Services\PlatformOrganization\API\Controllers\PlatformOrganizationController;
use App\Services\PlatformWorkspace\API\Controllers\ListIdentityApplicationDirectoryController;
use App\Services\PlatformWorkspace\API\Controllers\ListIdentityProjectDirectoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('platform/v1')->middleware('identity.introspect')->group(function (): void {
    Route::get('identity-projects', ListIdentityProjectDirectoryController::class);
    Route::get('identity-applications', ListIdentityApplicationDirectoryController::class);
    Route::post('invitations/accept', [PlatformOrganizationController::class, 'acceptWorkspaceInvitation']);
    Route::get('workspaces/{workspaceId}', [PlatformOrganizationController::class, 'workspace']);
    Route::patch('workspaces/{workspaceId}', [PlatformOrganizationController::class, 'updateWorkspace']);
    Route::get('workspaces/{workspaceId}/overview', [PlatformOrganizationController::class, 'workspaceOverview']);
    Route::get('workspaces/{workspaceId}/members', [PlatformOrganizationController::class, 'workspaceMembers']);
    Route::patch('workspaces/{workspaceId}/members/{accountId}', [PlatformOrganizationController::class, 'updateWorkspaceMember']);
    Route::delete('workspaces/{workspaceId}/members/{accountId}', [PlatformOrganizationController::class, 'removeWorkspaceMember']);
    Route::post('workspaces/{workspaceId}/ownership', [PlatformOrganizationController::class, 'transferWorkspaceOwnership']);
    Route::get('workspaces/{workspaceId}/invitations', [PlatformOrganizationController::class, 'workspaceInvitations']);
    Route::post('workspaces/{workspaceId}/invitations', [PlatformOrganizationController::class, 'createWorkspaceInvitation']);
    Route::delete('workspaces/{workspaceId}/invitations/{invitationId}', [PlatformOrganizationController::class, 'revokeWorkspaceInvitation']);
    Route::get('workspaces/{workspaceId}/resources/identity-projects', [PlatformOrganizationController::class, 'workspaceIdentityProjects']);
    Route::get('workspaces/{workspaceId}/resources/identity-projects/available', [PlatformOrganizationController::class, 'availableIdentityProjects']);
    Route::post('workspaces/{workspaceId}/resources/identity-projects', [PlatformOrganizationController::class, 'linkIdentityProject']);
    Route::delete('workspaces/{workspaceId}/resources/identity-projects/{identityProjectId}', [PlatformOrganizationController::class, 'unlinkIdentityProject']);
});
