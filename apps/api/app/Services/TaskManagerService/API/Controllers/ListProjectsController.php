<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\ListProjectsRequest;
use App\Services\TaskManagerService\API\Resources\ProjectCollectionResource;
use App\Services\TaskManagerService\Application\DTOs\Input\ListProjectsDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('projects', methods: ['GET'], middleware: ['api', 'identity.introspect:account.read'], name: 'projects.index')]
#[Request(ListProjectsRequest::class, ListProjectsDTO::class)]
#[Service(TaskManagerHttpService::class, 'Projects retrieved.')]
#[Response(ProjectCollectionResource::class)]
#[Doc(summary: 'List the authenticated owner\'s projects', tags: ['Projects'])]
final class ListProjectsController extends Controller {}
