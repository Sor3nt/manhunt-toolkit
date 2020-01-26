<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Glg;
use App\Service\Archive\Ifp;
use App\Service\Archive\Mdl;
use App\Service\Archive\Mls;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class GenerateGlobalFileCommand extends Command
{

    protected static $defaultName = 'generate:global';

    protected function configure()
    {
        $this->setDescription('Generate a global animation file by multiple files');
        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');
        $this->addArgument('file', InputArgument::REQUIRED, 'File to process (allanims_pc.ifp)');

        $this->addOption(
            'game',
            null,
            InputOption::VALUE_OPTIONAL,
            'mh1 or mh2?',
            MHT::GAME_AUTO
        );

        $this->addOption(
            'platform',
            null,
            InputOption::VALUE_OPTIONAL,
            'pc,ps2,psp,wii,xbox?',
            MHT::PLATFORM_AUTO
        );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folder = realpath($input->getArgument('folder'));
        $file = $input->getArgument('file');



        $game = $input->getOption('game');
        $platform = $input->getOption('platform');


        //load the resource

        $animations = new Finder();

        $animations
            ->name($file)
            ->notPath('export')

            ->files()
            ->in($folder);

        $outputTo = realpath($folder) . "/export/global/" . str_replace(".", '#', $file);
        @mkdir($outputTo, 0777, true);

        $filesCount = 0;
        $filesDuplicated = 0;
        $filesKeep = 0;


        foreach ($animations as $animation) {

            $output->write(sprintf("Processing %s ... ", $animation->getRelativePathname()));

            $resources = new Resources();

            $resource = $resources->load($animation->getRelativePathname(), $game, $platform);

            /** @var Ifp $handler */
            $handler = $resource->getHandler();

            if (get_class($handler) == Ifp::class){
                $handler->keepOrder = false;
            }

            $ifpEntries = $handler->unpack($resource->getInput(), $game, $platform);

            $newFiles = 0;
            foreach ($ifpEntries as $relativeFilename => $data) {
                $filesCount++;

                $pathInfo = pathinfo($relativeFilename);

                $outputDir = $outputTo . '/' . $pathInfo['dirname'];
                @mkdir($outputDir, 0777, true);

                if (is_array($data)){
                    $data = \json_encode($data, JSON_PRETTY_PRINT);
                    $extension = '.json';
                }else{
                    $extension = '.glg';
                }



                $outputFile = $outputDir . '/' . $pathInfo['basename'] . $extension;

                if (file_exists($outputFile)){
                    $filesDuplicated++;
                    continue;
                }

                file_put_contents($outputFile, $data);
                $filesKeep++;
                $newFiles++;
            }

            $output->writeln(sprintf("Add %s new Files", $newFiles));
        }

        $output->writeln("Packing... ");

        //load the resource
        $resources = new Resources();
        $resource = $resources->load($outputTo, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);

        $handler = $resource->getHandler();

        $result = $handler->pack( $resource->getInput(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

        $outputTo = str_replace('#','.', $outputTo);
        $outputTo = str_replace('.json','', $outputTo);

        file_put_contents($outputTo, $result);
        $output->write("\n");
        $output->writeln(sprintf("Total entries processed: %s", number_format($filesCount)));
        $output->writeln(sprintf("Duplicated entries: %s", number_format($filesDuplicated)));
        $output->writeln(sprintf("Entries saved: %s", number_format($filesKeep)));

        $output->writeln("Saved tp " . $outputTo);
    }

}