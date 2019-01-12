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

class AnalyseMlsCommand extends Command
{

    protected static $defaultName = 'analyse:mls';

    protected function configure()
    {
        $this->setDescription('folder with the *.mls files to analyse. (MH1 PC/PS2/XBOX & MH2 PC/PS2/PSP/WII)');

        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');

        $this
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

        $resources = new Resources();


        $resultsSorted = [
            'differences' => [],
            'same' => [],
            'unique' => []
        ];

        $path = pathinfo($folder);
        $outputTo = $path['dirname'] . '/export';


        $finder = new Finder();
        $finder->name('/\.mls/i')->files()->in($folder );

        $mlsScriptsByEntt = [];

        foreach ($finder as $file) {

            $output->writeln('Process ' . $file->getRelativePathname() . ' ');


            $resource = $resources->load($folder . '/' . $file->getRelativePathname(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_AUTO);

            $handler = $resource->getHandler();

            $results = $handler->unpack( $resource->getInput(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_AUTO);

            foreach ($results as $result) {

                if (!isset($mlsScriptsByEntt[ $result['ENTT']['name'] ])){
                    $mlsScriptsByEntt[ $result['ENTT']['name'] ] = [];
                }

                $mlsScriptsByEntt[ $result['ENTT']['name'] ][] = $result['SRCE'];
            }
        }

        foreach ($mlsScriptsByEntt as $scriptName => $scripts) {

            // the script is unique only one version provide this script
            if (count($scripts) == 1){
                $resultsSorted['unique'][$scriptName] = $scripts;
                continue;
            }

            $scripts = array_unique($scripts);

            // we have some differences
            if (count($scripts) > 1){
                $resultsSorted['differences'][$scriptName] = $scripts;
            }else{
                $resultsSorted['same'][$scriptName] = $scripts;
            }

        }


        foreach ($resultsSorted as $section => $entries) {
            @mkdir($outputTo . '/' . $section, 0777, true);

            $output->writeln('Section ' . $section);

            foreach ($entries as $entryName => $scripts) {
                $output->writeln('Script Name ' . $entryName);

                foreach ($scripts as $index => $script) {
                    $output->writeln('Script index ' . $index);
                    file_put_contents(
                        $outputTo . '/' . $section . '/' .  $entryName . '_' . $index . '.srce',
                        $script
                    );
                }
            }
        }

        $output->write("\nDone.\n");
    }
}