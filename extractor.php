<?php
ini_set('memory_limit','-1');
require_once 'vendor/autoload.php';

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Resources;
use Symfony\Component\Finder\Finder;

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
        list($script, $folder) = $argv;
        break;
    case 3:
        list($script, $folder, $game) = $argv;
        break;
    case 4:
        list($script, $folder, $game, $platform) = $argv;
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
$folder = realpath($folder);


$finder = new Finder();
$finder
    ->name('/\.mls/i')
    ->files()
    ->in($folder);

$dirNames = [];

foreach ($finder as $file) {
    try{
        $resources = new Resources();

        $resource = $resources->load($file, $game, $platform);

        $handler = $resource->getHandler();

        $ext = $file->getFilenameWithoutExtension();
        $dirNames[$ext] = [];

        $results = $handler->unpack($resource->getInput(), $game, $platform);

        if ($handler instanceof Mls){


            foreach ($results as $result) {
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'FrisbeeSpeechPlay', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamFromEntityAuto', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamAuto', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamFromPosAuto', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamFromPosAutoLooped', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamFromEntityAutoLooped', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamFromEntityAux', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayScriptAudioStreamAux', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayDirectorSpeechPlaceholder', 0));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayAudioOneShotFromEntity', 2));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayAudioLoopedFromEntity', 2));
                $dirNames[$ext] = array_merge($dirNames[$ext], extractMlsFunctionString($result['SRCE'], 'PlayAudioOneShotFromPos', 2));
            }

            echo ".";
    //        var_dump($dirNames);exit;
        }
    }catch(\Exception $e){
echo "err";
    }

}

$output = [];
foreach ($dirNames as $levelName => $levelAudio) {

    $levelAudio = array_filter($levelAudio);
    $levelAudio = array_unique($levelAudio);
    $levelAudio = implode("\n", $levelAudio);

    $output[] = [
        'level' => $levelName,
        'names' => explode("\n", $levelAudio)
    ];


}

file_put_contents('mls-level-audio-names.json', \json_encode($output, JSON_PRETTY_PRINT));

echo "done";

function extractMlsFunctionString($src, $function, $paramIndex){

    $results = [];
    preg_match_all("/" . $function . "\((.*)\)/i", $src, $matches);

    foreach ($matches[1] as $match) {
        $options = explode(',', $match);
        $options = array_map('trim', $options);
        if (isset($options[$paramIndex])){
            $value = $options[$paramIndex];
            $value = str_replace('\'', '', $value);
            $value = str_replace('""', '', $value);

            $results[] = $value;
        }
    }

    return $results;
}

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