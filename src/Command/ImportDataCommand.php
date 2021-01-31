<?php

namespace App\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportDataCommand extends Command
{
    protected static $defaultName = 'app:import-data';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var KernelInterface
     */
    private $appKernel;

    public function __construct(string $name = null, EntityManagerInterface $entityManager, KernelInterface $appKernel)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->appKernel = $appKernel;
    }

    protected function configure()
    {
        $this
            ->setDescription('Importe les données contenu de la fichier sql')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->entityManager->getConnection();

        $sqlFile = $this->appKernel->getProjectDir() . DIRECTORY_SEPARATOR . "eni_pics.sql";

        if (!file_exists($sqlFile)){
            $io->error('Le fichier .sql n\'existe pas à ' . $sqlFile);
            return Command::FAILURE;
        }

        $sql = file_get_contents($sqlFile);

        try {
            $connection->executeQuery($sql);
        }
        catch (Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Les données ont bien été chargées dans MySQL !');
        return Command::SUCCESS;
    }
}
