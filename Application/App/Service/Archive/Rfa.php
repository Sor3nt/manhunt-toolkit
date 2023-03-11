<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use PHPUnit\Framework\Exception;
use Symfony\Component\Finder\Finder;

class Rfa extends Archive {

    public $name = 'The Punisher Animations';

    public static $supported = 'rfa';

    public static function canPack( $pathFilename, $input, $game, $platform ){
        return false;
    }

    public function unpack(NBinary $binary, $game, $platform){

        $fourCC = $binary->consume(4, NBinary::STRING);

        if ($fourCC != "VMVF") throw new Exception("Not a Punisher Animation file");

        $fixed = $binary->consume(16, NBinary::HEX);
        if ($fixed != "10000000000000000000000000000000"){
            die("fixed variation");
        }

        $aSize = $binary->consume(4, NBinary::INT_32);

        $count = $binary->consume(4, NBinary::INT_32);
        $zero = $binary->consume(4, NBinary::INT_32);
        if ($zero != 0) die("zero is not zero");

        $bottomTableCount = $binary->consume(4, NBinary::INT_32);

        $offsetUnknown1 = $binary->consume(4, NBinary::INT_32);
        $offsetUnknown2 = $binary->consume(4, NBinary::INT_32);

        $zero2 = $binary->consume(4, NBinary::INT_32);
        if ($zero2 != 0) die("zero2 is not zero");

        $fixed2 = $binary->consume(12, NBinary::HEX);
        if ($fixed2 != "00000080000000000000803f"){
            die("fixed2 variation");
        }

        $unknownFloat1 = $binary->consume(4, NBinary::FLOAT_32);

        $zero3 = $binary->consume(4, NBinary::INT_32);
        if ($zero3 != 0) die("zero3 is not zero");

        $unknownFloat2 = $binary->consume(4, NBinary::FLOAT_32);
        $version = $binary->consume(4, NBinary::INT_32);

        $zero4 = $binary->consume(4, NBinary::INT_32);
        if ($zero4 != 0) die("zero4 is not zero");

        $bottomTableOffset = $binary->consume(4, NBinary::INT_32);
        $bottomTableOffset2 = $binary->consume(4, NBinary::INT_32);

        $offsets = $this->getOffsets( $binary, $count);


        $ifpResults = [
            'frameTimeCount' => 0,
            'bones' => []
        ];

        $frameTimeCount = 0;

        $mh2BoneIdMap = [

            0 => 1000,
            1 => 1045,
            2 => 1094,
            3 => 1023,
            4 => 1077,
            5 => 1095,
            6 => 1002,
            7 => 1056,
            8 => 1001,
            9 => 1003,
            10 => 1057,


            25 => 1005,
            12 => 1019,
            18 => 1020,
            22 => 1021,
            16 => 1024,
            14 => 1039,
            29 => 1059,
            13 => 1073,
            19 => 1074,
            23 => 1075,
            17 => 1078,
            15 => 1093,

        ];

        foreach ($offsets as $index => $offset) {

            if (!isset($mh2BoneIdMap[$index])) continue;

//            echo "Process Index " . $index . "\n";


            $result = [];


            $binary->current = $offset;


            $xyzwCount = $binary->consume(2, NBinary::INT_16);
            $xyzCount = $binary->consume(2, NBinary::INT_16);
            $rotationCount = $binary->consume(2, NBinary::INT_16);

            $aBoolean = $binary->consume(1, NBinary::INT_8);
            $alawys100 = $binary->consume(1, NBinary::INT_8);

            if ($alawys100 != 100){
//                echo "Always100 is not 100 it is currently " . $alawys100 . "\n";
            }

            if ($xyzwCount > 0){

                $result['block1']['xyzw'] = $this->getVector4($binary, $xyzwCount);;
                $result['block1']['rotation'] = $this->getVector4($binary, $rotationCount);
                $result['block1']['times'] = $this->getTimes($binary, $xyzwCount, $version);

                $this->pad($binary);
            }


            if ($xyzCount > 0){

                $alaways1 = $binary->consume(2, NBinary::INT_16);
                if ($alaways1 != 1){
                    echo "alaways1 is " . $alaways1 . "\n";

                }

                $aVal2 = $binary->consume(2, NBinary::INT_16);

                $aVal3 = $binary->consume(2, NBinary::INT_16);

                $unknown2 = $binary->consume(2, NBinary::INT_16);
                $unknown3 = $binary->consume(2, NBinary::INT_16);


//                var_dump($unknown3, "\n");


                if ($version == 3){


                    if ($unknown3 == 1) {
                        echo "CHECK 40...\n";



                        $result['block2']['unknown'] = [];

                        for ($i = 0; $i < 10; $i++) {
                            $result['block2']['unknown'][] = $binary->consume(4, NBinary::INT_32);
                        }

                    }else if ($unknown3 == 10){
                        echo "CHECK 24...\n";

//                        var_dump($aVal2,$aVal3, $unknown, $unknown2, $unknown3, "\n\n");


                        $result['block2']['unknown'] = [];

                        for($i = 0; $i < 6; $i++){
                            $result['block2']['unknown'][] = $binary->consume(4, NBinary::INT_32);
                        }

                    }
                }


                if ($xyzCount > 0){

                    $result['block2']['xyz'] = $this->getVector3($binary, $xyzCount);

                    $this->pad($binary);

                    $result['block2']['times'] = $this->getTimes($binary, $xyzCount, $version);

                    $this->pad($binary);
                }

            }

//
//
//
            if (isset($result['block1'])){

                $ifpResult = [
                    "boneId" => $index,
//                    "boneId" => $mh2BoneIdMap[$index - 1],
                    "frameType" => 1,
                    "startTime" => 0,
                    'frames' => [
                        'frames' => [],
                        "lastFrameTime" => end($result['block1']['times'])
                    ]

                ];

                if (end($result['block1']['times']) > $frameTimeCount){
                    $frameTimeCount = end($result['block1']['times']);
                }

                foreach ($result['block1']['xyzw'] as $xyzwIndex =>  $xyzw) {
                    $ifpResult['frames']['frames'][] = [
                        'time' => $result['block1']['times'][$xyzwIndex],
                        'quat' => [
                            0,
                            0,
                            0,
                            0
                        ]
                    ];
                    break;
                }


                /*

0 1 2 3
1 0 2 3
                 */


                $ifpResults['bones'][] = $ifpResult;
            }


//            if (isset($result['block2'])){
//
//                $ifpResult = [
//                    "boneId" => $index,
////                    "boneId" => $mh2BoneIdMap[$index],
//                    "frameType" => 3,
//                    "startTime" => 0,
//                    'frames' => [
//                        'frames' => [],
//                        "lastFrameTime" => end($result['block2']['times'])
//                    ],
//
//                ];
//
////                if (end($result['block2']['times']) > $frameTimeCount){
////                    $frameTimeCount = end($result['block1']['times']);
////                }
//
//                foreach ($result['block2']['xyz'] as $xyz) {
//                    $ifpResult['frames']['frames'][] = [
//                        'position' => $xyz
//                    ];
//                }
//
//                $ifpResults['bones'][] = $ifpResult;
//            }


//
//            foreach ($result['xyzw'] as $index2 => $xyzw) {
//                $ifpResult['frames'][] = [
//                    'time' => $result['times'][$index2],
//                    'quat' => $xyzw
//                ];
//            }


            if (isset($offsets[ $index + 1 ]) && $binary->current > $offsets[ $index + 1 ]){
                echo "PARSING INVALD!\n";
                echo sprintf("Current: %s\n", $binary->current);
                echo sprintf("Next: %s\n", $offsets[ $index + 1 ]);
                echo sprintf("Index: %s\n", $index);
                exit;
            }

        }

        $ifpResults['frameTimeCount'] = $frameTimeCount;

        return ['anim.json' => $ifpResults];
    }

