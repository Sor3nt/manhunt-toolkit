<?php

namespace App\Command;

use App\MHT;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Search for buildable files and build them')
            ->addArgument('folder', InputArgument::REQUIRED, 'folder to search (recursive).')
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

        $folder = realpath($input->getArgument('folder'));
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

        $finder = new Finder();
        $finder
            ->name('/#mls/i')
//            ->contains('/#mls/i')

            ->directories()
            ->in($folder);


        $output->writeln(sprintf("Build  %s folder(s)", $finder->count()));

        $this->processFile($finder, $game, $platform, $output);






        $output->writeln(sprintf("\nProcess done."));

    }

    private function processFile(Finder $finder, $game, $platform, OutputInterface $output){

        foreach ($finder as $file) {

            $outputTo = str_replace('#','.', $file);
            $outputTo = str_replace('.json','', $outputTo);


            //load the resource
            $resources = new Resources();
            $resource = $resources->load($file, $game, $platform);

            $handler = $resource->getHandler();

//            $output->writeln( sprintf('Identify as %s ', $handler->name));
            $output->writeln( sprintf('Processing %s ', $file));

            $result = $handler->pack( $resource->getInput(), $game, $platform );

            file_put_contents($outputTo, $result);
        }
    }

}