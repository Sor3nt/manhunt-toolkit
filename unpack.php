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
        $platform != MHT::PLATFORM_PSP_001 &&
        $platform != MHT::PLATFORM_XBOX &&
        $platform != MHT::PLATFORM_WII
    ){
        throw new \Exception('Invalid platform, allowed is pc, ps2, psp, psp001, xbox, wii');
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
if(
    in_array('unzip-only', $options) !== false ||
    in_array('only-unzip', $options) !== false ||
    in_array('unzip', $options) !== false
){

    $outputTo = str_replace("#", '.', $outputTo);

    file_put_contents(
        $outputTo,
        $resource->getInput()->binary
    );

    echo sprintf("Saved to %s.",  $outputTo);
    return;
}

$handler = $resource->getHandler();

echo sprintf('Identify file as %s', $handler->name) . "\n";
echo sprintf('Processing %s ', $file);


if (isset($handler->keepOrder)){
    if (in_array('ignore-order', $options) !== false){
        $handler->keepOrder = false;
    }else{
        $handler->keepOrder = $keepOrder;
    }
}

if ($handler instanceof App\Service\Archive\Fsb3 ||
    $handler instanceof App\Service\Archive\Fsb4){

    $handler->debug = in_array('debug', $options) !== false;
}


$results = $handler->unpack( $resource->getInput(), $game, $platform );

//Try to resolve FSB3 audio names
if ($handler instanceof App\Service\Archive\Fsb4){

    $fevFile = str_replace('.fsb', '.fev', $file);

    if (file_exists($fevFile)){

        $fevResource = $resources->load($fevFile, $game, $platform);
        $fevHandler = $fevResource->getHandler();
        $fevResult = $fevHandler->unpack( $fevResource->getInput(), $game, $platform );


        foreach ($fevResult['relations']['blocks'] as $block) {

            $folderName = $block['name'];

            foreach ($block['relations'] as $file) {

                $filenameParts = explode("/", $file['wav']);
                $innerFolder = $filenameParts[ count($filenameParts) - 2];
                $filename = $filenameParts[ count($filenameParts) - 1];

                foreach ($results as $fsbFilename => $result) {
                    if (substr($fsbFilename, -3) !== "wav") continue;

                    if (strtolower($filename) == strtolower($fsbFilename)){
                        if (count($block['relations']) == 1){
                            $newFilename = $innerFolder . '/' . $filename;

                        }else{
                            $newFilename = $innerFolder . '/' .  $folderName . '/' . $filename;

                        }

                        $results[$newFilename] = $result;
                        unset($results[$fsbFilename]);
                    }


                }

            }

        }
    }else{
        echo "\nWARNING: unable to locate " . $fevFile . "! Store without folders structure!\n";
    }
}
if ($handler instanceof App\Service\Archive\Fsb3){

    $dirFile = str_replace('.fsb', '.dir', $file);

    if (file_exists($dirFile)){

        $dirResource = $resources->load(str_replace('.fsb', '.dir', $file), $game, $platform);
        $dirHandler = $dirResource->getHandler();
        $dirResult = $dirHandler->unpack( $dirResource->getInput(), $game, $platform );

        $newResults = [];
        $known = 0;
        $unknown = 0;

        $results['fsb3.json'] = \json_decode($results['fsb3.json'], true);
        $results['fsb3.json']['orders'] = [];
        foreach ($results as $filename => $data) {
            if ($filename == "fsb3.json") continue;

            $index = (int) explode('.', $filename)[0];
            $extension = explode('.', $filename)[1];


            list($hashName, $originalFile) = $dirResult[$index];


            if (strpos($hashName, 'scripted') !== false){
                $newFilename = str_replace('\wii_stream', '', $hashName);
                $newFilename = str_replace('\pc_stream', '', $hashName);
                $newFilename = str_replace('scripted\\', '', $newFilename);
                $newFilename = str_replace('\\', '/', $newFilename);

                list($folder, $file) = explode("/", $newFilename);

                $results['fsb3.json']['orders'][] = $folder . '/' . $file;
                $newResults[$folder . '/' . $file] = $data;
                $known++;
            }else if (strpos($hashName, 'executions') !== false){
                $newFilename = str_replace('executions\\', '', $hashName);
                $newFilename = str_replace('\\', '/', $newFilename);

                list($folder, $file) = explode("/", $newFilename);

                $results['fsb3.json']['orders'][] = $originalFile . '/' . $file;

                $newResults[$originalFile . '/' . $file] = $data;
                $known++;
            }else{
                $results['fsb3.json']['orders'][] = 'unknown/' . $filename;
                $newResults['unknown/' . $filename] = $data;
                $unknown++;
            }

        }

        $newResults['fsb3.json'] = \json_encode($results['fsb3.json'], JSON_PRETTY_PRINT);

        echo sprintf("\nTranslation: %s٪\n", number_format($known / ($unknown + $known) * 100, 2));

        $results = $newResults;
    }else{
        echo "\nWARNING: unable to locate " . $dirFile . "! Store as nonamed WAV!\n";
    }



    //Duplicate handling
    $resultCount = count($results) - 1;
    $uniq = in_array('no-duplicates', $options) !== false ||
            in_array('uniq', $options) !== false;

    $results['fsb3.json'] = \json_decode($results['fsb3.json'], true);

    $knownMd5 = [];
    $duplicateCount = 0;
    foreach ($results as $filepathName => $result) {
        if ($filepathName == "fsb3.json") continue;
        if (substr($filepathName, 0, 7) == "unknown") continue;
        $knownMd5[md5($result)] = explode("/", $filepathName)[0];
    }

    foreach ($results as $filepathName => $result) {
        if ($filepathName == "fsb3.json") continue;
        if (substr($filepathName, 0, 7) != "unknown") continue;

        if (isset($knownMd5[md5($result)])){
            $duplicateCount++;

            if ($uniq){
                unset($results[$filepathName]);
                $results['fsb3.json']['orders'] = removeOrderName($results['fsb3.json']['orders'], $filepathName);

            }else{
                //move the duplicate into his related folder
                $newPath = $knownMd5[md5($result)] . '/duplicate/' . explode("/", $filepathName)[1];
                $results[ $newPath ] = $result;
                unset($results[$filepathName]);

                $results['fsb3.json']['orders'] = replaceOrderName($results['fsb3.json']['orders'], $filepathName, $newPath);

            }
        }
    }

    $newResults['fsb3.json'] = \json_encode($results['fsb3.json'], JSON_PRETTY_PRINT);

    echo sprintf("Duplicates: %s٪. ", number_format($duplicateCount / $resultCount * 100, 2));

    if ($uniq){
        echo sprintf("%s duplicate audios removed.\n", $duplicateCount);
    }else{
        echo "\n";
    }

}

if ($handler instanceof App\Service\Archive\Tex){
    if (in_array('to-png', $options) !== false ){
        $ddsHandler = new Dds();
        $imageHandler = new Image();
        foreach ($results as $filename => $result) {
            $ddsResult = $ddsHandler->unpack( new NBinary($result), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

            unset($results[$filename]);
            $filename = substr($filename,0, -3) . 'png';
            $results[$filename] = $imageHandler->rgbaToImage($ddsResult['rgba'], $ddsResult['width'], $ddsResult['height']);
        }
    }
}



if ($handler instanceof Mls){
    $results = $handler->getValidatedResults( $results, $game, $platform );
}


if (is_array($results)){
    $wavHandler = new \App\Service\Archive\Wav();
    $adx2wav = new \App\Service\AudioCodec\AdxPcma();

    foreach ($results as $relativeFilename => $data) {


        if (
            (
                in_array('to-pcm', $options) !== false ||
                in_array('convert', $options) !== false ||
                in_array('pcm', $options) !== false

            )

        ){

            if ($handler instanceof App\Service\Archive\Fsb3 || $handler instanceof App\Service\Archive\Fsb4){
                if (substr($relativeFilename, -3) === "wav"){
                    echo "Convert " . $relativeFilename . " to PCM ...";
                    $data = $wavHandler->unpack(new \App\Service\NBinary($data), MHT::GAME_AUTO, MHT::PLATFORM_AUTO);
                    echo "OK\n";
                }

            }else if ($handler instanceof App\Service\Archive\Afs){
                if (substr($relativeFilename, -3) === "adx"){

                    echo "Convert " . $relativeFilename . " to PCM ...";
                    $relativeFilename = substr($relativeFilename, 0, -3) . 'wav';
                    $data = $adx2wav->decode(new \App\Service\NBinary($data));
                    echo "OK\n";

                }
            }


        }


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
    echo "*.FSB - Audio Container\t\t\t\t";
    echo "*.DIR - Audio Names\n";
    echo "*.AFS - Container Format\t\t\t\t";
    echo "\n";
    echo "\n";
    echo "Options for every zipped file\n";
    echo "\t--unzip [--unzip-only, --only-unzip]\tJust deflate a given archive";
    echo "\n";
    echo "\n";

    echo "Options per Format\n";
    echo "*.FSB\n";
    echo "\t--convert [--pcm, --to-pcm]\t\tConvert the Audios to PCM instead to ADPCM\n";
    echo "\t--uniq    [--no-duplicates]\t\tRemove all Duplicate (unused) Audios. (FSB3)\n";
    echo "\t--debug\t\t\t\t\tExtended FSB Output.\n";
    echo "\n";
    echo "*.AFS\n";
    echo "\t--convert [--pcm, --to-pcm]\t\tConvert the Audios to PCM instead to ADX\n";
    echo "\n";
    echo "*.TEX\n";
    echo "\t--to-png\t\tConvert the exported DDS into PNG\n";
    echo "\n";
    echo "\n";

    echo "Usage: php unpack.php [options] <filenamw> [game] [platform]\n";
    echo "Example: php unpack.php A01_Escape_Asylum.mls mh2 pc\n";

}