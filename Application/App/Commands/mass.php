<?php
//ini_set('memory_limit','-1');
//
//require_once __DIR__ . '/../../vendor/autoload.php';

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
    if (substr($argument, 0, 2) == "-"){
        $options[] = substr($argument, 2);
        unset($argv[$index]);
    }
}

switch (count($argv)){
    case 4:
        list($file, $script, $folder, $type) = $argv;
        break;
    case 5:
        list($file, $script, $folder, $type, $game) = $argv;
        break;
    case 6:
        list($file, $script, $folder, $type, $game, $platform) = $argv;
        break;
    default:
        printHelp();
        exit;
        break;
}

$folder = realpath($folder);
$onlyUnzip = in_array('only-unzip', $options) !== false;
if ($onlyUnzip == false){
    $onlyUnzip = in_array('unzip-only', $options) !== false;
}
$flat = in_array('flat', $options) !== false;
$noDuplicates = in_array('no-duplicates', $options) !== false;


if ($noDuplicates && $flat == false){
    echo "Option 'no-duplicates' can only be used with the option '--flat'\n";
    exit;
}

$path = pathinfo($folder);
//prepare output folder
$outputTo = $path['dirname'] . "/" . $path['basename'] . '/export';
if (!is_dir($outputTo))
    @mkdir($outputTo, 0777, true);
$outputFolder = realpath($outputTo);


//load the resource
$resources = new Resources();

$finder = new Finder();
if ( $type == null){
    $finder
        ->name('/\.bin/i')
        ->name('/\.col/i')
        ->name('/\.dff/i')
        ->name('/\.glg/i')
        ->name('/\.gxt/i')
        ->name('/\.ifp/i')
        ->name('/\.inst/i')
        ->name('/\.pak/i')
        ->name('/\.tex/i')
        ->name('/\.txd/i')
        ->name('/\.fsb/i')
        ->name('/\.afs/i')
        ->name('/\.mls/i')
        ->files()
        ->in($folder);
}else{
    $finder
        ->name('/' . $type . '/i')

        ->files()
        ->in($folder);
}

$md5ByFile = [];


echo sprintf("Mass Extraction for %s files", $finder->count()) . "\n";


foreach ($finder as $file) {
    $mht = __DIR__ . '/../../mht.php';

    $output = shell_exec(sprintf(
        'php %s %s %s %s %s --no-header -ignore-order', $mht, 'unpack', $file, $game, $platform
    ) );
}


echo "\nDone.\n";


function printHelp(){

    echo "Usage: php mht.phar mass <folder> <extension> [game] [platform]\n";
    echo "Example: php mht.phar mass . mls mh2 pc\n";

}