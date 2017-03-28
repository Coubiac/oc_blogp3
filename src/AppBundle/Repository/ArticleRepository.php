<?php

namespace AppBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */

use Doctrine\ORM\Tools\Pagination\Paginator;


class ArticleRepository extends \Doctrine\ORM\EntityRepository
{

    public function getArticlesPaginated($page, $nbPerPage)
    {
        $qb = $this->createQueryBuilder('a')->where('a.date <= :now')->setParameter(':now', new \DateTime());
        $query = $qb->getQuery();
        $query
            // On définit l'annonce à partir de laquelle commencer la liste
            ->setFirstResult(($page - 1) * $nbPerPage)
            // Ainsi que le nombre d'annonce à afficher sur une page
            ->setMaxResults($nbPerPage);
        // Enfin, on retourne l'objet Paginator correspondant à la requête construite
        // (n'oubliez pas le use correspondant en début de fichier)
        return new Paginator($query, true);
    }

    public function findAllAsc()
    {
        return $this->findBy(array(), array('date' => 'ASC'));
    }
}
