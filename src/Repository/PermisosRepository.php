<?php declare(strict_types=1);

namespace App\Repository;

use App\Exception\PermisosException;

class PermisosRepository extends BaseRepository
{
    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }

    public function checkAndGetPermisos(int $permisosId)
    {
        $query = 'SELECT * FROM `permisos` WHERE `id` = :id';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $permisosId);
        $statement->execute();
        $permisos = $statement->fetchObject();
        if (empty($permisos)) {
            throw new PermisosException('Permisos not found.', 404);
        }

        return $permisos;
    }

    public function getAllPermisos(): array
    {
        $query = 'SELECT * FROM `permisos` ORDER BY `id`';
        $statement = $this->getDb()->prepare($query);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function createPermisos($permisos)
    {
        $query = 'INSERT INTO `permisos` (`id`, `id_rol`, `id_operacion`) VALUES (:id, :id_rol, :id_operacion)';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $permisos->id);
	$statement->bindParam('id_rol', $permisos->id_rol);
	$statement->bindParam('id_operacion', $permisos->id_operacion);
        $statement->execute();

        return $this->checkAndGetPermisos((int) $this->getDb()->lastInsertId());
    }

    public function updatePermisos($permisos, $data)
    {
        if (isset($data->id_rol)) { $permisos->id_rol = $data->id_rol; }
        if (isset($data->id_operacion)) { $permisos->id_operacion = $data->id_operacion; }

        $query = 'UPDATE `permisos` SET `id_rol` = :id_rol, `id_operacion` = :id_operacion WHERE `id` = :id';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $permisos->id);
	$statement->bindParam('id_rol', $permisos->id_rol);
	$statement->bindParam('id_operacion', $permisos->id_operacion);
        $statement->execute();

        return $this->checkAndGetPermisos((int) $permisos->id);
    }

    public function deletePermisos(int $permisosId)
    {
        $query = 'DELETE FROM `permisos` WHERE `id` = :id';
        $statement = $this->getDb()->prepare($query);
        $statement->bindParam('id', $permisosId);
        $statement->execute();
    }
}