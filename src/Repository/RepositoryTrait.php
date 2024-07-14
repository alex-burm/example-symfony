<?php

namespace App\Repository;

trait RepositoryTrait
{
    protected function getStatement($sql)
    {
        return $this->getEntityManager()
            ->getConnection()
            ->prepare($sql);
    }
}
