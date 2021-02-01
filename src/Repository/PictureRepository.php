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

    public function findPhotographer(string $name): array
    {
        //on cherche des photographes "distinct" dans la table des photos
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('DISTINCT(p.photographer)');

        //en fonction du nom reçu en argument
        $queryBuilder->andWhere('p.photographer LIKE :photographer')
            ->setParameter(':photographer', "%" . $name . "%");

        $query = $queryBuilder->getQuery();
        $query->setMaxResults(15);

        //on récupère les données sous forme de tableau
        $result = $query->getArrayResult();

        //il y a trop de niveaux de tableaux... je ne garde que ce qui est sous la clé "1" dans les sous-tableaux
        return array_column($result, "1");
    }
}
