<?php

namespace App\Command;

use App\Entity\Picture;
use App\Entity\Tag;
use App\Repository\PictureRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetUnsplashDataCommand extends Command
{
    protected static $defaultName = 'app:get-unsplash-data';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    //ma clé d'api, devrait être stocké genre dans .env.local pour être privé
    const API_KEY = "mItBPL5udgEhRaz_YYlXNQr9BmGtjthBrKJQYrahVXw";


    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->pictureRepository = $this->entityManager->getRepository(Picture::class);
        $this->tagRepository = $this->entityManager->getRepository(Tag::class);
    }


    protected function configure()
    {
        $this
            ->setDescription('Retrieve pictures data from unsplash api')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        //déjà on récupère des photos
        //$this->getPictures();

        //on va chercher leur tags ensuite (les données ne sont pas dispo dans la première réponse)
        $this->getTags();

        $this->io->success('Done!');

        return Command::SUCCESS;
    }

    private function getPictures()
    {

        //on exécute la requête 10 fois
        for($i=0; $i<10; $i++) {
            $response = $this->httpClient->request('GET', 'https://api.unsplash.com/photos/random', [
                'query' => [
                    'client_id' => self::API_KEY,
                    'featured' => 'true',
                    'orientation' => 'landscape',
                    'count' => 30
                ],
            ]);

            //contient 30 photos aléatoires
            $content = $response->toArray();

            foreach ($content as $picData) {
                //on vérifie si cette photo existe déjà dans notre bdd
                $foundPicture = $this->pictureRepository->findOneBy(['unsplashId' => $picData['id']]);
                if ($foundPicture) {
                    $this->io->writeln('Picture ' . $picData['id'] . ' exists!');
                    continue;
                }

                $picture = new Picture();
                $picture->setDescription($picData['description']);
                $picture->setTitle($picData['alt_description']);
                $picture->setLikes($picData['likes']);
                $picture->setUnsplashId($picData['id']);
                $picture->setCreatedAt(new \DateTime($picData['created_at']));
                $picture->setSmallUrl($picData['urls']['small']);
                $picture->setBigUrl($picData['urls']['regular']);

                $this->entityManager->persist($picture);
            }

            $this->entityManager->flush();

            //on attend une seconde sinon unsplash renvoie exactement les mêmes photos
            sleep(1);
        }
    }

    private function getTags()
    {
        $allPictures = $this->pictureRepository->findAll();

        //une requête par photo... faudra pas aller trop vite
        foreach($allPictures as $picture) {
            //si la photo a déjà des tags, on passe à la suivante
            if ($picture->getTags()->count() > 0){
                continue;
            }

            //fait la requête à l'api
            $response = $this->httpClient->request(
                'GET',
                'https://api.unsplash.com/photos/' . $picture->getUnsplashId(),
                [
                    'query' => [
                        'client_id' => self::API_KEY,
                    ],
                ]
            );

            //contient plus d'info sur cette photo
            $content = $response->toArray();

            if (empty($content['tags'])){
                $this->io->writeln('Pas de tags pour ' . $picture->getUnsplashId());
                continue;
            }

            foreach ($content['tags'] as $tagData) {
                //on cherche d'abord ce tag pour éviter les doublons
                $foundTag = $this->tagRepository->findOneBy(['name' => $tagData['title']]);
                if ($foundTag){
                    $this->io->writeln('tag ' . $tagData['title'] . ' exists');

                    //on l'associe quand même à cette photo
                    $picture->addTag($foundTag);

                    continue;
                }

                $tag = new Tag();
                $tag->setName($tagData['title']);

                $this->entityManager->persist($tag);

                //associe ce nouveau tag à cette photo
                $picture->addTag($tag);
            }

            //pour sauvegarder les associations
            $this->entityManager->persist($picture);

            //on flush tout de suite, de toute façon on a le temps :D
            $this->entityManager->flush();

            //on attend deux MINUTES parce qu'on a droit à seulement 50 requêtes par heure :(
            sleep(120);
        }
    }
}
