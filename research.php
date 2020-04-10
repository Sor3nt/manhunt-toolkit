<?php
require_once 'vendor/autoload.php';

use App\MHT;
use App\Service\Archive\Inst;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
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

switch (count($argv)){
    case 2:
        list($script, $folder) = $argv;
        break;
    default:
        printHelp();
        exit;
        break;
}

$folder = realpath($folder);

$options = [];
foreach ($argv as $index => $argument) {
    if (substr($argument, 0, 2) == "--"){
        $options[] = substr($argument, 2);
        unset($argv[$index]);
    }
}


//$research = (int) readline("Research : ");
$research = 1;

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


if ($research == 1){
    instSearchGetValues($folder, $game, $platform);
}


function instSearchGetValues( $folder, $game, $platform ){

    $resources = new Resources();
    $finder = new Finder();
    $finder
        ->name('/.inst/i')
        ->name('/ENTINST.BIN/i')

        ->files()
        ->in($folder);

    $foundValues = [];
    echo "Processing files...";
    foreach ($finder as $file) {
//        if (!($file->getExtension() == "bin" && $file->getExtension() == "inst")) continue;

        $resource = $resources->load($file, $game, $platform);
        $handler = $resource->getHandler();
        $results = $handler->unpack($resource->getInput(), $game, $platform);

        foreach ($results as $fileName => $data) {

            foreach ($data['parameters'] as $parameter) {
//                if (strtoupper($parameter['parameterId']) == strtoupper($searchHash)){
                    $foundValues[] = [
                        'id' => $parameter['parameterId'],
                        'name' => $data['internalName'],
                        'value' => $parameter['value']
                    ];
//                }
            }
        }
    }
    echo " done \n";

    echo "Filter results...";

    $uniqueOptions = [];
    foreach ($foundValues as $foundValue) {
        if (in_array($foundValue['id'], $uniqueOptions) !== false) continue;
        $uniqueOptions[] = $foundValue['id'];
        echo ".";
    }

    $unique = [];
    foreach ($foundValues as $foundValue) {

        foreach ($uniqueOptions as $option) {

            if (!isset($unique[$option])) $unique[$option] = [];

            if ($foundValue['id'] == $option){
                if (in_array($foundValue['value'], $unique[$option]) !== false) continue;
                $unique[$option][] = $foundValue['value'];
            }
        }

    }

    echo " done \n";


    echo "Generate Export...";
    $report = [];


    $report[] = sprintf('# MHT INST research: Collect all Options with the related values');
    $report[] = sprintf('# Process Folder: %s', $folder);
    $report[] = sprintf('# %s unique options(s) found', count($unique));
    $report[] = str_repeat('#', 40);
    $report[] = '';

    $report[] = sprintf('# Unknown Options');
    $report[] = str_repeat('#', 20);

    foreach ($unique as $optionName => $values) {
        if (in_array($optionName, Manhunt2::$hashNames) !== false) continue;

        if (count($values) == 1){
            $report[] = sprintf('# Option %s has only one value %s', $optionName, $values[0]);

        }else{
            $report[] = sprintf('# Option %s has %s unique values', $optionName, count($values));
            $report[] = '';

            sort($values);
            foreach ($values as $foundValue) {
                $report[] = sprintf('%s', $foundValue);
            }

            $report[] = '';
        }

    }

    $report[] = str_repeat('#', 20);
    $report[] = '';
    $report[] = sprintf('# Known Options');

    foreach ($unique as $optionName => $values) {
        if (in_array($optionName, Manhunt2::$hashNames) !== false) continue;

        $report[] = sprintf('# Option %s has %s unique values', $optionName, count($values));
        $report[] = '';
        foreach ($values as $foundValue) {
            $report[] = sprintf('%s', $foundValue);
        }

        $report[] = '';

    }
    $report[] = str_repeat('#', 20);

    file_put_contents(sprintf('mht-research_inst.txt'), implode("\n", $report));
    echo " done \n";

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