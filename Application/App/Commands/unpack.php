<?php
//ini_set('memory_limit','-1');
//
//require_once __DIR__ . '/../../vendor/autoload.php';

use App\MHT;
use App\Service\Archive\Dds;
use App\Service\Archive\Mls;
use App\Service\Archive\Textures\Image;
use App\Service\ImageMagick;
use App\Service\MyFinder;
use App\Service\NBinary;
use App\Service\Resources;

$game = MHT::GAME_MANHUNT_2;
$platform = MHT::PLATFORM_PC;



$options = [];
foreach ($argv as $index => $argument) {
    if (substr($argument, 0, 1) == "-"){
        $options[] = substr($argument, 1);
        unset($argv[$index]);
    }
}

$argv = array_values($argv);

switch (count($argv)){
    case 3:
        list($script, $cmd ,$file) = $argv;
        break;
    case 4:
        list($script, $cmd,$file, $game) = $argv;
        break;
    case 5:
        list($script, $cmd, $file, $game, $platform) = $argv;
        break;
    default:
        printHelp();
        exit;
        break;
}

$file = realpath($file);

$myFinder = new MyFinder($file);

$keepOrder = true;

if ($myFinder->game !== null && $myFinder->platform !== null){
    $game = $myFinder->game;
    $_platform = $myFinder->platform;

    if ($platform === "psp001" && $_platform === "psp")
        $platform = "psp001";
    else
        $platform = $_platform;
}

echo sprintf("Game: %s | Platform: %s\n", $game, $platform);

if ($game !== MHT::GAME_AUTO){
    if ($game != MHT::GAME_MANHUNT && $game != MHT::GAME_MANHUNT_2 && $game != MHT::GAME_BULLY){
        throw new \Exception('Invalid game, allowed is mh1, mh2 or bully');
    }
}

