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
            //on sépare chaque mot-clef tapé dans un tableau
            $keywordsArray = explode(" ", $keywords);
            dump($keywordsArray);

            //on ajoute un where en mode OR pour chaque mot-clef
            for ($i = 0; $i < count($keywordsArray); $i++) {
                //on doit créer des paramètres avec des noms différents ici, d'où le $i...
                $queryBuilder->orWhere("p.title LIKE :kw$i OR p.description LIKE :kw$i")
                    ->setParameter(":kw$i", "%". $keywordsArray[$i] ."%");
            }
        }

        //limite à 30 résultats
        $queryBuilder->setMaxResults(30);

        //récupère l'objet Query
        $query = $queryBuilder->getQuery();

        //retourne les résultats
        return $query->getResult();
    }
}
