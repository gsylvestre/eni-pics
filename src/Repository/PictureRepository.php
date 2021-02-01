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

    public function search(string $keywords = null)
    {
        //crée notre querybuilder avec un alias de p pour Picture
        $queryBuilder = $this->createQueryBuilder('p');

        //seulement si on a reçu des mots-clefs...
        if ($keywords) {
            $queryBuilder->andWhere('p.title LIKE :kw OR p.description LIKE :kw')
                    ->setParameter(':kw', "%$keywords%");
        }

        //limite à 30 résultats
        $queryBuilder->setMaxResults(30);

        //récupère l'objet Query
        $query = $queryBuilder->getQuery();

        //retourne les résultats
        return $query->getResult();
    }
}
