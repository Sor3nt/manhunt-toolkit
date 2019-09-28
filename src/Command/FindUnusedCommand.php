<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Glg;
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

class FindUnusedCommand extends Command
{

    protected static $defaultName = 'find:unused';

    protected function configure()
    {
        $this->setDescription('Search unused stuff');
        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');
        $this->addArgument('type', InputArgument::OPTIONAL, 'file type');

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
        $type = $input->getArgument('type');

        $game = $input->getOption('game');
        $platform = $input->getOption('platform');


        //load the resource

        $levels = new Finder();

        $levels
            ->name('/\.bsp/i')
            ->notPath('export')

            ->files()
            ->in($folder);

        foreach ($levels as $level) {


            $unusedModels = $this->getUnusedModels($level->getPath(), $game, $platform);

            if (count($unusedModels)){

                $output->writeln(sprintf("Found %s unused Models in %s", count($unusedModels), $level->getRelativePath(). '/modelspc.mdl'));
                $output->writeln("=> " . implode("\n=> ", $unusedModels));

            }
        }

        $output->write("\nDone.\n");
    }

    private function getUnusedModels($folder, $game, $platform ){

        $usedModels = $this->getUsedModels($folder, $game, $platform);
        $availableModels = $this->getAvailableModels($folder, $game, $platform);

        $unusedModels = [];
        foreach ($availableModels as $availableModel) {
            if (in_array($availableModel, $usedModels) === false){
                $unusedModels[] = $availableModel;
            }
        }

        return $unusedModels;
    }

    private function getUsedModels( $folder, $game, $platform){
        $resources = new Resources();

        $fileName = "resource3.glg";

        if ($platform == MHT::PLATFORM_PS2){
            $fileName = "ENTTDATA.INI";
        }

        $resource = $resources->load($folder . '/' . $fileName, $game, $platform);

        $handler = $resource->getHandler();

        /** @var Glg\EntityTypeData\Ec[] $records */
        $records = $handler->unpack($resource->getInput(), $game, $platform);

        $usedModels = [];

        foreach ($records as $record) {

            if ($model = $record->get('MODEL')){
                $usedModels[] = $model;
            }

            if ($head = $record->get('HEAD')){
                $usedModels[] = $head;
            }
        }

        return array_unique($usedModels);
    }


    private function getAvailableModels( $folder, $game, $platform){
        $resources = new Resources();

        $fileName = "modelspc.mdl";

        if ($platform == MHT::PLATFORM_PS2){
            $fileName = "MODELS.DFF";
        }

        $resource = $resources->load($folder . '/' . $fileName, $game, $platform);

        $handler = $resource->getHandler();

        $models = $handler->unpack($resource->getInput(), $game, $platform);

        $availableModels = [];

        foreach ($models as $modelName => $model) {

            list($name, $ext) = explode(".", $modelName);
            $availableModels[] = $name;
        }

        return array_unique($availableModels);
    }


}