<?php

namespace App\Repository;

use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Picture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Picture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Picture[]    findAll()
 * @method Picture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PictureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    public function findPaginatedPictures(int $page = 1)
    {
        //nombre de photos à afficher par page
        $numberOfPicturesPerPage = 30;

        //calcul de l'offset à partir du numéro de page de l'URL
        //offset : à partir de quelle ligne veut-on récupérer les résultats ?
        //si l'offset est égal à 60, alors on récupérera les photos #61 à #90
        $offset = ($page - 1) * $numberOfPicturesPerPage;

        //notre query builder
        $queryBuilder = $this->createQueryBuilder('p');

        //nombre max par page
        $queryBuilder->setMaxResults($numberOfPicturesPerPage);
        //on utilise l'offset (voir ci-dessus)
        $queryBuilder->setFirstResult($offset);

        //on tri, ici arbitrairement sur les likes
        $queryBuilder->addOrderBy('p.likes', 'DESC');

        //récupère l'objet Query de Doctrine
        $query = $queryBuilder->getQuery();

        //nos résultats pour cette page
        $result = $query->getResult();

        //ici, on souhaite connaître le nombre total de résultats SI on n'utilisait pas de limite
        //c'est utile pour savoir si on doit afficher le bouton vers la page suivante ou pas !
        //et aussi pour afficher à l'utilisateur le nombre total de résultats tout simplement...
        //pour faire ça, le plus simple est de modifier notre queryBuilder :

        //on enlève la limit et l'offset
        $queryBuilder->setMaxResults(null)->setFirstResult(null);
        //on ne sélectionne que le nombre total de résultat
        $queryBuilder->select('COUNT(p.id)');
        //on appelle cette méthode qui nous retourne directement le résultat, sans tableau autour
        $totalResultsCount = (int) $queryBuilder->getQuery()->getSingleScalarResult();

        //puisqu'on a plusieurs valeurs à return depuis la fonction, on met tout ça dans un tableau
        //toutes ces données sont utiles pour l'affichage des liens et des infos de pagination dans twig
        $data = [
            "numberOfResultsPerPage" => $numberOfPicturesPerPage,
            "totalResultsCount" => $totalResultsCount,
            "currentPage" => $page,
            "results" => $query->getResult(),
        ];

        return $data;
    }
}
