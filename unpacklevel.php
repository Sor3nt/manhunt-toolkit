<?php
ini_set('memory_limit','-1');
require_once 'vendor/autoload.php';

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Resources;

echo "\n";
echo "███╗   ███╗██╗  ██╗████████╗    ██╗   ██╗   ██████╗    █████╗ \n";
echo "████╗ ████║██║  ██║╚══██╔══╝    ██║   ██║  ██╔═████╗  ██╔══██╗\n";
echo "██╔████╔██║███████║   ██║       ██║   ██║  ██║██╔██║  ╚██████║\n";
echo "██║╚██╔╝██║██╔══██║   ██║       ╚██╗ ██╔╝  ████╔╝██║   ╚═══██║\n";
echo "██║ ╚═╝ ██║██║  ██║   ██║        ╚████╔╝██╗╚██████╔╝██╗█████╔╝\n";
echo "╚═╝     ╚═╝╚═╝  ╚═╝   ╚═╝         ╚═══╝ ╚═╝ ╚═════╝ ╚═╝╚════╝ \n";
echo "\t\t\tCoded by Sor3nt | dixmor-hospital.com\n";
echo "A free and open source toolkit to quickly modify Rockstar`s game Manhunt. \n";
echo "\n";

$game = MHT::GAME_MANHUNT_2;
$platform = MHT::PLATFORM_PC;



$options = [];
foreach ($argv as $glgIndex => $argument) {
    if (substr($argument, 0, 2) == "--"){
        $options[] = substr($argument, 2);
        unset($argv[$glgIndex]);
    }
}

switch (count($argv)){
    case 2:
        list($script, $levelFolder) = $argv;
        break;
    case 3:
        list($script, $levelFolder, $game) = $argv;
        break;
    case 4:
        list($script, $levelFolder, $game, $platform) = $argv;
        break;
    default:
        printHelp();
        exit;
        break;
}

$keepOrder = true;
//$onlyUnzip = false;

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
$levelFolder = realpath($levelFolder);

$path = pathinfo($levelFolder);

//prepare output folder
$outputTo = $path['dirname'] . '/export/' . $path['filename'];
@mkdir($outputTo, 0777, true);

$level = [];
echo "Loading resources...";
foreach ([$path['filename'] . '.mls', 'resource3.glg', 'entity_pc.inst', 'modelspc.mdl', 'modelspc.tex', 'collisions_pc.col' ] as $requiredFile) {
    $file = $levelFolder . '/' . $requiredFile;
    if (!file_exists( $levelFolder . '/' . $requiredFile))
        die(sprintf("Unable to process, required file %s missed!", $requiredFile));

    //load the resource
    $resources = new Resources();
    $resource = $resources->load($file, $game, $platform);

    $handler = $resource->getHandler();


    if (isset($handler->keepOrder)){
        $handler->keepOrder = $keepOrder;
    }

    $results = $handler->unpack( $resource->getInput(), $game, $platform );


//    if ($handler instanceof Mls){
//        $results = $handler->getValidatedResults( $results, $game, $platform );
//    }

    $level[explode('.', $requiredFile)[1]] = $results;

}
echo "OK\n";
echo "Process mapping...\n";

$output = [];

foreach ($level['glg'] as $glgIndex => $glgEntry) {
    /** @var App\Service\Archive\Glg\EntityTypeData\Ec $glgEntry */



    echo sprintf("Try to map GLG %s\n", $glgEntry->name);


//    if ($glgEntry->class == "DUMMY"){
//        $output[$glgIndex . '.json'] = $glgEntry;
//        unset($level['glg'][$glgIndex]);
//        continue;
//    }

    $outputSubFolder = $glgEntry->class . '/' . $glgEntry->name;

    $output[$outputSubFolder . '/' . $glgEntry->name . '.ini'] = $glgEntry;

    foreach ($level['col'] as $colIndex => $colEntry) {
        if ($colEntry['name'] != $glgEntry->name) continue;
        echo sprintf("  => Found Collisioin %s\n", $colEntry['name']);

        $output[$outputSubFolder . '/' . $colEntry['name'] . '.col'] = $colEntry;
        break;
    }



    $model = $glgEntry->get('model');
    if ($model){
        foreach ($level['mdl'] as $mdlIndex => $mdlEntry) {

            $name = explode('#', $mdlIndex)[1];
            $name = explode('.', $name)[0];

            if ($name !== $model) continue;

            echo sprintf("  => Found Model %s\n", $name);


            $output[$outputSubFolder . '/' . $name . '.mdl'] = $mdlEntry;

            $mdlHandler = new \App\Service\Archive\Mdl\Extract();
            $textureNames = $mdlHandler->getTextureNames(new \App\Service\NBinary($mdlEntry));

            foreach ($textureNames as $textureName) {

                foreach ($level['tex'] as $txdIndex => $txdEntry) {
                    $texName = explode('.', $txdIndex)[0];
                    if ($texName !== $textureName) continue;

                    echo sprintf("  => Found Texture %s\n", $texName);

                    $output[$outputSubFolder . '/' . $txdIndex] = $txdEntry;
                    unset($level['tex'][$txdIndex]);
                }

            }


            unset($level['mdl'][$mdlIndex]);
            break;
        }
    }

    foreach ($level['inst'] as $instIndex => $instEntry) {
        if (strtolower($instEntry['record']) != strtolower($glgEntry->name)) continue;

        echo sprintf("  => Found Inst %s\n", $instEntry['internalName']);

        $output[$outputSubFolder . '/' . $instEntry['record'] . '.inst'] = $instEntry;

        foreach ($level['mls'] as $mlsIndex => $mlsEntry) {
            if (strtolower($mlsEntry['ENTT']['name']) !== strtolower($instEntry['internalName'])) continue;
            $output[$outputSubFolder . '/' . $instEntry['internalName'] . '.srce'] = $mlsEntry['SRCE'];

            echo sprintf("  => Found MLS %s\n", $instEntry['internalName']);

            unset($level['mls'][$mlsIndex]);
            break;
        }

        unset($level['inst'][$instIndex]);
    }

    unset($level['glg'][$glgIndex]);

}


foreach ($output as $relativeFilename => $data) {
    echo '.';

    $pathInfo = pathinfo($relativeFilename);

    $outputDir = $outputTo . '/' . $pathInfo['dirname'];
    @mkdir($outputDir, 0777, true);

    if (is_array($data)){
        $data = \json_encode($data, JSON_PRETTY_PRINT);
    }

    file_put_contents($outputDir . '/' . $pathInfo['basename'] , $data);
}

echo sprintf("\nExtracted to %s",  $outputTo);


function replaceOrderName($orders, $old, $new){
    $newOrders = [];
    foreach ($orders as $order) {
        if ($order == $old){
            $newOrders[] = $new;
        }else{
            $newOrders[] = $order;
        }
    }

    return $newOrders;
}


function removeOrderName($orders, $name){
    $newOrders = [];
    foreach ($orders as $order) {
        if ($order != $name){
            $newOrders[] = $order;
        }
    }

    return $newOrders;
}

function printHelp(){
    echo "TODO";

    echo "Usage: php unpack.php [options] <filenamw> [game] [platform]\n";
    echo "Example: php unpack.php A01_Escape_Asylum.mls mh2 pc\n";

}