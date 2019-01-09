<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Ifp extends Archive
{
    public $name = 'Animations';

    public static $supported = 'ifp';

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
            if (strpos($relPath, "#") == false) return false;

            $category = explode("#", $relPath)[1];

            switch (strtolower($category)){

                case 'bookends':
                case 'legion':
                case 'playeranims':
                case 'openables':
                case 'genhunteranims':

                    return true;

                    break;

                default:
            }
        }

        return false;
    }

    public function unpack(NBinary $binary, $game, $platform)
    {

        if ($game == MHT::GAME_AUTO) $game = MHT::GAME_MANHUNT_2;
        if ($platform == MHT::PLATFORM_AUTO) $platform = MHT::PLATFORM_PC;

        /**
         * ROOT (ANCT)
         */
        $headerType = $binary->consume(4, NBinary::STRING);
        $numBlock = $binary->consume(4, NBinary::INT_32);

        if ($numBlock > 10000) {
            $game = MHT::GAME_MANHUNT_2;
            $platform = MHT::PLATFORM_WII;

            $binary->current -= 4;
            $binary->numericBigEndian = true;

            $numBlock = $binary->consume(4, NBinary::INT_32);
        }

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
            $path = $count . "#" . $blockName;
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

                $numberOfBones = substr($numberOfBones, 0, 2);
                if (strlen($numberOfBones) == 2) {
                    $binary->consume(4, NBinary::BINARY);
                    $numberOfBones = $binary->unpack(hex2bin($numberOfBones), NBinary::INT_8) * -1;
                } else {

                    die("PS2 error");
                }

            } else {
                $numberOfBones = $binary->consume(4, NBinary::INT_32);
            }

            $chunkSize = $binary->consume(4, NBinary::INT_32);
            $frameTimeCount = $binary->consume(4, NBinary::FLOAT_32);

            if ($game == MHT::GAME_MANHUNT_2 && $platform == MHT::PLATFORM_PS2) {

                $frameTimeCount = (string)$frameTimeCount;
                if (strlen($frameTimeCount) > 15) {
                    $frameTimeCount = (float)substr($frameTimeCount, 0, -5);
                } else {

                    die("PS2 error");
                }
            }

            $resultAnimation = [
                'frameTimeCount' => $frameTimeCount * 30,
            ];

            /**
             * Sequences
             */
            $bones = $this->extractBones($numberOfBones, $binary, $chunkSize, $game, $platform);

            $resultAnimation['bones'] = $bones;

            //headerSize
            $binary->consume(4, NBinary::INT_32);

            //pecTime
            $unknown5 = $binary->consume(4, NBinary::HEX);

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
                        'unknown5' => $binary->consume(40, NBinary::HEX)
                    ];


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


            $results[ $count . "#" . $animationName ] = $resultAnimation;
//            file_put_contents($outputTo . $count . "#" . $animationName . ".json", \json_encode($resultAnimation, JSON_PRETTY_PRINT));

            $animationCount--;
            $count++;
        }

        return $results;
    }

    private function extractBones($numberOfBones, NBinary $binary, $chunkSize = null, $game, $platform)
    {

        $bones = [];

        if ($game == MHT::GAME_MANHUNT_2 && $platform == MHT::PLATFORM_PS2) {

            $zlibData = $binary->consume($chunkSize, NBinary::BINARY);

            $zlibData = zlib_decode($zlibData);

            $binary = new NBinary($zlibData);

            //unknown ps2 values
            $binary->consume(4, NBinary::HEX);
            $binary->consume(4, NBinary::HEX);
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

            $startTime = $binary->consume(2, NBinary::LITTLE_U_INT_16);
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

                $resultBone['unknown1'] = $binary->consume(2, NBinary::HEX);
                $resultBone['unknown2'] = $binary->consume(2, NBinary::HEX);
                $resultBone['unknown3'] = $binary->consume(2, NBinary::HEX);
                $resultBone['unknown4'] = $binary->consume(2, NBinary::HEX);
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
                $platform
            );

            $bones[] = $resultBone;

            $numberOfBones--;
        }

        return $bones;
    }

    private function extractFrames($startTime, $frames, $frameType, NBinary $binary, $game, $platform)
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
                    $time = $binary->consume(2, NBinary::LITTLE_U_INT_16);

                    $resultFrame['time'] = $time / 2048 * 30;
                    $curTime = $resultFrame['time'];
                }

                $frameTime += $curTime;
            }else{


                //todo ....
                if ($startTime < 1) $startTime = 1;

                $frameTime = ($index/2048*30)+$startTime-1;
            }

            if (isset($resultFrame['time'])){

//                var_dump($frameTime, $resultFrame['time'], "\n");
            }



//
//            var_dump($frameTime / 30);



