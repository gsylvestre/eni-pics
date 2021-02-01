<?php

namespace App\Repository;

use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    /**
     * Retourne des photos contenant l'un ou l'autre des tags de la photo reçue en argument
     *
     * @param Picture $picture
     * @return int|mixed|string
     */
    public function findSimilarPictures(Picture $picture)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        //jointure sur les tags pour accéder à l'alias plus loin
        $queryBuilder->join('p.tags', 't');

        //fait un WHERE IN en passant directement le tableau de tags !
        //doctrine va se débrouiller avec les ids tout seul
        $queryBuilder->andWhere('t.id IN (:tagIds)')->setParameter(':tagIds', $picture->getTags());

        //exclut la photo actuelle des résultats
        $queryBuilder->andWhere('p != :currentPic')->setParameter(':currentPic', $picture);

        //200 photos max... c'est bcp, mais on ne va en garder que 20 au hasard,
        //pour que ça change, sinon c'est toujours les mêmes...
        $queryBuilder->setMaxResults(200);

        $query = $queryBuilder->getQuery();
        $results = $query->getResult();

        //on les mélange
        shuffle($results);
        //on n'en garde que 20
        $pics = array_splice($results, 0, 20);

        return $pics;
    }
}
