<?php

require_once 'vendor/autoload.php';

use App\MHT;
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

$outputTo = str_replace('#','.', $file);
$outputTo = str_replace('.json','', $outputTo);

//load the resource
$resources = new Resources();
$resource = $resources->load($file, $game, $platform);

$handler = $resource->getHandler();

echo sprintf('Identify as %s ', $handler->name) . "\n";
echo sprintf('Processing %s ', $file);

$result = $handler->pack( $resource->getInput(), $game, $platform );

file_put_contents($outputTo, $result);

echo sprintf("\nPacked to %s",  $outputTo) . "\n";


function printHelp(){

    echo "Compatible Formats:\n";
    echo "*.INST - Entity Positions\t\t\t";
    echo "*.BIN - Execution Animations\n";
    echo "*.IFP - Animations\t\t\t\t";
    echo "*.GLG - Settings Files\n";
    echo "*.PAK - Manhunt Data Container\t\t\t";
    echo "*.GXT - Translations\n";
    echo "*.MDL - 3D Models (Manhunt 2)\t\t\t";
    echo "*.MLS - Levelscript\n";
    echo "*.COL - Collision Matrix\t\t\t";
    echo "*.DFF - 3D Models (Manhunt 1)\n";
    echo "\n";

    echo "Usage: php pack.php <filename/folder> [game] [platform]\n";
    echo "Example: php pack.php A01_Escape_Asylum#mls mh2 pc\n";

}