if ($platform !== MHT::PLATFORM_AUTO){
    if (
        $platform != MHT::PLATFORM_PC &&
        $platform != MHT::PLATFORM_PS2 &&
        $platform != MHT::PLATFORM_PS2_064 &&
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
if (is_dir($outputTo) === false)
    @mkdir($outputTo, 0777, true);

if(
    in_array('unzip-only', $options) !== false ||
    in_array('only-unzip', $options) !== false ||
    in_array('unzip', $options) !== false
){
    $input = new NBinary( file_get_contents($file) );

    $outputTo = str_replace("#", '.', $outputTo);

    file_put_contents(
        $outputTo,
        $input->binary
    );

    echo sprintf("Saved to %s.",  $outputTo);
    return;
}

//load the resource
$resources = new Resources();
$resource = $resources->load($file, $game, $platform);
//


$handler = $resource->getHandler();

echo sprintf('Identify file as %s', $handler->name) . "\n";
echo sprintf('Processing %s ', $file);


if (isset($handler->asRaw)){
    if (in_array('raw', $options) !== false){
        $handler->asRaw = true;
    }else{
        $handler->asRaw = false;
    }
}


if (isset($handler->mono)){
    if (in_array('mono', $options) !== false){
        $handler->mono = true;
    }else{
        $handler->mono = false;
    }
}
if (isset($handler->chunk400)){
    if (in_array('chunk400', $options) !== false){
        $handler->chunk400 = true;
    }else{
        $handler->chunk400 = false;
    }
}

if (isset($handler->onlyMemDump)){
    if ($cmd === "memdump"){
        $handler->onlyMemDump = true;
    }else{
        $handler->onlyMemDump = false;
    }
}

if (isset($handler->keepOrder)){
    if (in_array('ignore-order', $options) !== false){
        $handler->keepOrder = false;
    }else{
        $handler->keepOrder = $keepOrder;
    }
}

if (isset($handler->keepOrder)){
    if (in_array('cutscene', $options) !== false){
        $handler->isCutscene = true;
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

    $levelSpeechFolder = str_replace('.fsb', '', $file);
    $levelSpeechFolder = explode(DIRECTORY_SEPARATOR, $levelSpeechFolder);
    $levelName = $levelSpeechFolder[count($levelSpeechFolder) - 1];
    unset($levelSpeechFolder[count($levelSpeechFolder) - 1]);
    $levelSpeechFolder = implode(DIRECTORY_SEPARATOR, $levelSpeechFolder);
    $levelFolder = $levelSpeechFolder . '/../../../levels/' . $levelName;
    $levelSpeechFile = $levelFolder . '/speech.lst';

    $newResults = [];
    $known = 0;
    $unknown = 0;


    if (file_exists($levelSpeechFile)){
        $contextList = explode("\r\n", file_get_contents($levelSpeechFile));

        $mapFull = [];

        $indexWav = 0;
        $contextNames = [];
        foreach ($contextList as $contextName) {
            if (empty($contextName)) continue;

            $contextMapResource = $resources->load($levelSpeechFolder . '/' . $contextName . '/context_map.bin', $game, $platform);
            $contextMaphandler = $contextMapResource->getHandler();
            $contextMap = $contextMaphandler->unpack($contextMapResource->getInput(), $game, $platform);
//
//            foreach ($contextMap['result'] as $map) {
//                $contextNames[] = $map['name'];
//            }


            reset($results);
            foreach ($contextMap['result'] as $mapIndex => $map) {

//var_dump($map['index']);
                //hmm hab den array index verändert, ka nomma prüfen ^
                $newResults[$contextMap['name'] . '/' . $map['name'] . '/' . $mapIndex . '.wav'] = next($results);
            }

            $indexWav += count($contextMap['result']);


        }

        $newResults['fsb3.json'] = $results['fsb3.json'];
        $results = $newResults;


    }else if (file_exists($dirFile)){

        $dirResource = $resources->load(str_replace('.fsb', '.dir', $file), $game, $platform);
        $dirHandler = $dirResource->getHandler();
        $dirResult = $dirHandler->unpack( $dirResource->getInput(), $game, $platform );


        $results['fsb3.json'] = \json_decode($results['fsb3.json'], true);
        $results['fsb3.json']['orders'] = [];
        foreach ($results as $filename => $data) {
            if ($filename == "fsb3.json") continue;

            $index = (int) explode('.', $filename)[0];
            $extension = explode('.', $filename)[1];


            list($hashName, $originalFile) = $dirResult[$index];


            if (strpos($hashName, 'scripted') !== false){
                $newFilename = str_replace('\wii_stream', '', $hashName);
                $newFilename = str_replace('\pc_stream', '', $newFilename);
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

            $newResults['fsb3.json'] = \json_encode($results['fsb3.json'], JSON_PRETTY_PRINT);
            echo sprintf("\nTranslation: %s٪\n", number_format($known / ($unknown + $known) * 100, 2));
            $results = $newResults;


            //Duplicate handling
            $resultCount = count($results) - 1;
            $uniq = in_array('no-duplicates', $options) !== false ||
                in_array('uniq', $options) !== false;

            if (isset($results['fsb3.json'])) {
                $results['fsb3.json'] = \json_decode($results['fsb3.json'], true);
            }

            $knownMd5 = [];
            $duplicateCount = 0;
            foreach ($results as $filepathName => $result) {
                if ($filepathName == "fsb3.json") continue;
                if (substr($filepathName, 0, 7) == "unknown") continue;

                if(!empty($result))
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

            if ($duplicateCount > 0 && $resultCount > 0)
                echo sprintf("Duplicates: %s٪. ", number_format($duplicateCount / $resultCount * 100, 2));

            if ($uniq){
                echo sprintf("%s duplicate audios removed.\n", $duplicateCount);
            }else{
                echo "\n";
            }
        }



    }else{
        echo "\nWARNING: unable to locate " . $dirFile . "! Store as nonamed WAV!\n";
    }




}

if ($handler instanceof App\Service\Archive\Font){

    $textureFolder = false;
    if ($game === MHT::GAME_MANHUNT_2){
        if ($platform === MHT::PLATFORM_PC){
            $textureFolder = $myFinder->findFile('global_pc.tex');
        }else if ($platform === MHT::PLATFORM_PS2 || $platform === MHT::PLATFORM_PSP){
            $textureFolder = $myFinder->findFile('GLOBAL.TXD');
        }else if ($platform === MHT::PLATFORM_WII){
            $textureFolder = $myFinder->findFile('global_wii.txd');
        }
    }

    if ($textureFolder === false)
        die("Unable to find texture file");

    $resourcesFont = new Resources();
    $resourceFont = $resourcesFont->load($textureFolder, $game, $platform);
    $fontImageHandler = $resourceFont->getHandler();

    $textureResults = $fontImageHandler->unpack( $resourceFont->getInput(), $game, $platform );

    $ddsHandler = new Dds();
    $imageHandler = new Image();

    $resultsNew = [];
    foreach ($results as $fontName => $font) {

        if ($platform === MHT::PLATFORM_PC) {
            $textureName = $fontName . '.dds';
        }else if ($platform === MHT::PLATFORM_PS2 || $platform === MHT::PLATFORM_PSP || $platform === MHT::PLATFORM_WII) {
            $textureName = $fontName . '.png';
        }

        foreach ($textureResults as $name => $textureResult) {
            if ($textureName == $name){


                if ($game === MHT::GAME_MANHUNT_2 && $platform === MHT::PLATFORM_PC) {
                    $ddsResult = $ddsHandler->unpack(new NBinary($textureResult), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
                    $imageBinary = $imageHandler->rgbaToImage($ddsResult['rgba'], $ddsResult['width'], $ddsResult['height']);
                }else{
                    $imageBinary = $textureResult;
                }

                file_put_contents('tmp.png', $imageBinary);
                $image = imagecreatefrompng('tmp.png');
                imageAlphaBlending($image, true);
                imageSaveAlpha($image, true);


                $size = getimagesize('tmp.png');

                foreach ($font['charInfoTable'] as $item) {

                    $height = ($item['position']['y2'] *  $size[1]) - ($item['position']['y1']) * $size[1];
                    if ($height === 0.0){
                        $height = $font['fontHeight'] * 2 * $size[1];
                    }
                    // Ausschnitt erstellen
                    $croppedImage = imagecrop($image, [
                        'x' => $item['position']['x1'] * $size[0],
                        'y' => $item['position']['y1'] * $size[1],
                        'width' => ($item['position']['x2'] * $size[0] ) - ($item['position']['x1']) * $size[0],
                        'height' => $height
                    ]);

                    imageAlphaBlending($croppedImage, true);
                    imageSaveAlpha($croppedImage, true);
                    imagepng($croppedImage, 'tmp2.png', 0);

                    imagedestroy($croppedImage);
                    $resultsNew[$fontName . '/' . $item['code'] . '.png'] = file_get_contents('tmp2.png');
                    unlink('tmp2.png');
                }

                // Speicher freigeben
                imagedestroy($image);
                unlink('tmp.png');
            }
        }


        $results = $resultsNew;

    }
}


if ($handler instanceof App\Service\Archive\Tex){

    $imageMagick = new ImageMagick();
    if ($imageMagick->isAvailable() === false){
        if (in_array('to-png', $options) !== false ){
            echo "No ImageMagick found, please install for best results.\n";
            $ddsHandler = new Dds();
            $imageHandler = new Image();
            foreach ($results as $filename => $result) {
                $ddsResult = $ddsHandler->unpack( new NBinary($result), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC );

                unset($results[$filename]);
                $filename = substr($filename,0, -3) . 'png';
                $results[$filename] = $imageHandler->rgbaToImage($ddsResult['rgba'], $ddsResult['width'], $ddsResult['height']);
            }
        }
    }else{
        $ddsHandler = new Dds();
        $imageHandler = new Image();

        $newResults = [];
        foreach ($results as $filename => $content) {


            $header = $ddsHandler->readHeader(new NBinary($content));
            if($header['format'] == "DXT1") {
                $format = "jpg";

            }else if($header['format'] == "DXT5") {
                $format = "png";
            }else{
                //Ramps are strange DDS files, has a DDS container but holds only RGB values
                //dunno how to pack something like this with imagemagick

                $newResults[$filename] = $content;
                continue;
//
//                $data = new NBinary($content);
//                $rgba = [];
//                while($data->remain()){
//                    $rgba[] = $data->consume(1, NBinary::U_INT_8);
//                }
//
//                $content = $imageHandler->rgbaToImage($rgba, $header['width'], $header['height']);
//                $format = "bmp";
            }

            $filename = str_replace('.dds', '.' . $format, $filename);
            $newResults[$filename] = $imageMagick->convertTo($content, $format);
        }

        $results = $newResults;
    }
//
//
//

//    }
}



if ($handler instanceof Mls){
    $results = $handler->getValidatedResults( $results, $game, $platform );
}


if (is_array($results)){
    $wavHandler = new \App\Service\Archive\Wav();
    $adx2wav = new \App\Service\AudioCodec\AdxPcma();
    $vas2wav = new \App\Service\Archive\Vas();

    foreach ($results as $relativeFilename => $data) {


        if (
            (
                in_array('to-pcm', $options) !== false ||
                in_array('convert', $options) !== false ||
                in_array('pcm', $options) !== false

            )

        ){
            if ($handler instanceof App\Service\Archive\Fsb3 || $handler instanceof App\Service\Archive\Fsb4){
                if (substr($relativeFilename, -3) === "wav") {
                    echo "Convert " . $relativeFilename . " to PCM ...";
                    $data = $wavHandler->unpack(new \App\Service\NBinary($data), MHT::GAME_AUTO, MHT::PLATFORM_AUTO);
                    echo "OK\n";
                }else if (substr($relativeFilename, -3) === "vas") {
                    echo "Convert " . $relativeFilename . " to PCM ...";
                    $data = $wavHandler->unpack(new \App\Service\NBinary($data), MHT::GAME_AUTO, MHT::PLATFORM_AUTO);
                    $relativeFilename = substr($relativeFilename, 0, -3) . 'wav';
                    echo "OK\n";
                }else if (substr($relativeFilename, -4) === "genh"){
                    echo "Convert " . $relativeFilename . " to PCM (using ffmpeg)...";
                    file_put_contents('tmp.genh', $data);
                    system('ffmpeg -i tmp.genh tmp.wav');
                    if (file_exists('tmp.wav')){
                        $data = file_get_contents('tmp.wav');
                        $relativeFilename = substr($relativeFilename, 0, -4) . 'wav';

                        echo "OK\n";
                    }else{
                        echo "failed\n";
                        echo "is ffmpeg installed and global available ?\n";
                        exit;
                    }

                    @unlink('tmp.genh');
                    @unlink('tmp.wav');
//                    $data = $wavHandler->unpack(new \App\Service\NBinary($data), MHT::GAME_AUTO, MHT::PLATFORM_AUTO);
                }

            }else if ($handler instanceof App\Service\Archive\Afs){
                if (substr($relativeFilename, -3) === "adx"){

                    echo "Convert ADX " . $relativeFilename . " to PCM ...";
                    $relativeFilename = substr($relativeFilename, 0, -3) . 'wav';
                    $data = $adx2wav->decode(new \App\Service\NBinary($data));
                    echo "OK\n";

                }

                if (substr($relativeFilename, -3) === "vas"){

                    echo "Convert VAS " . $relativeFilename . " to PCM ...";
                    $relativeFilename = substr($relativeFilename, 0, -3) . 'wav';
                    $data = $vas2wav->unpack(new \App\Service\NBinary($data), $game, $platform);
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

            if ($relativeFilename === "")
                $outputDir = $outputTo;
            else
                $outputDir = $outputTo . '/' . $pathInfo['dirname'];

            if (!is_dir($outputDir))
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


    if (get_class($handler) === "App\Service\Archive\Rib"){
        $outputTo .= ".wav";
    }else{
        $outputTo = str_replace("#", '.', $outputTo);
    }

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

    echo "Usage: mht unpack [options] <filenamw> [game] [platform]\n";
    echo "Example: mht unpack A01_Escape_Asylum.mls mh2 pc\n";

}