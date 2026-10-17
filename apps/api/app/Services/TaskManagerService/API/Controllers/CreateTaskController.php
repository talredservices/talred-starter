<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Controllers;

use App\Services\TaskManagerService\API\Requests\CreateTaskRequest;
use App\Services\TaskManagerService\API\Resources\TaskResource;
use App\Services\TaskManagerService\Application\DTOs\Input\CreateTaskDTO;
use App\Services\TaskManagerService\API\Services\TaskManagerHttpService;
use Zolta\Http\Controller\Controller;
use Zolta\Http\Request\Attributes\Request;
use Zolta\Http\Response\Attributes\Response;
use Zolta\Http\Router\Attributes\Route;
use Zolta\Http\Service\Attributes\Doc;
use Zolta\Http\Service\Attributes\Service;

#[Route('projects/{projectId}/tasks', methods: ['POST'], middleware: ['api', 'identity.introspect:account.update'], name: 'projects.tasks.store')]
#[Request(CreateTaskRequest::class, CreateTaskDTO::class)]
#[Service(TaskManagerHttpService::class, 'Task created.', 201)]
#[Response(TaskResource::class)]
#[Doc(summary: 'Create a task in an owned project', tags: ['Tasks'])]
final class CreateTaskController extends Controller {}
