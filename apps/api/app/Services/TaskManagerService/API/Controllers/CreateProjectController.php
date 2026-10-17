<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\CreateProjectRequest;
use App\Services\TaskManagerService\API\Resources\ProjectResource;
use App\Services\TaskManagerService\Application\DTOs\Input\CreateProjectDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('projects', methods: ['POST'], middleware: ['api', 'identity.introspect:account.update'], name: 'projects.store')]
#[Request(CreateProjectRequest::class, CreateProjectDTO::class)]
#[Service(TaskManagerHttpService::class, 'Project created.', 201)]
#[Response(ProjectResource::class)]
#[Doc(summary: 'Create a project', tags: ['Projects'])]
final class CreateProjectController extends Controller {}
