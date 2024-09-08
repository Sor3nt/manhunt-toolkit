<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Ifp extends Archive
{
    public $name = 'Animations';

    public static $supported = 'ifp';

    public $keepOrder = false;
    public $isCutscene = false;

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            $relPath = strtolower($file->getRelativePath());

            if (strpos($relPath, "#") !== false){
                $category = explode("#", $relPath)[1];
            }else{
                $category = $relPath;
            }

            switch (strtolower($category)){
                case 'bookends':
                case 'legion':
                case 'playeranims':
                case 'openables':
                case 'cutscene':
                case 'genhunteranims':
                    return true;

                default:
            }
        }

        return false;
    }

    public function unpackDetectGamePlatform( NBinary $binary ){

        $binary->current = 4;
        $numBlock = $binary->consume(4, NBinary::INT_32);

        if ($numBlock > 10000) {
            $binary->current = 0;
            return [ MHT::GAME_MANHUNT_2, MHT::PLATFORM_WII];
        }

        $firstBlock = $binary->range(0, 111);

        $binary->current = 0;

        if (strpos($firstBlock, 'SEQT')) {
            return [ MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC];
        }else if (strpos($firstBlock, 'SEQU')){
            return [ MHT::GAME_MANHUNT, MHT::PLATFORM_PC];
        }

        throw new \Exception('Unable to detect the game, wrong file ?!');
    }

    public function unpack(NBinary $binary, $game, $platform)
    {

        if ($game == MHT::GAME_AUTO) list($game, $platform) = $this->unpackDetectGamePlatform($binary);

        /**
         * ROOT (ANCT)
         */
        $headerType = $binary->consume(4, NBinary::STRING);

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $numBlock = $binary->consume(4, NBinary::INT_32);

        if ($headerType !== "ANCT")
            throw new \Exception(sprintf('Expected ANCT got: %s', $headerType));

        $results = [];
        /**
         * BLOCK (BLOC)
         */
        $count = 1;
        while ($numBlock > 0) {

            $sectionBLOC = $binary->consume(4, NBinary::STRING);

            if ($sectionBLOC !== "BLOC")
                throw new \Exception(
                    sprintf('Expected BLOC got: %s', $sectionBLOC)
                );


            //Get Block name
            $blockNameLength = $binary->consume(4, NBinary::INT_32);
            $blockName = $binary->consume($blockNameLength, NBinary::STRING);

            /**
             * Animation Packs
             */

            $headerType = $binary->consume(4, NBinary::STRING);

            if ($headerType !== "ANPK")
                throw new \Exception(
                    sprintf('Expected ANPK got: %s', $headerType)
                );

            $animationCount = $binary->consume(4, NBinary::INT_32);

            /**
             * Animation Pack Entries
             */

            if ($this->keepOrder){
                $path = $count . "#" . $blockName;
            }else{
                $path = $blockName;
            }

            $animations = $this->extractAnimation($animationCount, $binary, $game, $platform);

            foreach ($animations as $animationFilename => $animation) {
                $results[ $path . '/' . $animationFilename] = $animation;
            }

            $numBlock--;
            $count++;
        }

        return $results;
    }

    /**
     * @param $animationCount
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    public function extractAnimation($animationCount, NBinary $binary, $game, $platform)
    {
        $results = [];
        $animations = [];

        $count = 1;

        while ($animationCount > 0) {

            $nameLabel = $binary->consume(4, NBinary::STRING);

            if ($nameLabel !== "NAME")
                throw new \Exception(
                    sprintf('Expected NAME got: %s', $nameLabel)
                );


            $animationNameLength = $binary->consume(4, NBinary::INT_32);

            $animationName = $binary->consume($animationNameLength, NBinary::STRING);

            $numberOfBones = bin2hex($binary->get(4));

            if (strpos(strtolower($numberOfBones), 'ff') !== false) {
                $platform = MHT::PLATFORM_PS2;
                $numberOfBones = $binary->consume(4, NBinary::INT_16) * -1;

            } else {
                if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;
                $numberOfBones = $binary->consume(4, NBinary::INT_32);
            }

            $chunkSize = $binary->consume(4, NBinary::INT_32);

            $frameTimeCount = 0;
            if ($platform !== MHT::PLATFORM_PS2_064){
                $frameTimeCount = $binary->consume(4, NBinary::FLOAT_32);

            }

            $resultAnimation = [
                'frameTimeCount' => $frameTimeCount * 30,
            ];


            /**
             * Sequences
             */
            list($bones, $ps2FrameTimeCount) = $this->extractBones($numberOfBones, $binary, $chunkSize, $game, $platform);

            //ps2 correction
            if ($ps2FrameTimeCount !== false){
                $resultAnimation['frameTimeCount'] = $ps2FrameTimeCount * 30;
            }

            $resultAnimation['bones'] = $bones;

            //headerSize
            $binary->consume(4, NBinary::INT_32);

            //pecTime
            $unknown5 = $binary->consume(4, NBinary::HEX);

            //stupid quick hack... todo
            if ($unknown5 == "40400000") $unknown5 = "00004040";


            //eachEntrySize
            $binary->consume(4, NBinary::INT_32);
            $numEntry = $binary->consume(4, NBinary::INT_32);


            $resultAnimation['unknown5'] = $unknown5;

            $resultAnimation['entry'] = [];
            while ($numEntry > 0) {

                if ($game == MHT::GAME_MANHUNT) {

                    $entry = [
                        'time' => $binary->consume(4, NBinary::FLOAT_32),
                        'unknown' => $binary->consume(4, NBinary::HEX),
                        'unknown2' => $binary->consume(4, NBinary::HEX),
                        'unknown3' => $binary->consume(4, NBinary::HEX),
                        'unknown4' => $binary->consume(4, NBinary::HEX),
                        'unknown6' => $binary->consume(4, NBinary::FLOAT_32), //boneId todo rename
                        'particleName' => $binary->consume(8, NBinary::BINARY),
                        'particlePosition' => [
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32)
                        ],
                        'unknown5' => $binary->consume(4, NBinary::HEX)
                    ];


                    if (str_replace('00', '', $entry['particleName']) !== ''){
                        $seperator = strpos($entry['particleName'], "\x00");

                        $commandName = substr($entry['particleName'], 0, $seperator);
                        $unknownCommandRemain = substr($entry['particleName'], $seperator);

                        $entry['particleName'] = $commandName;
                        $entry['unknownParticleName'] = $unknownCommandRemain;
                    }

                    $resultAnimation['entry'][] = $entry;


                } else {

                    $unknownSize = 40;

                    //MH2 PSP v0.01 hack
                    if ($platform == MHT::PLATFORM_PSP_001){
                        $unknownSize = 4;
                    }

                    $entry = [
                        'time' => $binary->consume(4, NBinary::FLOAT_32),
                        'unknown' => $binary->consume(4, NBinary::HEX),
                        'unknown2' => $binary->consume(4, NBinary::HEX),
                        'CommandName' => $binary->consume(64, NBinary::BINARY),
                        'unknown3' => $binary->consume(4, NBinary::HEX),
                        'unknown6' => $binary->consume(4, NBinary::FLOAT_32), //boneId todo rename
                        'particleName' => $binary->consume(8, NBinary::BINARY),
                        'particlePosition' => [
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32),
                            $binary->consume(4, NBinary::FLOAT_32)
                        ],
                        'unknown5' => $binary->consume($unknownSize, NBinary::HEX)
                    ];


                    if ($entry['unknown3'] == "00000001") $entry['unknown3'] = "01000000";


                    if (str_replace('00', '', $entry['CommandName']) !== ''){
                        $seperator = strpos($entry['CommandName'], "\x00");

                        $commandName = substr($entry['CommandName'], 0, $seperator);
                        $unknownCommandRemain = substr($entry['CommandName'], $seperator);

                        $entry['CommandName'] = $commandName;
                        $entry['unknownCommandRemain'] = $unknownCommandRemain;
                    }


                    if (str_replace('00', '', $entry['particleName']) !== ''){
                        $seperator = strpos($entry['particleName'], "\x00");

                        $commandName = substr($entry['particleName'], 0, $seperator);
                        $unknownCommandRemain = substr($entry['particleName'], $seperator);

                        $entry['particleName'] = $commandName;
                        $entry['unknownParticleName'] = $unknownCommandRemain;
                    }


                    $resultAnimation['entry'][] = $entry;

                }

                $numEntry--;
            }


            $animations[] = $resultAnimation;



            if ($this->keepOrder){
                $results[ $count . "#" . $animationName ] = $resultAnimation;
            }else{
                $results[ $animationName ] = $resultAnimation;
            }

            $animationCount--;
            $count++;
        }

        return $results;
    }

    private function extractBones($numberOfBones, NBinary $binary, $chunkSize, $game, $platform)
    {

        $frameTimeCount = false;
        $bones = [];

        if ($game == MHT::GAME_MANHUNT_2 && $platform == MHT::PLATFORM_PS2) {

            $zlibData = $binary->consume($chunkSize, NBinary::BINARY);

            $zlibData = zlib_decode($zlibData);

            $binary = new NBinary($zlibData);

            //unknown ps2 values
            $unknown = $binary->consume(4, NBinary::FLOAT_32);

            $frameTimeCount = $binary->consume(4, NBinary::FLOAT_32);
        }

        while ($numberOfBones > 0) {

            $sequenceLabel = $binary->consume(4, NBinary::STRING);

            if ($sequenceLabel !== "SEQT" && $sequenceLabel !== "SEQU") {
                throw new \Exception(
                    sprintf('Expected SEQT or SEQU got: %s', $sequenceLabel)
                );
            }

            $boneId = $binary->consume(2, NBinary::INT_16);
            $frameType = $binary->consume(1, NBinary::INT_8);
            $frames = $binary->consume(2, NBinary::INT_16);

            if ($platform == MHT::PLATFORM_WII){
                $startTime = $binary->consume(2, NBinary::INT_16);
            }else{
                $startTime = $binary->consume(2, NBinary::LITTLE_U_INT_16);

            }

            $startTime = ($startTime / 2048) * 30;

            $resultBone = [
                'boneId' => $boneId,
                'frameType' => $frameType,
                'startTime' => $startTime,
                'frames' => []
            ];


            /**
                if frameType > 2 then
                (
                [((readshort f)/2048.0),((readshort f)/2048.0),((readshort f)/2048.0),((readshort f)/2048.0)]
                )
                else if startTime == 0 then fseek f -2 #seek_cur
             *
             */

            if ($frameType == 3) {

                $resultBone['direction'] = [
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048
                ];



            }else if($frameType < 3 && $startTime == 0){
                //back to starttime
                $binary->current -= 2;
            }

            /**
             * FRAMES
             */

            $resultBone['frames'] = $this->extractFrames(
                $startTime,
                $frames,
                $frameType,
                $binary,
                $game,
                $platform,
                $sequenceLabel
            );

            $bones[] = $resultBone;

            $numberOfBones--;
        }

        return [$bones, $frameTimeCount];
    }

    private function extractFrames($startTime, $frames, $frameType, NBinary $binary, $game, $platform, $sequenceLabel)
    {

        $resultFrames = [ 'frames' => [] ];

        $index = 0;
        $frameTime = 0;

        while ($frames > 0) {

            $resultFrame = [];

            if ($startTime == 0) {

                // first frame == starTime
                if ($index == 0 && $frameType == 3) {
                    $curTime = 0;
                } else {
                    if ($platform == MHT::PLATFORM_WII) {
                        $time = $binary->consume(2, NBinary::INT_16);
                    }else{
                        $time = $binary->consume(2, NBinary::LITTLE_U_INT_16);

                    }

                    $resultFrame['time'] = $time / 2048 * 30;
                    $curTime = $resultFrame['time'];
                }

                $frameTime += $curTime;
            }else{
                if ($startTime < 1) $startTime = 1;

                $frameTime = ($index/2048*30)+$startTime-1;
            }

            if ($frameType < 3) {
                $factor = 4096;

                if ($platform == MHT::PLATFORM_WII){
                    $resultFrame['quat'] = [
                        $binary->readSwitchedInt16() / $factor,
                        $binary->readSwitchedInt16() / $factor,
                        $binary->readSwitchedInt16() / $factor,
                        $binary->readSwitchedInt16() / $factor
                    ];

                }else{

                    $resultFrame['quat'] = [
                        $binary->consume(2, NBinary::INT_16) / $factor,
                        $binary->consume(2, NBinary::INT_16) / $factor,
                        $binary->consume(2, NBinary::INT_16) / $factor,
                        $binary->consume(2, NBinary::INT_16) / $factor,
                    ];
                }


            }

            if ($frameType > 1) {

                $factor = 2048;
                if ($this->isCutscene) $factor = 1024;

                if ($platform == MHT::PLATFORM_WII){
                    $resultFrame['position'] = [
                        $binary->readSwitchedInt16() / $factor,
                        $binary->readSwitchedInt16() / $factor,
                        $binary->readSwitchedInt16() / $factor
                    ];

                }else{

                    $resultFrame['position'] = [
                        $binary->consume(2, NBinary::INT_16) / $factor,
                        $binary->consume(2, NBinary::INT_16) / $factor,
                        $binary->consume(2, NBinary::INT_16) / $factor
                    ];

                }
            }

            $resultFrames['frames'][] = $resultFrame;

            $frames--;
            $index++;
        }

        if ($sequenceLabel == "SEQT") {
            $resultFrames['lastFrameTime'] = ($binary->consume(4, NBinary::FLOAT_32)) * 30;
        }

        return $resultFrames;
    }

    private function prepareData( $finder ){
        $ifp = [];


        $lastFolder = "";
        if ($finder instanceof Finder){
            foreach ($finder as $file) {

                $folder = $file->getPathInfo()->getFilename();
                $lastFolder = $folder;

                if (!isset($ifp[$folder])) $ifp[$folder] = [];

                $ifp[$folder][$file->getFilename()] = \json_decode($file->getContents(), true);
            }

        }else{
            $ifp = $finder;
            $lastFolder = array_keys($ifp)[0];
        }

        if (strpos($lastFolder, "#") !== false){

            uksort($ifp, function($a, $b){
                $_a = explode("#", $a)[0];
                $_b = explode("#", $b)[0];
                if ($_a == $_b) return 0;
                return $_a > $_b ? 1 : -1;
            });

            foreach ($ifp as &$item) {
                uksort($item, function($a, $b){
                    $_a = explode("#", $a)[0];
                    $_b = explode("#", $b)[0];
                    if ($_a == $_b) return 0;
                    return $_a > $_b ? 1 : -1;
                });
            }

        }


        return $ifp;
    }

    /**
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack($records, $game, $platform)
    {

        $records = $this->prepareData($records);

        $binary = new NBinary("ANCT");

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $binary->write(count($records), NBinary::INT_32);

        foreach ($records as $blockName => $animations) {

            $binary->write("BLOC", NBinary::STRING);

            /*
             * Add the length of the Block name and the block name itself
             */
            if (strpos($blockName, "#") !== false){
                $blockName = explode("#", $blockName)[1];
            }

            $blockName .= "\x00";

            $binary->write(strlen($blockName), NBinary::INT_32);
            $binary->write($blockName, NBinary::STRING);

            $binary->concat( $this->packAnimation($animations, $game, $platform) );

        }

        return $binary->binary;

    }

    public function multiplyQuaternions($a, $b) {
        // from http://www.euclideanspace.com/maths/algebra/realNormedAlgebra/quaternions/code/index.htm
        $qax = $a['x'];
        $qay = $a['y'];
        $qaz = $a['z'];
        $qaw = $a['w'];
        $qbx = $b['x'];
        $qby = $b['y'];
        $qbz = $b['z'];
        $qbw = $b['w'];

        return [
            'x' => $qax * $qbw + $qaw * $qbx + $qay * $qbz - $qaz * $qby,
            'y' => $qay * $qbw + $qaw * $qby + $qaz * $qbx - $qax * $qbz,
            'z' => $qaz * $qbw + $qaw * $qbz + $qax * $qby - $qay * $qbx,
            'w' => $qaw * $qbw - $qax * $qbx - $qay * $qby - $qaz * $qbz
        ];
    }

    public function findMaxStartTime($animation){
        $max = 0;
        foreach ($animation['bones'] as $bone) {
            if ($max < $bone['startTime']){
                $max = $bone['startTime'];
            }
        }

        return ceil($max);

    }

    /**
     * @param $animations
     * @param $game
     * @param $platform
     * @return NBinary
     */
    public function packAnimation($animations, $game, $platform)
    {

        $binary = new NBinary();

        if ($platform == MHT::PLATFORM_WII){
            $binary->numericBigEndian = true;
        }

        $binary->write("ANPK", NBinary::STRING);
        $binary->write(count($animations), NBinary::INT_32);

        foreach ($animations as $animationName => $animation) {

            if ($animation == null) continue;

            $portAnimationToManhunt2 = false;
            $portAnimationToManhunt1 = false;

            $binary->write("NAME", NBinary::STRING);

            /*
             * Add the length of the Animation name and the Animation name itself
             */

            if (strpos($animationName, "#") !== false){
                $animationName = explode("#", $animationName)[1];
            }

            $animationName = explode(".json", $animationName)[0];
            $animationName .= "\x00";

            $binary->write(strlen($animationName), NBinary::INT_32);
            $binary->write($animationName, NBinary::STRING);


            if (isset($animation['raw'])){
                $binary->write($animation['raw'], NBinary::HEX);
                continue;
            }

            $binary->write(count($animation['bones']), NBinary::INT_32);


            $chunkBinary = new NBinary();
            $chunkBinary->numericBigEndian = $binary->numericBigEndian;

            $chunkSize = 0;

            if ($animation['frameTimeCount'] === 0){
                $animation['frameTimeCount'] = $this->findMaxStartTime($animation);
            }
            $fixedFrameTimeCount = $animation['frameTimeCount'];

            foreach ($animation['bones'] as $bone) {

                $chunkBinary->write($game == MHT::GAME_MANHUNT ? "SEQU" : "SEQT", NBinary::STRING);

                $boneId = $bone['boneId'];

                $chunkBinary->write($boneId, NBinary::INT_16);
                $chunkBinary->write($bone['frameType'], NBinary::INT_8);
                $chunkBinary->write(count($bone['frames']['frames']), NBinary::INT_16);

                /**
                 * Chunk start
                 */
                $singleChunkBinary = new NBinary();
                $singleChunkBinary->numericBigEndian = $chunkBinary->numericBigEndian;
                $singleChunkBinary->write((int)(($bone['startTime'] / 30) * 2048), NBinary::LITTLE_U_INT_16);

                if ($bone['frameType'] == 3) {
                    if (isset($bone['direction'])){
                        $singleChunkBinary->write($bone['direction'][0] * 2048, NBinary::INT_16);
                        $singleChunkBinary->write($bone['direction'][1] * 2048, NBinary::INT_16);
                        $singleChunkBinary->write($bone['direction'][2] * 2048, NBinary::INT_16);
                        $singleChunkBinary->write($bone['direction'][3] * 2048, NBinary::INT_16);
                    }else{
                        $singleChunkBinary->write($bone['unknown1'], NBinary::HEX);
                        $singleChunkBinary->write($bone['unknown2'], NBinary::HEX);
                        $singleChunkBinary->write($bone['unknown3'], NBinary::HEX);
                        $singleChunkBinary->write($bone['unknown4'], NBinary::HEX);
                    }
                }

                $onlyFirstTime = true;

                foreach ($bone['frames']['frames'] as $index => $frame) {

                    if ($bone['startTime'] == 0) {

                        if ($index == 0 && $bone['frameType'] == 3) {
                        } else {
//                            $singleChunkBinary->write( 12, NBinary::LITTLE_U_INT_16);


                            //.... thats because we skip 2 bytes by the extraction...
                            if ($onlyFirstTime){
                                if($frame['time'] != 0){

                                    $singleChunkBinary->write( ($frame['time'] / 30) * 2048, NBinary::LITTLE_U_INT_16);
                                }
                                $onlyFirstTime = false;
                            }else{
                                $singleChunkBinary->write( ($frame['time'] / 30) * 2048, NBinary::LITTLE_U_INT_16);
                            }

                        }
                    }


                    // we want MH2 but have no lastFrameTime that mean we port a MH1 animation to MH2
                    if (
                        $game == MHT::GAME_MANHUNT_2 &&
                        !isset($bone['frames']['lastFrameTime'])
                    ) {
                        $portAnimationToManhunt2 = true;
                    }

                    if (
                        $game == MHT::GAME_MANHUNT &&
                        isset($bone['frames']['lastFrameTime'])
                    ) {
                        $portAnimationToManhunt1 = true;
                    }

                    if ($bone['frameType'] < 3) {

                        if (
                            ($portAnimationToManhunt2 || $portAnimationToManhunt1) &&
                            $boneId == 1094 //Spine(0)
                        ) {
                            $recalc = $this->multiplyQuaternions([
                                'x' => $frame['quat'][0],
                                'y' => $frame['quat'][1],
                                'z' => $frame['quat'][2],
                                'w' => $frame['quat'][3]
                            ], [
                                'x' => 0.500398,
                                'y' => 0.500001,
                                'z' => -0.4996,
                                'w' => 0.500001
                            ]);

                            $singleChunkBinary->write(intval($recalc['x'] * 4096), NBinary::INT_16);
                            $singleChunkBinary->write(intval($recalc['y'] * 4096), NBinary::INT_16);
                            $singleChunkBinary->write(intval($recalc['z'] * 4096), NBinary::INT_16);
                            $singleChunkBinary->write(intval($recalc['w'] * 4096), NBinary::INT_16);

                        }else{
                            $singleChunkBinary->write(intval($frame['quat'][0] * 4096), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['quat'][1] * 4096), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['quat'][2] * 4096), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['quat'][3] * 4096), NBinary::INT_16);

                        }

                    }

                    if ($bone['frameType'] > 1) {

                        if (
                            //clavicle left
                            $portAnimationToManhunt2 && $boneId == 1003
                        ) {
                            $frame['position'][0] += 0.170165;
                            $frame['position'][1] += -2.68221e-07;
                            $frame['position'][2] += 0.0150331;

                        }else if (
                            //clavicle right
                            $portAnimationToManhunt2 && $boneId == 1057
                        ) {
                            $frame['position'][0] += 0.170165;
                            $frame['position'][1] += -2.75671e-07;
                            $frame['position'][2] += 0.0150331;
                        }

                        if (
                            //clavicle left
                            $portAnimationToManhunt1 && $boneId == 1003
                        ) {
                            $frame['position'][0] += 0.152684;
                            $frame['position'][1] += 0;
                            $frame['position'][2] += 0.00713972;

                        }else if (
                            //clavicle right
                            $portAnimationToManhunt1 && $boneId == 1057
                        ) {
                            $frame['position'][0] += 0.152684;
                            $frame['position'][1] += 0;
                            $frame['position'][2] += 0.00713982;
                        }

                        $factor = 2048;
                        if ($this->isCutscene) $factor = 1024;

                        $singleChunkBinary->write(intval($frame['position'][0] * $factor), NBinary::INT_16);
                        $singleChunkBinary->write(intval($frame['position'][1] * $factor), NBinary::INT_16);
                        $singleChunkBinary->write(intval($frame['position'][2] * $factor), NBinary::INT_16);


                    }
                }

                $chunkSize += $singleChunkBinary->length() * 2;
                $chunkBinary->concat($singleChunkBinary);

                if ($game == MHT::GAME_MANHUNT) {

                }else if ($game == MHT::GAME_MANHUNT_2) {

                    //when we pack a MH1 animation into MH2 , the lastFrameTime is missed
                    if (!isset($bone['frames']['lastFrameTime'])){

                        //port from v0.64
                        if ($fixedFrameTimeCount === 0){
                            echo sprintf("Autocorrect %s, set duration to %s (MH v0.64 port)\n", $animationName, $bone['frames']['lastFrameTime'], $animation['frameTimeCount']);
                            $fixedFrameTimeCount = $bone['frames']['lastFrameTime'];
                        }

                        //todo: sollte das nicht die anzajl der frames durch 30 sein ?!
                        $chunkBinary->write($fixedFrameTimeCount / 30, NBinary::FLOAT_32);
                    }else{

                        if ($bone['frames']['lastFrameTime'] > $fixedFrameTimeCount || $fixedFrameTimeCount === 0  ){
                            echo sprintf("Autocorrect %s, set duration to %s (instead of %s)\n", $animationName, $bone['frames']['lastFrameTime'], $animation['frameTimeCount']);
                            $fixedFrameTimeCount = $bone['frames']['lastFrameTime'];
                        }

                        $chunkBinary->write($bone['frames']['lastFrameTime'] / 30, NBinary::FLOAT_32);
                    }
                }
            }

            $binary->write($chunkSize / 2, NBinary::INT_32);
            $binary->write($fixedFrameTimeCount / 30, NBinary::FLOAT_32);
            $binary->concat($chunkBinary);

            //headerSize
            $binary->write(16, NBinary::INT_32);

            if (!isset($animation['unknown5'])){
                $animation['unknown5'] = "00004040";
            }

            $binary->write($animation['unknown5'], NBinary::HEX);

            //eachEntrySize
            if ($game == MHT::GAME_MANHUNT_2) {
                $binary->write(160, NBinary::INT_32);
            } else {
                $binary->write(64, NBinary::INT_32);
            }

            if ($portAnimationToManhunt2) {
                $binary->write(count($animation['entry']), NBinary::INT_32);

                foreach ($animation['entry'] as $entry) {

                    $binary->write($entry['time'], NBinary::FLOAT_32);
                    $binary->write($entry['unknown'], NBinary::HEX);
                    $binary->write($entry['unknown2'], NBinary::HEX);

//                    if ($game == MHT::GAME_MANHUNT_2) {

//                    $commandName = current(unpack("H*", $entry['CommandName']));
//                    $missed = 128 - strlen($commandName) % 128;

                    $binary->write("", NBinary::STRING);

                    $dummy = \json_decode('{"unknownCommandRemain": "\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000\u0000"}', true);
                    //NOTE: this is just garbage, not needed but included to reach 100%
                    $binary->write($dummy['unknownCommandRemain'], NBinary::STRING);
//                    $binary->write(str_repeat("\x00", $missed / 2), NBinary::BINARY);
//                    }

                    $binary->write($entry['unknown4'], NBinary::HEX);


                    $binary->write($entry['unknown6'], NBinary::FLOAT_32);

//                $particleName = current(unpack("H*", $entry['particleName']));
//                $missed = 16 - strlen($particleName) % 16;

                    $binary->write($entry['particleName'], NBinary::STRING);

                    //NOTE: this is just garbage, not needed but included to reach 100%
                    $binary->write($entry['unknownParticleName'], NBinary::STRING);
//                $binary->write(str_repeat("\x00", $missed / 2), NBinary::BINARY);

                    foreach ($entry['particlePosition'] as $pPos) {
                        $binary->write($pPos, NBinary::FLOAT_32);
                    }

                    $binary->write('00000000000000000000000000000000000000000000000000000000000000000000000000000000', NBinary::HEX);
//                    $binary->write($entry['unknown5'], NBinary::HEX);
                }

                // we can not port the effects right now.... just remove them
//                $binary->write(0, NBinary::INT_32);
            }else if ($portAnimationToManhunt1){
                $binary->write(0, NBinary::INT_32);
            }else{
                if (!isset($animation['entry'])) $animation['entry'] = [];

                $binary->write(count($animation['entry']), NBinary::INT_32);

                foreach ($animation['entry'] as $entry) {

                    $binary->write($entry['time'], NBinary::FLOAT_32);
                    $binary->write($entry['unknown'], NBinary::HEX);
                    $binary->write($entry['unknown2'], NBinary::HEX);

                    if ($game == MHT::GAME_MANHUNT_2) {

//                    $commandName = current(unpack("H*", $entry['CommandName']));
//                    $missed = 128 - strlen($commandName) % 128;

                        $binary->write($entry['CommandName'], NBinary::STRING);

                        //NOTE: this is just garbage, not needed but included to reach 100%
                        $binary->write($entry['unknownCommandRemain'], NBinary::STRING);
//                    $binary->write(str_repeat("\x00", $missed / 2), NBinary::BINARY);
                    }

                    $binary->write($entry['unknown3'], NBinary::HEX);

                    if ($game == MHT::GAME_MANHUNT) {
                        $binary->write($entry['unknown4'], NBinary::HEX);
                    }



                    $binary->write($entry['unknown6'], NBinary::FLOAT_32);

//                $particleName = current(unpack("H*", $entry['particleName']));
//                $missed = 16 - strlen($particleName) % 16;

                    $binary->write($entry['particleName'], NBinary::STRING);

                    //NOTE: this is just garbage, not needed but included to reach 100%
                    $binary->write($entry['unknownParticleName'], NBinary::STRING);
//                $binary->write(str_repeat("\x00", $missed / 2), NBinary::BINARY);

                    foreach ($entry['particlePosition'] as $pPos) {
                        $binary->write($pPos, NBinary::FLOAT_32);
                    }

                    //TODD Cutscene write issue...
//                    if ($platform === MHT::PLATFORM_PC && strlen($entry['unknown5']) != 40){
//                        $entry['unknown5'] = "00000000000000000000000000000000000000000000000000000000000000000000000000000000";
//                    }

                    $binary->write($entry['unknown5'], NBinary::HEX);
                }

            }


        }

        return $binary;
    }
}