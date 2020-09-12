<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\User;

final class UserRepository extends BaseRepository
{
    public function getUser(int $userId): object
    {
        $query = 'SELECT `id`, `name`, `email` FROM `users` WHERE `id` = :id';
        $statement = $this->database->prepare($query);
        $statement->bindParam('id', $userId);
        $statement->execute();
        $user = $statement->fetchObject();
        if (!$user) {
            throw new User('User not found.', 404);
        }

        return $user;
    }

    public function checkUserByEmail(string $email): void
    {
        $query = 'SELECT * FROM `users` WHERE `email` = :email';
        $statement = $this->database->prepare($query);
        $statement->bindParam('email', $email);
        $statement->execute();
        $user = $statement->fetchObject();
        if ($user) {
            throw new User('Email already exists.', 400);
        }
    }

    public function getUsersByPage(
        int $page,
        int $perPage,
        ?string $name,
        ?string $email
    ): array {
        $params = [
            'name' => is_null($name) ? '' : $name,
            'email' => is_null($email) ? '' : $email,
        ];
        $query = $this->getQueryUsersByPage();
        $statement = $this->database->prepare($query);
        $statement->bindParam('name', $params['name']);
        $statement->bindParam('email', $params['email']);
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

    public function getQueryUsersByPage(): string
    {
        return "
            SELECT `id`, `name`, `email`
            FROM `users`
            WHERE `name` LIKE CONCAT('%', :name, '%')
            AND `email` LIKE CONCAT('%', :email, '%')
            ORDER BY `id`
        ";
    }

    public function getAll(): array
    {
        $query = 'SELECT `id`, `name`, `email` FROM `users` ORDER BY `id`';
        $statement = $this->database->prepare($query);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function search(string $usersName): array
    {
        $query = '
            SELECT `id`, `name`, `email`
            FROM `users`
            WHERE `name` LIKE :name
            ORDER BY `id`
        ';
        $name = '%' . $usersName . '%';
        $statement = $this->database->prepare($query);
        $statement->bindParam('name', $name);
        $statement->execute();
        $users = $statement->fetchAll();
        if (!$users) {
            throw new User('User name not found.', 404);
        }

        return $users;
    }

    public function loginUser(string $email, string $password): object
    {
        $query = '
            SELECT *
            FROM `users`
            WHERE `email` = :email AND `password` = :password
            ORDER BY `id`
        ';
        $statement = $this->database->prepare($query);
        $statement->bindParam('email', $email);
        $statement->bindParam('password', $password);
        $statement->execute();
        $user = $statement->fetchObject();
        if (!$user) {
            throw new User('Login failed: Email or password incorrect.', 400);
        }

        return $user;
    }

    public function create(object $user): object
    {
        $this->database->beginTransaction();

        $query = '
            INSERT INTO `users`
                (`name`, `email`, `password`, `created_at`)
            VALUES
                (:name, :email, :password, :created_at)
        ';
        $statement = $this->database->prepare($query);
        $statement->bindParam('name', $user->name);
        $statement->bindParam('email', $user->email);
        $statement->bindParam('password', $user->password);
        $statement->bindParam('created_at', date('Y-m-d H:i:s'));
        $data = $statement->execute();

        if (!$data) {
            $this->database->rollBack();
            throw new User('Create failed: Input incorrect data.', 400);
        }

        return $this->getUser((int) $this->database->lastInsertId());
    }

    public function update(object $user): object
    {
        $this->database->beginTransaction();

        $query = '
            UPDATE `users` SET `name` = :name, `email` = :email, `updated_at` = :updated_at WHERE `id` = :id
        ';
        $statement = $this->database->prepare($query);
        $statement->bindParam('id', $user->id);
        $statement->bindParam('name', $user->name);
        $statement->bindParam('email', $user->email);
        $statement->bindParam('updated_at', date('Y-m-d H:i:s'));
        $data = $statement->execute();

        if (!$data) {
            $this->database->rollBack();
            throw new User('Update failed: Input incorrect data.', 400);
        }

        return $this->getUser((int) $user->id);
    }

    public function delete(int $userId): void
    {
        $query = 'DELETE FROM `users` WHERE `id` = :id';
        $statement = $this->database->prepare($query);
        $statement->bindParam('id', $userId);
        $statement->execute();
    }

    public function deleteUserTasks(int $userId): void
    {
        $query = 'DELETE FROM `tasks` WHERE `userId` = :userId';
        $statement = $this->database->prepare($query);
        $statement->bindParam('userId', $userId);
        $statement->execute();
    }
}
