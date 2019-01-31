<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnpackCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('archive:unpack')
            ->setAliases(['unpack', 'extract', 'uncompress'])
            ->setDescription('Unpack a Manhunt file.')
            ->addArgument('file', InputArgument::REQUIRED, 'This file will be extracted')
            ->addOption('only-unzip', null, null, 'Will only unzip the file')
            ->addOption(
                'game',
                null,
                InputOption::VALUE_OPTIONAL,
                'mh1 or mh2?',
                MHT::GAME_AUTO
            )

            ->addOption(
                'platform',
                null,
                InputOption::VALUE_OPTIONAL,
                'pc,ps2,psp,wii,xbox?',
                MHT::PLATFORM_AUTO
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $file = $input->getArgument('file');
        $game = $input->getOption('game');
        $platform = $input->getOption('platform');


        if ($game !== MHT::GAME_AUTO){
            if ($game != MHT::GAME_MANHUNT && $game != MHT::GAME_MANHUNT_2){
                throw new \Exception('Invalid game, allowed is mh1 or mh2');
            }
        }

        if ($platform !== MHT::PLATFORM_AUTO){
            if (
                $platform != MHT::PLATFORM_PC &&
                $platform != MHT::PLATFORM_PS2 &&
                $platform != MHT::PLATFORM_PSP &&
                $platform != MHT::PLATFORM_XBOX &&
                $platform != MHT::PLATFORM_WII
            ){
                throw new \Exception('Invalid platform, allowed is pc, ps2, psp, xbox, wii');
            }
        }

        $path = pathinfo($file);

        $originalExtension = $path['extension'];

        //prepare output folder
        $outputTo = $path['dirname'] . '/export/' . $path['filename'] . '#' . $originalExtension;
        @mkdir($outputTo, 0777, true);

        //load the resource
        $resources = new Resources();
        $resource = $resources->load($file, $game, $platform);

        if ($input->getOption('only-unzip')){

            $outputTo = str_replace("#", '.', $outputTo);

            file_put_contents(
                $outputTo,
                $resource->getInput()->binary
            );

            $output->writeln(sprintf("Saved to %s.",  $outputTo));
            return;
        }

        $handler = $resource->getHandler();

        $output->writeln( sprintf('Identify file as %s ', $handler->name));
        $output->write( sprintf('Processing %s ', $file));

        $results = $handler->unpack( $resource->getInput(), $game, $platform );

        if ($handler instanceof Mls){
            $results = $handler->getValidatedResults( $results, $game, $platform );
        }

        if (is_array($results)){

            foreach ($results as $relativeFilename => $data) {

                //we loop through dataset not a fileset
                if ( is_numeric($relativeFilename) ){

                    rmdir($outputTo);
                    $outputTo = str_replace("#", '.', $outputTo) . '.json';

                    file_put_contents(
                        $outputTo,
                        \json_encode($results, JSON_PRETTY_PRINT)
                    );

                    if (json_last_error() !== 0){
                        var_dump($results);
                        $output->writeln('EMERGENCY JSON error received: ' . json_last_error_msg());
                        exit;
                    }


                    break;

                }else{
                    $output->write('.');

                    $pathInfo = pathinfo($relativeFilename);

                    $outputDir = $outputTo . '/' . $pathInfo['dirname'];
                    @mkdir($outputDir, 0777, true);

                    if (isset($pathInfo['extension'])) {
                        $extension = ''; // we keep the extension from the given filename
                    }else if (is_array($data)){
                        $extension = '.json';

                    }else{
                        $extension = '.' . $originalExtension;
                    }

                    if (is_array($data)){
                        $data = \json_encode($data, JSON_PRETTY_PRINT);
                    }

                    file_put_contents($outputDir . '/' . $pathInfo['basename'] . $extension, $data);
                }

            }


        }else{
            rmdir($outputTo);
            $outputTo = str_replace("#", '.', $outputTo);

            file_put_contents(
                $outputTo,
                $results
            );

        }


        $output->writeln(sprintf("\nExtracted to %s",  $outputTo));
    }

}