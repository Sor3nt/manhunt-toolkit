<?php
ini_set('memory_limit','-1');
require_once __DIR__ . '/../../vendor/autoload.php';

use App\MHT;
use App\Service\Archive\Dds;
use App\Service\Archive\Mls;
use App\Service\Archive\Textures\Image;
use App\Service\NBinary;
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
foreach ($argv as $index => $argument) {
    if (substr($argument, 0, 2) == "--"){
        $options[] = substr($argument, 2);
        unset($argv[$index]);
    }
}

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

//load the resource
$resources = new Resources();

if (!file_exists($file . '.mht_bak')){
    file_put_contents($file . '.mht_bak', file_get_contents($file));
}else{
    unlink($file);
    copy($file.'.mht_bak', $file);
    echo "Use backup file...\n";
}

if (strpos($file, "global_pc.tex") !== false){ echo "Skip";exit;}
if (strpos($file, "damage_system.tex") !== false){ echo "Skip";exit;}
if (strpos($file, "gui_pc.tex") !== false){ echo "Skip";exit;}
if (strpos($file, "ingame_pc.tex") !== false){ echo "Skip";exit;}


$resource = $resources->load($file, $game, $platform);
$handler = $resource->getHandler();
echo 'Handler ' . $handler->name . "\n";
$results = $handler->unpack($resource->getInput(), $game, $platform);
$ddsHandler = new Dds();

$newResults = [];
foreach ($results as $fileName => &$data) {

    $header = $ddsHandler->readHeader(new NBinary($data));
    if ($header['format'] === "DXT1"){

        echo "Process " . $fileName . "...";
        file_put_contents('tmp.dds', $data);
    //    system('convert tmp.dds -negate tmp.dds');
    //    system('toon tmp.dds tmp.dds');
    //    system('convert tmp.dds -set colorspace Gray -separate -average tmp.dds');

        system('convert tmp.dds -colorspace Gray  -edge 1  tmp.dds');
//        system('convert tmp.dds -negate  tmp.dds');
//    system('convert tmp.dds -background White -alpha on -define dds:compression=dtx5 -monochrome tmp.dds');
        $data = file_get_contents('tmp.dds');
        echo "OK\n";

    }


    unset($results[$fileName]);
    $newResults[explode(".", $fileName)[0]] = $data;
}

$packed = $handler->pack($newResults, $game, $platform);
file_put_contents($file , $packed);

echo "Done";



function printHelp(){

    echo "Todo:\n";

    echo "\n";

    echo "Usage: php convert.php <tex file>\n";
    echo "Example: php convert.php any.tex\n";

}