    private function pad(NBinary $binary){
        $padding = 2 - ($binary->current % 2);
        if ($padding == 2) $padding = 0;

        $binary->current += $padding;

    }

    private function getVector3(NBinary $binary, $count){
        $xyz = [];

        for($i = 0; $i < $count; $i++){

            $xyz[] = [
                $binary->consume(1, NBinary::INT_8) / 127,
                $binary->consume(1, NBinary::INT_8) / 127,
                $binary->consume(1, NBinary::INT_8) / 127
            ];
        }

        return $xyz;

    }

    private function getVector4(NBinary $binary, $count){
        $xyzw = [];

        for($i = 0; $i < $count; $i++){

            $xyzw[] = [
                $binary->consume(1, NBinary::U_INT_8) / 127,
                $binary->consume(1, NBinary::U_INT_8) / 127,
                $binary->consume(1, NBinary::U_INT_8) / 127,
                $binary->consume(1, NBinary::U_INT_8) / 127
            ];
        }

        return $xyzw;

    }

    private function getTimes(NBinary $binary, $count, $version){
        $times = [];
        $lastTime = -1;
        for($i = 0; $i < $count; $i++){
            if ($version == 1){
                $time = $binary->consume(1, NBinary::U_INT_8);
            }else{
                $time = $binary->consume(2, NBinary::U_INT_8);
            }

            if ($time < $lastTime){
                die(sprintf("Time %s is not height as before %s", $time, $lastTime));
            }

            $lastTime = $time;


//            $time = ($time / 127) ;
            $time = ($time );

            $times[] = $time;

        }


        return $times;
    }


    private function getOffsets( NBinary $binary, $count ){

        $offsets = [];

        for($i = 0; $i < $count; $i++){
            $offsets[] = $binary->consume(4, NBinary::LITTLE_U_INT_16);

        }

        return $offsets;
    }


    /**
     * @param $files
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $files, $game, $platform ){
        return false;
    }
}