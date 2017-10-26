<?php
/**
 * Created by PhpStorm.
 * User: wilder11
 * Date: 25/10/17
 * Time: 10:50
 */

namespace AuPenDuick\Model;

class CompanyPictureManager extends EntityManager
{

    public function findAll()
    {
        $statement = $this->pdo->query('SELECT * FROM companyPictures');
        return $statement->fetchAll(\PDO::FETCH_CLASS, \AuPenDuick\Model\CompanyPicture::class);
    }

    public function findByCategory($id)
    {
        $query = "SELECT * FROM companyPictures WHERE id=:id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_CLASS, \AuPenDuick\Model\CompanyPicture::class);
    }

    public function findOne($id)
    {
        $query = "SELECT * FROM companyPictures WHERE id=:id";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
        $picture = $statement->fetchAll(\PDO::FETCH_CLASS, \AuPenDuick\Model\CompanyPicture::class);
        return $picture[0];
    }

    public function deleteById($id)
    {
        $query = 'DELETE FROM companyPictures WHERE id=:id';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();
    }

    public function insertCompanyPicture($name, $extension)
    {
        $query = 'UPDATE companyPictures SET name=:name, extension=:extension';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':name', $name);
        $statement->bindValue(':extension', $extension);
        $statement->execute();
    }

}