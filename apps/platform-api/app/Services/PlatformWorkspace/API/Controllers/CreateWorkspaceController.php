<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Controllers;

use App\Services\PlatformWorkspace\API\Requests\CreateWorkspaceRequest;
use App\Services\PlatformWorkspace\API\Resources\WorkspaceResource;
use App\Services\PlatformWorkspace\Application\DTOs\Input\CreateWorkspaceDTO;
use App\Services\PlatformWorkspace\API\Services\WorkspaceHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('platform/v1/workspaces', methods: ['POST'], middleware: ['api', 'identity.introspect'], name: 'platform.workspaces.store')]
#[Request(CreateWorkspaceRequest::class, CreateWorkspaceDTO::class)]
#[Service(WorkspaceHttpService::class, 'Workspace created.', 201)]
#[Response(WorkspaceResource::class)]
#[Doc(summary: 'Create a workspace for the authenticated account', tags: ['Platform Workspaces'])]
final class CreateWorkspaceController extends Controller {}
