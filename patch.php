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
$debug = in_array('debug', $options) !== false;

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
        $platform != MHT::PLATFORM_PSP_001 &&
        $platform != MHT::PLATFORM_XBOX &&
        $platform != MHT::PLATFORM_WII
    ){
        throw new \Exception('Invalid platform, allowed is pc, ps2, psp, psp001, xbox, wii');
    }
}

$file = realpath($file);

$patchName = "TEST-PATCH-PKG";
$patchInformation = \json_decode(file_get_contents($file), true);

echo sprintf("Processing Patch %s\n%s\n", $patchName, str_repeat('-', 10));

foreach ($patchInformation['patches'] as $patch) {

    if (!file_exists($patch['file'])){
        echo sprintf("Unable to apply Patch %s! File %s not found\n", $patch['name'], $patch['file']);
        if(in_array('ignore-error', $options) !== false) die("Stop. To skip error use --ignore-error");
        continue;
    }

    //load the resource
    $resources = new Resources();
    $resource = $resources->load($patch['file'], $game, $platform);
    $handler = $resource->getHandler();

    if($debug)
        echo sprintf('Identify file as %s', $handler->name) . "\n";

    $patchHandlerClass = "App\Service\Patch\\" . $patch['handler'];
    if (!class_exists($patchHandlerClass))
        die(sprintf("Unable to apply Patch %s! Class %s not found\n", $patch['name'], $patch['handler']));

    /** @var \App\Service\Patch\PatchAbstract $patchHandler */
    $patchHandler = new $patchHandlerClass($resource, $game, $platform, $debug);
    $binary = $patchHandler->apply($patch);


    foreach ($patchHandler->applied as $patchEntry) {
        echo sprintf("Applied: %s\n", $patchEntry['description']);
    }

    foreach ($patchHandler->exists as $patchEntry) {
        echo sprintf("%s was already applied\n", $patchEntry['description']);
    }


    if (count($patchHandler->applied) > 0){
        file_put_contents($patch['file'] . '.tmp.ifp', $binary);
        echo "Patch applied!\n";
    }else{
        echo "No changes made!\n";
    }

}

