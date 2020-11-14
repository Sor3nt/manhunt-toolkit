<?php
ini_set('memory_limit','-1');
require_once 'vendor/autoload.php';

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


echo "Scan for DDS images... ";
$dds = scanForDDS(new NBinary(file_get_contents($file)));
echo count($dds) . " found\n";

$ddsHandler = new Dds();
$imageHandler = new Image();

foreach ($dds as $fileName => $file) {

    $ddsResult = $ddsHandler->unpack( new NBinary($file), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

    $fileName = substr($fileName,0, -3) . 'png';

    file_put_contents('export/' . $fileName, $imageHandler->rgbaToImage($ddsResult['rgba'], $ddsResult['width'], $ddsResult['height']));
}


function scanForDDS(NBinary $binary){

    if (mb_strpos($binary->binary, "DDS ") === false) return [];
    $binary->current = 0;
    $ddsHandler = new Dds();

    $fileName = 0;
    $result = [];
    while($binary->remain()){

        while($binary->consume(4, NBinary::BINARY) === "DDS "){
            $binary->current -= 4;

            $start = $binary->current;

            $header = $ddsHandler->readHeader($binary);

            $size = $header['width'] * $header['height']; //only first image, ignore mipmaps

            echo "Found DDS (" . $header['width'] . "x" . $header['height'] . ") at point " . $start . "\n";
            $binary->current = $start;
            $result["unknown_" . $fileName . ".dds"] = $binary->consume(4 + $header['size'] + $size, $binary);
            $binary->current = $start + 4;

            $fileName++;
//            var_dump($fileName);


        }
    }

    return $result;

}

function printHelp(){

    echo "Todo:\n";

    echo "\n";

    echo "Usage: php scan.php [options] <filenamw> [game] [platform]\n";
    echo "Example: php scan.php A01_Escape_Asylum.mls mh2 pc\n";

}