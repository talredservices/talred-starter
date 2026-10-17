<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Controllers;

use App\Services\PlatformWorkspace\API\Requests\GetWorkspaceRequest;
use App\Services\PlatformWorkspace\API\Resources\WorkspaceResource;
use App\Services\PlatformWorkspace\Application\DTOs\Input\GetWorkspaceDTO;
use App\Services\PlatformWorkspace\API\Services\WorkspaceHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('platform/v1/workspaces/{workspaceId}', methods: ['GET'], middleware: ['api', 'identity.introspect'], name: 'platform.workspaces.show')]
#[Request(GetWorkspaceRequest::class, GetWorkspaceDTO::class)]
#[Service(WorkspaceHttpService::class, 'Workspace retrieved.')]
#[Response(WorkspaceResource::class)]
#[Doc(summary: 'Retrieve an accessible workspace for the authenticated account', tags: ['Platform Workspaces'])]
final class GetWorkspaceController extends Controller {}
