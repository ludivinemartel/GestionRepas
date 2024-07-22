<?php

namespace App\Command;

use App\Entity\FoodComposition;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:import-food-composition',
    description: 'Import food composition data from Ciqual CSV.'
)]
class ImportFoodCompositionCommand extends Command
{
    private $entityManager;
    private $params;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvFilePath = $this->params->get('kernel.project_dir') . '/data/ciqual.csv';
        if (!file_exists($csvFilePath)) {
            $output->writeln('CSV file not found at path: ' . $csvFilePath);
            return Command::FAILURE;
        }

        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();

        $output->writeln('CSV Headers: ' . implode(', ', $headers));

        $records = $csv->getRecords();

        foreach ($records as $record) {
            if (!isset($record['Protéines, N x 6.25 (g/100 g)'])) {
                $output->writeln('Header "Protéines, N x 6.25 (g/100 g)" not found in CSV');
                return Command::FAILURE;
            }

            $foodComposition = new FoodComposition();
            $foodComposition->setName($record['alim_nom_fr']);
            $foodComposition->setKcal((float)$record['Energie, Règlement UE N° 1169/2011 (kcal/100 g)']);
            $foodComposition->setProtein((float)$record['Protéines, N x 6.25 (g/100 g)']);
            $foodComposition->setFat((float)$record['Lipides (g/100 g)']);
            $foodComposition->setCarbohydrate((float)$record['Glucides (g/100 g)']);

            $this->entityManager->persist($foodComposition);
        }

        $this->entityManager->flush();

        $output->writeln('Import completed.');

        return Command::SUCCESS;
    }
}