//            if ($startTime == 0) {
//
//                // first frame == starTime
//                if ($index == 0 && $frameType < 3) {
//                    $time = $startTime;
//                } else {
//                    $time = $binary->consume(2, NBinary::LITTLE_U_INT_16);
//                }
//
//                $resultFrame['time'] = ($time / 2048) * 30;
//            }

            if ($frameType < 3) {

                $resultFrame['quat'] = [
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                ];

            }

            if ($frameType > 1) {

                $resultFrame['position'] = [
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                    $binary->consume(2, NBinary::INT_16) / 2048,
                ];
            }

            $resultFrames['frames'][] = $resultFrame;

            $frames--;
            $index++;
        }

        if ($game == MHT::GAME_MANHUNT_2) {
            $resultFrames['lastFrameTime'] = ($binary->consume(4, NBinary::FLOAT_32)) * 30;
        }

        return $resultFrames;
    }

    private function prepareData( Finder $finder ){
        $ifp = [];

        foreach ($finder as $file) {

            $folder = $file->getPathInfo()->getFilename();

            if (!isset($ifp[$folder])) $ifp[$folder] = [];

            $ifp[$folder][$file->getFilename()] = \json_decode($file->getContents(), true);
        }

        uksort($ifp, function($a, $b){
            return explode("#", $a)[0] > explode("#", $b)[0];
        });

        foreach ($ifp as &$item) {
            uksort($item, function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });
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
            $blockName = explode("#", $blockName)[1] . "\x00";

            $binary->write(strlen($blockName), NBinary::INT_32);
            $binary->write($blockName, NBinary::STRING);

            $binary->concat( $this->packAnimation($animations, $game, $platform) );

        }

        return $binary->binary;

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

        $portAnimationToManhunt2 = false;

        foreach ($animations as $animationName => $animation) {

            $binary->write("NAME", NBinary::STRING);

            /*
             * Add the length of the Animation name and the Animation name itself
             */
            $animationName = explode("#", $animationName)[1];
            $animationName = explode(".json", $animationName)[0];
            $animationName .= "\x00";

            $binary->write(strlen($animationName), NBinary::INT_32);
            $binary->write($animationName, NBinary::STRING);

            $binary->write(count($animation['bones']), NBinary::INT_32);


            $chunkBinary = new NBinary();
            $chunkBinary->numericBigEndian = $binary->numericBigEndian;

            $chunkSize = 0;

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
                    $singleChunkBinary->write($bone['unknown1'], NBinary::HEX);
                    $singleChunkBinary->write($bone['unknown2'], NBinary::HEX);
                    $singleChunkBinary->write($bone['unknown3'], NBinary::HEX);
                    $singleChunkBinary->write($bone['unknown4'], NBinary::HEX);
//                }else if($bone['frameType'] < 3 && $bone['startTime'] == 0){
//                    $singleChunkBinary->write("FFFF", NBinary::HEX);

                }


//                if ($bone['frameType'] > 2) {
//                    if ($bone['startTime'] > 0) {
//                        $singleChunkBinary->write($bone['unknown1'], NBinary::HEX);
//                    }
//
//                    $singleChunkBinary->write($bone['unknown2'], NBinary::HEX);
//                    $singleChunkBinary->write($bone['unknown3'], NBinary::HEX);
//                    $singleChunkBinary->write($bone['unknown4'], NBinary::HEX);
//                }


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

//                    if ($bone['startTime'] == 0) {
//
//                        if ($index == 0 && $bone['frameType'] < 3) {
//                        } else {
//                            $singleChunkBinary->write( ($frame['time'] / 30) * 2048, NBinary::LITTLE_U_INT_16);
//                        }
//                    }


                    // we want MH2 but have no lastFrameTime that mean we port a MH1 animation to MH2
                    if (
                        $game == MHT::GAME_MANHUNT_2 &&
                        !isset($bone['frames']['lastFrameTime'])
                    ) {
                        $portAnimationToManhunt2 = true;
                    }

                    if ($bone['frameType'] < 3) {

                        if (
                            $portAnimationToManhunt2 &&
                            $boneId == 1094
                        ) {
                            //Spine(0) is in someway twisted, for now just use another mh2 spine values
                            $singleChunkBinary->write(intval(0.99365234375 * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval(0.8232421875 * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval(-1.01513671875 * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval(1.1416015625 * 2048), NBinary::INT_16);

                        }else{
                            $singleChunkBinary->write(intval($frame['quat'][0] * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['quat'][1] * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['quat'][2] * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['quat'][3] * 2048), NBinary::INT_16);

                        }

                    }

                    if ($bone['frameType'] > 1) {

                        // we want MH2 but have no lastFrameTime that mean we port a MH1 animation to MH2
                        if (
                            $portAnimationToManhunt2 &&
                            ($boneId == 1057 || $boneId == 1003)
                        ){
                            //clavicle right and clavicle left, cash is heigher as daniel so fix the first value
                            $singleChunkBinary->write(intval(0.1318359375 * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['position'][1] * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['position'][2] * 2048), NBinary::INT_16);

                        }else{
                            $singleChunkBinary->write(intval($frame['position'][0] * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['position'][1] * 2048), NBinary::INT_16);
                            $singleChunkBinary->write(intval($frame['position'][2] * 2048), NBinary::INT_16);
                        }


                    }
                }

                $chunkSize += $singleChunkBinary->length() * 2;
                $chunkBinary->concat($singleChunkBinary);

                if ($game == MHT::GAME_MANHUNT_2) {

                    //when we pack a MH1 animation into MH2 , the lastFrameTime is missed
                    //use frameTimeCount instead
                    if (!isset($bone['frames']['lastFrameTime'])){

                        $chunkBinary->write($animation['frameTimeCount'] / 30, NBinary::FLOAT_32);
                    }else{
                        $chunkBinary->write($bone['frames']['lastFrameTime'] / 30, NBinary::FLOAT_32);
                    }
                }
            }

            $binary->write($chunkSize / 2, NBinary::INT_32);
            $binary->write($animation['frameTimeCount'] / 30, NBinary::FLOAT_32);
            $binary->concat($chunkBinary);

            //headerSize
            $binary->write(16, NBinary::INT_32);
            $binary->write($animation['unknown5'], NBinary::HEX);

            //eachEntrySize
            if ($game == MHT::GAME_MANHUNT_2) {
                $binary->write(160, NBinary::INT_32);
            } else {
                $binary->write(64, NBinary::INT_32);
            }

            if ($portAnimationToManhunt2){

                // we can not port the effects right now.... just remove them
                $binary->write(0, NBinary::INT_32);

            }else{
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

                    $binary->write($entry['unknown5'], NBinary::HEX);
                }

            }


        }

        return $binary;
    }
}