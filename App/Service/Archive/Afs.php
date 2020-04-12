<?php
namespace App\Service\Archive;

use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Afs extends Archive {

    public $name = 'Audio File (AFS)';

    public static $supported = 'afs';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){
        return false;
    }


    private function getBlock(NBinary $binary){


        $offset = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        $current = $binary->current;

        $binary->current = $offset;
        $data = $binary->consume($size, NBinary::BINARY);
        $binary->current = $current;

        return $data;

    }


    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $fourCC = $binary->consume(4, NBinary::STRING);
        $count = $binary->consume(4, NBinary::INT_32);

        $hashNames = $this->getBlock($binary);

        for($i = 1; $i < $count; $i++){
            $audio = new NBinary($this->getBlock($binary));
            $audio->numericBigEndian = true;

            $fourCC = $audio->consume(2, NBinary::HEX);

            $copyrightOffset = $audio->consume(2, NBinary::INT_16);
            /**
             * The "Encoding Type" field should contain one of:

                0x02 for ADX with pre-set prediction coefficients
                0x03 for Standard ADX
                0x04 for ADX with an exponential scale
                0x10 or 0x11 for AHX
             */
            $encodingType = $audio->consume(1, NBinary::INT_8);
            if ($encodingType !== 0x03) die("Encoding not supported");

            $blockSize = $audio->consume(1, NBinary::INT_8);
            $sampleBitdepth = $audio->consume(1, NBinary::INT_8);
            $channelCount = $audio->consume(1, NBinary::INT_8);

            $sampleRate = $audio->consume(4, NBinary::INT_32);
            $totalSamples = $audio->consume(4, NBinary::INT_32);

            $highpassFrequency =  $audio->consume(2, NBinary::INT_16);
            $version = $audio->consume(1, NBinary::INT_8);
            $flags = $audio->consume(1, NBinary::INT_8);

            $loopEnabled = $audio->consume(4, NBinary::INT_32);
            if ($loopEnabled !== 0) die("loop not implemented");


            $audio->current = $copyrightOffset + 4;
            $audioData =  $audio->consume($audio->remain(), NBinary::BINARY);





            $a = sqrt(2.0) - cos(2.0 * 3.14159265358979323846264338327950288 * ($highpassFrequency / $sampleRate));
            $b = sqrt(2.0) - 1.0;
            $c = ($a - sqrt(($a + $b) * ($a - $b))) / $b;

            // double coefficient[2];
            $coefficient = [
                $c * 2.0,
                -($c * $c)
            ];
var_dump($coefficient);exit;






            $wav = new Wav();

            $out = $wav->generatePCM(new NBinary($audioData), $channelCount, $sampleRate);
//            $out = $wav->generateADPCM(new NBinary($audioData), $uncompressedLength, 1, 44100);

//            var_dump(\mb_strlen($audioData, '8bit'));
//            var_dump(\mb_strlen($out, '8bit'));
//            var_dump($uncompressedLength);
//            exit;
            if ($i == 120){
                file_put_contents("test.wav", $out);exit;
//                var_dump($audioData->hex);
//                exit;
//
            }

        }
var_dump("end");exit;

//        return $entries;

    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform){
    }




}
