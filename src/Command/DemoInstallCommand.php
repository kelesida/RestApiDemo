<?php

namespace App\Command;

use App\Entity\Table;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand as Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DemoInstallCommand extends Command
{
    protected static $defaultName = 'demo:install';

    protected function configure()
    {
        $this
            ->setDescription('First execution of restApi reservation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        exec('php bin/console doctrine:database:create');
        $io->success('Generated database.');

        exec('php bin/console doctrine:schema:update --force');
        $this->fixtureEntities();
        $io->success('Generated entities.');

        $io->success('Api successfully installed. Use POST /api/v2/reservation method in Postman');

        return 0;
    }

    private function fixtureEntities()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        for ($i = 0; $i < 5; $i++) {
            $table = new Table();
            $em->persist($table);

            unset($table);
        }

        $em->flush();
    }
}
