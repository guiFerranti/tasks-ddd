<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CreateTaskDTO;
use App\Application\DTOs\ListTasksDTO;
use App\Application\DTOs\UpdateTaskDTO;
use App\Application\UseCases\Tasks\CreateTaskUseCase;
use App\Application\UseCases\Tasks\DeleteTaskUseCase;
use App\Application\UseCases\Tasks\GetTaskByIdUseCase;
use App\Application\UseCases\Tasks\ListDeletedTasksUseCase;
use App\Application\UseCases\Tasks\ListTasksUseCase;
use App\Application\UseCases\Tasks\UpdateTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Users\Entities\User;
use App\Http\Resources\TaskResource;
use App\Infrastructure\Http\Validators\Tasks\CreateTaskValidator;
use App\Infrastructure\Http\Validators\Tasks\ListTasksValidator;
use App\Infrastructure\Http\Validators\Tasks\UpdateTaskValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * @group Tarefas
 *
 * Endpoints para gerenciamento de tarefas (CRUD, listagem com filtros).
 */
class TaskController extends Controller
{
    /**
     * Criar Nova Tarefa
     *
     * Cria uma nova tarefa atribuída a um usuário específico.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @bodyParam title string required Título da tarefa. Example: Reunião de equipe
     * @bodyParam description string required Descrição detalhada. Example: Discutir planejamento do próximo trimestre
     * @bodyParam status string required Status inicial (pending, in_progress, completed). Example: pending
     * @bodyParam assigned_to integer required ID do usuário atribuído. Example: 2
     *
     * @response 201 {
     *   "id": 1,
     *   "title": "Reunião de equipe",
     *   "status": "pending",
     *   "assigned_to": 2,
     *   "created_by": 1,
     *   "created_at": "2024-05-20T00:00:00.000000Z"
     * }
     * @response 400 {
     *   "error": "Usuário atribuído não encontrado"
     * }
     * @response 422 {
     *   "errors": {
     *     "title": ["O campo título é obrigatório"]
     *   }
     * }
     */
    public function store(CreateTaskValidator $request, CreateTaskUseCase $useCase)
    {
        try {
            $validated = $request->validated();
            $dto = CreateTaskDTO::fromValidatedData($validated);

            $task = $useCase->execute(auth()->user(), $dto);
            return response()->json($task, 201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Usuário atribuído não encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Atualizar Tarefa
     *
     * Atualiza informações de uma tarefa existente.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @urlParam task integer required ID da tarefa. Example: 1
     * @bodyParam title string Título. Example: Reunião atualizada
     * @bodyParam description string Descrição. Example: Novo tópico: Orçamento
     * @bodyParam status string Status (pending, in_progress, completed). Example: in_progress
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "Reunião atualizada",
     *   "status": "in_progress"
     * }
     * @response 403 {
     *   "error": "Apenas o criador pode editar a tarefa"
     * }
     */
    public function update(UpdateTaskValidator $request, Task $task, UpdateTaskUseCase $useCase)
    {
        try {
            $validated = $request->validated();
            $dto = UpdateTaskDTO::fromValidatedData($validated);

            $updatedTask = $useCase->execute($task, $dto);
            return new TaskResource($updatedTask);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Excluir Tarefa (Admin)
     *
     * Remove uma tarefa do sistema (soft delete, apenas para administradores).
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @urlParam task integer required ID da tarefa. Example: 1
     *
     * @response 204
     * @response 403 {
     *   "error": "Acesso negado: apenas administradores"
     * }
     * @response 400 {
     *   "error": "Não é possível excluir tarefas concluídas"
     * }
     */
    public function destroy(Task $task, DeleteTaskUseCase $useCase)
    {
        try {
            $useCase->execute(auth()->user(), $task);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Listar Tarefas com Filtros
     *
     * Lista tarefas com filtragem avançada.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @queryParam assignedTo integer Filtrar por usuário atribuído. Example: 2
     * @queryParam status string Filtrar por status. Example: pending
     * @queryParam createdAfter date Filtrar por data de criação (YYYY-MM-DD). Example: 2024-05-01
     *
     * @response 200 [{
     *   "id": 1,
     *   "title": "Reunião de equipe",
     *   "status": "pending",
     *   "assigned_to": 2
     * }]
     */
    public function index(ListTasksValidator $request, ListTasksUseCase $useCase)
    {
        try {
            $dto = ListTasksDTO::fromValidatedData($request->validated());
            $tasks = $useCase->execute($dto);
            return response()->json($tasks);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    /**
     * Listar Tarefas Excluídas (Admin)
     *
     * Lista tarefas removidas via soft delete (apenas administradores).
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @queryParam assignedTo integer Filtrar por usuário atribuído. Example: 2
     * @queryParam status string Filtrar por status antes da exclusão. Example: completed
     * @queryParam createdAfter date Filtrar por data de criação (YYYY-MM-DD). Example: 2024-05-01
     *
     * @response 200 [{
     *   "id": 1,
     *   "title": "Tarefa excluída",
     *   "deleted_at": "2024-05-20T12:00:00.000000Z"
     * }]
     */
    public function indexDeleted(ListTasksValidator $request, ListDeletedTasksUseCase $useCase)
    {
        try {
            $dto = ListTasksDTO::fromValidatedData($request->validated());
            $tasks = $useCase->execute($dto);
            return response()->json($tasks);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao listar tarefas excluídas'], 500);
        }
    }

    /**
     * Detalhes da Tarefa
     *
     * Retorna informações detalhadas de uma tarefa específica.
     *
     * @authenticated
     * @header Authorization Bearer {token}
     * @urlParam id integer required ID da tarefa. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "Reunião de equipe",
     *   "description": "Discutir planejamento",
     *   "status": "pending",
     *   "created_by": 1,
     *   "assigned_to": 2
     * }
     * @response 404 {
     *   "error": "Tarefa não encontrada"
     * }
     */
    public function show(int $id, GetTaskByIdUseCase $useCase)
    {
        $task = $useCase->execute($id);

        if (!$task) {
            return response()->json(['error' => 'Tarefa não encontrada'], 404);
        }

        return new TaskResource($task);
    }
}
