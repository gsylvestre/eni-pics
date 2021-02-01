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

    public function search(string $keywords = null, ?int $minLikes = 0, ?int $minDownloads = 0, string $sort = "id", int $page = 1)
    {

        //nombre de photos à afficher par page
        $numberOfPicturesPerPage = 30;

        //calcul de l'offset à partir du numéro de page de l'URL
        //offset : à partir de quelle ligne veut-on récupérer les résultats ?
        //si l'offset est égal à 60, alors on récupérera les photos #61 à #90
        $offset = ($page - 1) * $numberOfPicturesPerPage;

        //notre query builder
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

        //si on a reçu un nombre de likes minimum...
        if ($minLikes){
            //on ajoute alors une clause where
            $queryBuilder->andWhere('p.likes > :minLikes')
                ->setParameter(':minLikes', $minLikes);
        }

        //si on a un nombre de téléchargements mini
        if ($minDownloads){
            //nouvelle clause where
            $queryBuilder->andWhere('p.downloads > :minDownloads')
                ->setParameter(':minDownloads', $minDownloads);
        }

        //nombre max par page
        $queryBuilder->setMaxResults($numberOfPicturesPerPage);
        //on utilise l'offset (voir ci-dessus)
        $queryBuilder->setFirstResult($offset);

        //on tri, ici arbitrairement sur les likes
        $queryBuilder->addOrderBy('p.'.$sort, 'DESC');

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

    /**
     * trouve les photographes en fonction de leur nom
     */
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
