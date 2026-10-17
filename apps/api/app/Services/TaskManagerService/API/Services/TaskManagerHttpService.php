<?php

declare(strict_types=1);

namespace App\Services\TaskManagerService\API\Services;

use App\Services\TaskManagerService\Application\DTOs\Input\CreateProjectDTO;
use App\Services\TaskManagerService\Application\DTOs\Input\CreateTaskDTO;
use App\Services\TaskManagerService\Application\DTOs\Input\DeleteProjectDTO;
use App\Services\TaskManagerService\Application\DTOs\Input\DeleteTaskDTO;
use App\Services\TaskManagerService\Application\DTOs\Input\ListProjectsDTO;
use App\Services\TaskManagerService\Application\DTOs\Input\ListProjectTasksDTO;
use App\Services\TaskManagerService\Application\DTOs\Input\UpdateTaskDTO;
use App\Services\TaskManagerService\Application\Services\Projects\CreateProjectService;
use App\Services\TaskManagerService\Application\Services\Projects\DeleteProjectService;
use App\Services\TaskManagerService\Application\Services\Projects\ListProjectsService;
use App\Services\TaskManagerService\Application\Services\Tasks\CreateTaskService;
use App\Services\TaskManagerService\Application\Services\Tasks\DeleteTaskService;
use App\Services\TaskManagerService\Application\Services\Tasks\ListProjectTasksService;
use App\Services\TaskManagerService\Application\Services\Tasks\UpdateTaskService;
use App\Services\TaskManagerService\Domain\Exceptions\InvalidTaskTransition;
use App\Services\TaskManagerService\Domain\Exceptions\ProjectKeyAlreadyExists;
use App\Services\TaskManagerService\Domain\Exceptions\ProjectNotFound;
use App\Services\TaskManagerService\Domain\Exceptions\TaskNotFound;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

/** Translate use-case failures before the attribute router renders its response. */
final readonly class TaskManagerHttpService
{
    public function __construct(
        private CreateProjectService $createProject,
        private DeleteProjectService $deleteProject,
        private ListProjectsService $listProjects,
        private CreateTaskService $createTask,
        private DeleteTaskService $deleteTask,
        private ListProjectTasksService $listProjectTasks,
        private UpdateTaskService $updateTask,
    ) {}

    public function __invoke(CreateProjectDTO|DeleteProjectDTO|ListProjectsDTO|CreateTaskDTO|DeleteTaskDTO|ListProjectTasksDTO|UpdateTaskDTO $input): ResponseDTO
    {
        try {
            return match (true) {
                $input instanceof CreateProjectDTO => ($this->createProject)($input),
                $input instanceof DeleteProjectDTO => ($this->deleteProject)($input),
                $input instanceof ListProjectsDTO => ($this->listProjects)($input),
                $input instanceof CreateTaskDTO => ($this->createTask)($input),
                $input instanceof DeleteTaskDTO => ($this->deleteTask)($input),
                $input instanceof ListProjectTasksDTO => ($this->listProjectTasks)($input),
                $input instanceof UpdateTaskDTO => ($this->updateTask)($input),
            };
        } catch (ProjectNotFound|TaskNotFound $exception) {
            throw new HttpException(404, $exception->getMessage(), $exception);
        } catch (InvalidTaskTransition|ProjectKeyAlreadyExists $exception) {
            throw new HttpException(409, $exception->getMessage(), $exception);
        } catch (InvalidArgumentException $exception) {
            throw new HttpException(422, $exception->getMessage(), $exception);
        }
    }
}
