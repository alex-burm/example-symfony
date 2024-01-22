<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function getPopularList(): array
    {
        $sql = '
            SELECT
                category.name,
                COUNT(post.id) as posts
            FROM category
            INNER JOIN post ON post.category_id = category.id
            GROUP BY category.id
        ';

        return $this->_em
            ->getConnection()
            ->prepare($sql)
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
