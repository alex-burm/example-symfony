<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
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
    use RepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getAllItems(): array
    {
        $sql = 'SELECT * FROM post';
        $stm = $this->getStatement($sql);
        return $stm->executeQuery()->fetchAllAssociative();
    }

    public function getPostListQuery(?string $keyword = null): string
    {
        $sql = '
            SELECT SQL_CALC_FOUND_ROWS 
                post.*,
                category.name AS category
            FROM post
            INNER JOIN category ON post.category_id = category.id
        ';

        if (strlen($keyword ?? '') > 0) {
            $sql .= ' WHERE post.name LIKE "%' . addslashes(htmlspecialchars($keyword)) . '%"';
        }

        $sql .= ' ORDER BY post.published_at DESC, post.id DESC';
        return $sql;
    }

    public function getNextPosts(int $id, string $publishedAt): array
    {
        $sql = '
            SELECT 
                post.*,
                category.name AS category
            FROM post
            INNER JOIN category ON post.category_id = category.id
            WHERE 
                post.published_at <= :publishedAt
                AND post.id < :id
            ORDER BY post.published_at DESC, post.id DESC
            LIMIT 3
        ';

        $stm = $this->getStatement($sql);
        $stm->bindValue(':id', $id, ParameterType::INTEGER);
        $stm->bindValue(':publishedAt', $publishedAt);
        return $stm->executeQuery()->fetchAllAssociative();
    }
}
