<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateEventsCommand extends Command
{


    protected function configure()
    {
        $this
            ->setName('generate:events')
            ->setDescription('Generate a list of available MLS SCPT Events')
            ->addArgument('folder', InputArgument::REQUIRED, 'Search inside this folder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $folder = $input->getArgument('folder');
        $finder = new Finder();
        $finder->name('/\.scpt/')->files()->in( $folder );

        $triggerMap = [];

        foreach ($finder as $file) {

            $data = \json_decode($file->getContents(), true);

            foreach ($data as $entry) {
                $triggerMap[ $entry['onTrigger'] ] = $entry['name'];
            }
        }


        ksort($triggerMap);

        echo "\n\n";

        foreach ($triggerMap as $offset => $name) {
            echo sprintf("'%s' => '%s',\n", $name, $offset);
        }

    }
}