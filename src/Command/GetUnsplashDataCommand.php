<?php

namespace App\Command;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
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
        $this->getPictures();

        //on va chercher leur tags ensuite (les données ne sont pas dispo dans la première réponse)
        $this->getTags();

        $this->io->success('Done!');

        return Command::SUCCESS;
    }

    private function getPictures()
    {
        $pictureRepository = $this->entityManager->getRepository(Picture::class);

        //on exécute la requête 10 fois
        for($i=0; $i<10; $i++) {
            $response = $this->httpClient->request('GET', 'https://api.unsplash.com/photos/random', [
                'query' => [
                    'client_id' => 'mItBPL5udgEhRaz_YYlXNQr9BmGtjthBrKJQYrahVXw',
                    'featured' => 'true',
                    'orientation' => 'landscape',
                    'count' => 30
                ],
            ]);

            //contient 30 photos aléatoires
            $content = $response->toArray();

            foreach ($content as $picData) {
                //on vérifie si cette photo existe déjà dans notre bdd
                $foundPicture = $pictureRepository->findOneBy(['unsplashId' => $picData['id']]);
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

    }
}
