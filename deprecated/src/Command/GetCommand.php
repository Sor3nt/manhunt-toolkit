<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Glg\EntityTypeData\Ec;
use App\Service\Archive\Mdl\Extract;
use App\Service\Archive\Mls;
use App\Service\NBinary;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GetCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('entity:get')
            ->setAliases(['get'])
            ->setDescription('Extract a Entity.')
            ->addArgument('entityName', InputArgument::REQUIRED, 'The entity name')
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

        $entityName = $input->getArgument('entityName');
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


        $output->write(sprintf("Searching for %s ... ", $entityName));

        list($envExecResults, $res3Entries) = $this->findResource3Entry($entityName, $game, $platform);

        $result = [];

        if (count($res3Entries)){

            $output->writeln(sprintf("found ini record."));


            /** @var Ec $entry */
            $entry = $res3Entries[0]['entry'];
            $path = $res3Entries[0]['path'];

            $result[ $entry->name . '.glg' ] = $entry->__toString();

            $output->write(sprintf("Extract inst entry ... "));
            $insts = $this->getInst($path, $entry->name, $envExecResults, $game, $platform);

            if (count($insts)){

                $output->writeln(sprintf("%s extracted", count($insts) ));

                $output->write(sprintf("Search entity animation ... "));
                $animation = false;


                foreach ($insts as $inst) {
                    $result[ $inst['internalName'] . '.json' ] = \json_encode($inst, JSON_PRETTY_PRINT);

                    foreach ($inst['parameters'] as $parameter) {
                        if ($parameter['parameterId'] === "envExecEntityAnim"){
                            $entityAnimationName = $parameter['value'];


                            $animation = $this->getEntityAnimation($path, $entityAnimationName, $game, $platform);

                            if ($animation !== false){
                                $output->writeln(sprintf("found %s", $entityAnimationName ));
                                $result[ $entityAnimationName . '.json' ] = \json_encode($animation, JSON_PRETTY_PRINT);

                                break;

                            }

                        }
                    }

                }

                if ($animation == false){
                    $output->writeln(sprintf("none" ));
                }


            }



            if ($modelName = $entry->get('model')){

                $output->write(sprintf("Extract model %s ... ", $modelName));
                $model = $this->getModel( $path, $modelName, $game, $platform);

                if ($model !== false){
                    $output->writeln(sprintf("done"));

                    $result[ $model['name'] ] = $model['mdl'];

                    $mdlHandler = new Extract();
                    $textureNames = $mdlHandler->getTextureNames( new NBinary($model['mdl']));

                    $textureNames = array_unique($textureNames);

                    if (count($textureNames)){
                        $output->write(sprintf("Extract %s textures ... ", count($textureNames)));

                        $textures = $this->getTextures( $path, $textureNames, $game, $platform );

                        if (count($textures)){
                            $output->writeln(sprintf("%s extraced", count($textures)));


                            foreach ($textures as $textureName => $texture) {
                                $result[ $textureName ] = $texture;

                            }
                        }

                    }



                }
            }

        }


        $folder = "results/" . $entityName . "/";
        @mkdir($folder, 0777, true);

        foreach ($result as $fileName => $content) {
            file_put_contents( $folder . $fileName, $content);
        }

        $output->writeln("");
        $output->writeln("Entity exported to " . $folder);

    }

    private function quickUnpack($path, $file, $game, $platform ){
        $finder = new Finder();

        $finder
            ->name($file)
            ->files()
            ->in($path);

        $resources = new Resources();

        $result = [];
        foreach ($finder as $file) {
            $resource = $resources->load($file, $game, $platform);

            $handler = $resource->getHandler();

            $result[] = $handler->unpack($resource->getInput(), $game, $platform);

        }

        return $result;
    }

    private function getEntityAnimation($path, $animationName, $game, $platform){

        if ($platform == MHT::PLATFORM_PC){
            $file = 'allanims_pc.ifp';
        }else{
            die("getEntityAnimation platform todo...");
        }

        $unpacked = current($this->quickUnpack($path, $file, $game, $platform));
        foreach ($unpacked as $animFileName => $result) {
            list($folder, $animName) = explode("/", $animFileName);

            if (strtolower($animName) == strtolower($animationName)){
                return $result;
            }
        }

        return false;
    }

    private function getInst($path, $recordName, $envExecResults, $game, $platform){


        if ($platform == MHT::PLATFORM_PC){
            $file = 'entity_pc.inst';
        }else{
            die("getInst platform todo...");
        }

        $results = [];
        $files = $this->quickUnpack($path, $file, $game, $platform);
        foreach ($files as $unpacked) {


            foreach ($unpacked as $instFileName => $result) {

                list($id, $name) = explode("#", $instFileName);
                list($name) = explode(".", $name);


                if (
                    $result['entityClass'] == "EnvExec"

                ){

                    foreach ($envExecResults as $envExecResult) {
                        if (strtolower($result['record']) == strtolower($envExecResult)){
                            foreach ($result['parameters'] as $parameter) {


                                if ($parameter['value'] === $recordName){

                                    $results[] = $result;
                                    break 2;

                                }

                            }
                        }

                    }
                }


                if (strtolower($name) == strtolower($recordName)){

                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    private function getTextures($path, $textures, $game, $platform){


        if ($platform == MHT::PLATFORM_PC){
            $file = 'modelspc.tex';
        }else{
            die("getTextures platform todo...");
        }

        $results = [];

        $files = $this->quickUnpack($path, $file, $game, $platform);
        foreach ($files as $unpacked) {

            foreach ($unpacked as $resultModelName => $result) {

                foreach ($textures as $textureName) {


                    if (strtolower($resultModelName) == strtolower($textureName . ".bmp")){

                        $results[$resultModelName] = $result;
                    }
                }
            }
        }

        return $results;
    }

    private function getModel($path, $modelName, $game, $platform){

        if ($platform == MHT::PLATFORM_PC){
            $file = 'modelspc.mdl';
        }else{
            die("getTextures platform todo...");
        }

        $unpacked = current($this->quickUnpack($path, $file, $game, $platform));

        foreach ($unpacked as $resultModelName => $result) {

            if (strtolower($resultModelName) == strtolower($modelName . ".mdl")){

                return [
                    'name' => $resultModelName,
                    'mdl' => $result
                ];
            }
        }

        return false;
    }

    private function findResource3Entry($entityName, $game, $platform){

        $finder = new Finder();

        $finder
            ->name('resource3.glg')
            ->files()
            ->in(".");

        $resources = new Resources();

        $results = [];

        $envExecResults = [];

        foreach ($finder as $file) {

            $envExecResults = [];

            $resource = $resources->load($file, $game, $platform);

            $handler = $resource->getHandler();

            /** @var Ec[] $unpacked */
            $unpacked = $handler->unpack($resource->getInput(), $game, $platform);

            foreach ($unpacked as $entityPath => $result) {
                list($resultCategory, $resultEntityName)  = explode("/", $entityPath);

                if ($result->class == "EC_ENVIRONMENTAL_EXECUTION"){
                    $envExecResults[] = $resultEntityName;
                }


                if (strtolower($resultEntityName) == strtolower($entityName)){
                    $results[] = [
                        'path' => $file->getRelativePath(),
                        'entry' => $result
                    ];
                }
            }

        }

        return [$envExecResults, $results];
    }
}