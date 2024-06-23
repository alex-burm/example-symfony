<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getAllItems(): array
    {
        $sql = 'SELECT * FROM post';
        $stm = $this->getEntityManager()->getConnection()->prepare($sql);
        return $stm->executeQuery()->fetchAllAssociative();
    }

    public function getPostListQuery(): string
    {
        $sql = '
            SELECT SQL_CALC_FOUND_ROWS 
                post.*,
                category.name AS category
            FROM post
            INNER JOIN category ON post.category_id = category.id
        ';
        return $sql;
    }
}
