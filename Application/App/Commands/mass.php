<?php
ini_set('memory_limit','-1');

require_once __DIR__ . '/../../vendor/autoload.php';

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
    case 3:
        list($script, $folder, $type) = $argv;
        break;
    case 4:
        list($script, $folder, $type, $game) = $argv;
        break;
    case 5:
        list($script, $folder, $type, $game, $platform) = $argv;
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

    try{
        $resource = $resources->load($file, $game, $platform);

        if ($onlyUnzip){
            //prepare output folder
            $outputTo = $outputFolder . '/' . $file->getRelativePath();
            @mkdir($outputTo, 0777, true);

            file_put_contents(
                $outputTo.  "/" . $file->getFilename(),
                $resource->getInput()->binary
            );

            echo sprintf("Saved to %s.",  $outputTo) . "\n";
            continue;
        }


    }catch(\Exception $e) {
//                $output->writeln('Not supported ' . $file->getRelativePathname());

        continue;
    }



    $handler = $resource->getHandler();
    echo 'Handler ' . $handler->name . ' for file ' . $file->getRelativePathname() . "\n";


    if (isset($handler->keepOrder)){
        if (in_array('ignore-order', $options) !== false){
            $handler->keepOrder = false;
        }else{
            $handler->keepOrder = $keepOrder;
        }
    }

    $originalExtension = $file->getExtension();

    if ($flat){
        //prepare output folder

        $outputTo = $outputFolder . '/' ;
//        $outputTo = $outputFolder . '/' . str_replace('/', '_', $file->getRelativePath());

    }else{
        //prepare output folder
        $outputTo = $outputFolder . '/' . $file->getRelativePath();
        @mkdir($outputTo, 0777, true);
    }


    $results = $handler->unpack($resource->getInput(), $game, $platform);

    if ($handler instanceof Mls){
        $results = $handler->getValidatedResults( $results, $game, $platform );
    }


    if ($handler instanceof App\Service\Archive\Tex){
        if (in_array('to-png', $options) !== false ){
            $ddsHandler = new \App\Service\Archive\Dds();
            $imageHandler = new \App\Service\Archive\Textures\Image();
            foreach ($results as $filename => $result) {
                $ddsResult = $ddsHandler->unpack( new \App\Service\NBinary($result), $game, $platform );

                unset($results[$filename]);
                $filename = substr($filename,0, -3) . 'png';
                $results[$filename] = $imageHandler->rgbaToImage($ddsResult['rgba'], $ddsResult['width'], $ddsResult['height']);
            }
        }
    }


    if (is_array($results)){

        foreach ($results as $relativeFilename => $data) {


            //we loop through dataset not a fileset
            if ( is_numeric($relativeFilename) ){

                if ($flat) {
//                    if (file_exists($outputTo)) continue;

                    file_put_contents(
                        $outputTo . '_' . $file->getFilename() . '.json',
                        \json_encode($results, JSON_PRETTY_PRINT)
                    );
                }else{


                    file_put_contents(
                        $outputTo . '/' . $file->getFilename() . '.json',
                        \json_encode($results, JSON_PRETTY_PRINT)
                    );
                }

                if (json_last_error() !== 0){
                    var_dump($results);
                    echo 'EMERGENCY JSON error received: ' . json_last_error_msg();
                    exit;
                }


                break;

            }else{
                $pathInfo = pathinfo($relativeFilename);

                if ($flat) {
                    $outputDir = $outputTo;
//                    $outputDir = $outputTo . '_' . str_replace('.', '#', $file->getFilename()) . '_' . $pathInfo['dirname'];
                    @mkdir($outputTo, 0777, true);

                }else{
                    $outputDir = $outputTo . '/' . str_replace('.', '#', $file->getFilename()) . '/' . $pathInfo['dirname'];
                    @mkdir($outputDir, 0777, true);
                }

                if (isset($pathInfo['extension'])) {
                    $extension = ''; // we keep the extension from the given filename
                }else if (is_array($data)){
                    $extension = '.json';

                }else{
                    $extension = '.' . $originalExtension;
                }

                try{

                    if (is_array($data)){
                        $data = \json_encode($data, JSON_PRETTY_PRINT);
                    }



                    if ($flat) {
                        $md5 = md5($data);
                        if (!isset($md5ByFile[$md5])) $md5ByFile[$md5] = [];
                        if ($pathInfo['filename'] == ""){
                            $pathInfo['filename'] = "noname";
                        }

                        if ($extension == ""){
                            $extension = "." . explode(".", $pathInfo['basename'])[1];
                        }else {
                            $extension = "." . $extension;
                        }

                        $finalNamePath = $outputDir . $pathInfo['filename'] . $extension;


                        $appendix = "";
                        while (file_exists($outputDir . $pathInfo['filename'] . $appendix . $extension)){
                            if ($appendix === ""){
                                $appendix = 1;
                                continue;
                            }

                            $appendix++;
                        }

                        $finalNamePath = $outputDir . $pathInfo['filename'] . $appendix . $extension;
                        $md5ByFile[$md5][] = $finalNamePath;

                        file_put_contents($finalNamePath, $data);

                    }else{
                        file_put_contents($outputDir . '/' . $pathInfo['basename'] . $extension, $data);

                    }
                }catch(JsonException $e){

//                    var_dump($e->getMessage());

                }

            }

        }


    }else{

        if ($flat) {
            file_put_contents(
                $outputTo . '_' . $file->getFilename(),
                $results
            );
        }else{
            file_put_contents(
                $outputTo . '/' . $file->getFilename(),
                $results
            );

        }

    }

}

if ($noDuplicates == true){
    $deleted = 0;
    echo "Delete duplicated files \n";
    foreach ($md5ByFile as $entries) {
        if (count($entries) > 1){
            array_pop($entries);
            foreach ($entries as $entry) {
                echo ".";
                unlink($entry);
                $deleted++;
            }
        }
    }

    echo "\n";
    echo sprintf("%s files deleted", $deleted) . "\n";
    echo sprintf("%s files keep", count($md5ByFile)) . "\n";
}


echo "\nDone.\n";


function printHelp(){

    echo "Usage: php mass.php <folder> <extension> [game] [platform]\n";
    echo "Example: php mass.php . mls mh2 pc\n";

}