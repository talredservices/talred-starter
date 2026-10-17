<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\API\Controllers;

use App\Services\PlatformWorkspace\API\Requests\ListWorkspacesRequest;
use App\Services\PlatformWorkspace\API\Resources\WorkspaceCollectionResource;
use App\Services\PlatformWorkspace\Application\DTOs\Input\ListWorkspacesDTO;
use App\Services\PlatformWorkspace\API\Services\WorkspaceHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('platform/v1/workspaces', methods: ['GET'], middleware: ['api', 'identity.introspect'], name: 'platform.workspaces.index')]
#[Request(ListWorkspacesRequest::class, ListWorkspacesDTO::class)]
#[Service(WorkspaceHttpService::class, 'Workspaces retrieved.')]
#[Response(WorkspaceCollectionResource::class)]
#[Doc(summary: 'List workspaces accessible to the authenticated account', tags: ['Platform Workspaces'])]
final class ListWorkspacesController extends Controller {}
