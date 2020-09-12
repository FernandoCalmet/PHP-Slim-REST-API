<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\Task;

final class TaskRepository extends BaseRepository
{
    public function getQueryTasksByPage(): string
    {
        return "
            SELECT *
            FROM `tasks`
            WHERE `userId` = :userId
            AND `name` LIKE CONCAT('%', :name, '%')
            AND `description` LIKE CONCAT('%', :description, '%')
            AND `status` LIKE CONCAT('%', :status, '%')
            ORDER BY `id`
        ";
    }

    public function getTasksByPage(
        int $userId,
        int $page,
        int $perPage,
        ?string $name,
        ?string $description,
        ?string $status
    ): array {
        $params = [
            'userId' => $userId,
            'name' => is_null($name) ? '' : $name,
            'description' => is_null($description) ? '' : $description,
            'status' => is_null($status) ? '' : $status,
        ];
        $query = $this->getQueryTasksByPage();
        $statement = $this->database->prepare($query);
        $statement->bindParam('userId', $params['userId']);
        $statement->bindParam('name', $params['name']);
        $statement->bindParam('description', $params['description']);
        $statement->bindParam('status', $params['status']);
        $statement->execute();
        $total = $statement->rowCount();

        return $this->getResultsWithPagination(
            $query,
            $page,
            $perPage,
            $params,
            $total
        );
    }

    public function checkAndGetTask(int $taskId, int $userId): object
    {
        $query = '
            SELECT * FROM `tasks` WHERE `id` = :id AND `userId` = :userId
        ';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $taskId);
        $statement->bindParam('userId', $userId);
        $statement->execute();
        $task = $statement->fetchObject();
        if (!$task) {
            throw new Task('Task not found.', 404);
        }

        return $task;
    }

    public function getAllTasks(): array
    {
        $query = 'SELECT * FROM `tasks` ORDER BY `id`';
        $statement = $this->getDb()->prepare($query);
        $statement->execute();

        return (array) $statement->fetchAll();
    }

    public function getAll(int $userId): array
    {
        $query = 'SELECT * FROM `tasks` WHERE `userId` = :userId ORDER BY `id`';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('userId', $userId);
        $statement->execute();

        return (array) $statement->fetchAll();
    }

    public function search(string $tasksName, int $userId, ?int $status): array
    {
        $query = $this->getSearchTasksQuery($status);
        $name = '%' . $tasksName . '%';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('name', $name);
        $statement->bindParam('userId', $userId);
        if ($status === 0 || $status === 1) {
            $statement->bindParam('status', $status);
        }
        $statement->execute();

        return (array) $statement->fetchAll();
    }

    public function create(object $task): object
    {
        $this->database->beginTransaction();

        $query = '
            INSERT INTO `tasks`
                (`name`, `description`, `status`, `userId`)
            VALUES
                (:name, :description, :status, :userId)
        ';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('name', $task->name);
        $statement->bindParam('description', $task->description);
        $statement->bindParam('status', $task->status);
        $statement->bindParam('userId', $task->userId);
        $data = $statement->execute();

        if (!$data) {
            $this->database->rollBack();
            throw new Task('Create failed: Input incorrect data.', 400);
        }

        $taskId = (int) $this->database->lastInsertId();

        return $this->checkAndGetTask($taskId, (int) $task->userId);
    }

    public function update(object $task): object
    {
        $this->database->beginTransaction();

        $query = '
            UPDATE `tasks`
            SET `name` = :name, `description` = :description, `status` = :status
            WHERE `id` = :id AND `userId` = :userId
        ';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $task->id);
        $statement->bindParam('name', $task->name);
        $statement->bindParam('description', $task->description);
        $statement->bindParam('status', $task->status);
        $statement->bindParam('userId', $task->userId);
        $data = $statement->execute();

        if (!$data) {
            $this->database->rollBack();
            throw new Task('Update failed: Input incorrect data.', 400);
        }

        return $this->checkAndGetTask((int) $task->id, (int) $task->userId);
    }

    public function delete(int $taskId, int $userId): void
    {
        $query = 'DELETE FROM `tasks` WHERE `id` = :id AND `userId` = :userId';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $taskId);
        $statement->bindParam('userId', $userId);
        $statement->execute();
    }

    private function getSearchTasksQuery(?int $status): string
    {
        $statusQuery = '';
        if ($status === 0 || $status === 1) {
            $statusQuery = 'AND `status` = :status';
        }

        return "
            SELECT * FROM `tasks`
            WHERE `name` LIKE :name AND `userId` = :userId ${statusQuery}
            ORDER BY `id`
        ";
    }
}
