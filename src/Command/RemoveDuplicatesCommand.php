<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Dff;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Archive\ZLib;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class RemoveDuplicatesCommand extends Command
{

    protected static $defaultName = 'rm:double';

    protected function configure()
    {
        $this->setDescription('Little helper to copy only unique files');
        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folder = realpath($input->getArgument('folder'));

        $path = pathinfo($folder);

        //prepare output folder
        $outputTo = $path['dirname'] . '/export';
        @mkdir($outputTo, 0777, true);
        $outputFolder = realpath($outputTo);


        $finder = new Finder();
        $finder
            ->files()
            ->in($folder);

        $md5List = [];
        $results = [];


        foreach ($finder as $file) {

            $md5 = md5($file->getContents());
            if (in_array($md5, $md5List)) continue;

            $md5List[] = $md5;

            $results[$file->getFilename()] = $file->getRelativePathname();
        }

        foreach ($results as $filename => $result) {
            copy($folder . '/' .$result, $outputFolder . '/' . $filename);
            echo ".";
        }


        $output->write("\nDone.\n");

    }


}