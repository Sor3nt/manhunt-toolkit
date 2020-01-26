<?php
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


switch (count($argv)){
    case 2:
        list($script, $file) = $argv;
        break;
    case 3:
        list($script, $file, $game) = $argv;
        break;
    case 4:
        list($script, $file, $game, $platform) = $argv;
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

$path = pathinfo($file);

$originalExtension = $path['extension'];

//prepare output folder
$outputTo = $path['dirname'] . '/export/' . $path['filename'] . '#' . $originalExtension;
@mkdir($outputTo, 0777, true);

//load the resource
$resources = new Resources();
$resource = $resources->load($file, $game, $platform);
//
//if ($onlyUnzip){
//
//    $outputTo = str_replace("#", '.', $outputTo);
//
//    file_put_contents(
//        $outputTo,
//        $resource->getInput()->binary
//    );
//
//    echo sprintf("Saved to %s.",  $outputTo);
//    return;
//}

$handler = $resource->getHandler();

echo sprintf('Identify file as %s', $handler->name) . "\n";
echo sprintf('Processing %s ', $file);


if (isset($handler->keepOrder)){
    $handler->keepOrder = $keepOrder;
}

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
                echo 'EMERGENCY JSON error received: ' . json_last_error_msg();
                exit;
            }


            break;

        }else{
            echo '.';

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

echo sprintf("\nExtracted to %s",  $outputTo);


function printHelp(){

    echo "Compatible Formats:\n";
    echo "*.COL - Collision Matrix\t\t\t";
    echo "*.DFF - 3D Models (Manhunt 1)\n";
    echo "*.MDL - 3D Models (Manhunt 2)\t\t\t";
    echo "*.GLG - Settings Files\n";
    echo "*.GRF - AI Map Path\t\t\t\t";
    echo "*.GXT - Translations\n";
    echo "*.IFP - Animations\t\t\t\t";
    echo "*.BIN - Execution Animations\n";
    echo "*.INST - Entity Positions\t\t\t";
    echo "*.MLS - Levelscript\n";
    echo "*.PAK - Manhunt Data Container\t\t\t";
    echo "*.TEX / TXD - Textures\n";
    echo "\n";

    echo "Usage: php unpack.php <filenamw> [game] [platform]\n";
    echo "Example: php unpack.php A01_Escape_Asylum.mls mh2 pc\n";

}