<?php

namespace App\Service\Archive;

use App\Service\AudioCodec\AdxPcma;
use App\Service\File;
use App\Service\NBinary;
use Exception;
use Symfony\Component\Finder\Finder;

class Vas extends Archive
{

    public $name = 'Audio Format (Vas)';

    public static $supported = 'vas';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack($pathFilename, $input, $game, $platform)
    {
        return false;
    }


    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return string|null
     */
    public function unpack(NBinary $binary, $game, $platform)
    {

        $fourCC = $binary->consume(4, NBinary::STRING);
        $size = $binary->consume(4, NBinary::INT_32);
        $sampFreq = $binary->consume(2, NBinary::INT_16);
        $unk = $binary->consume(2, NBinary::INT_16);

        $data = $binary->consume($size, NBinary::BINARY);
        $input = new NBinary($data);


        $wav = new NBinary();
        $wav->write('RIFF', NBinary::STRING);
        $wav->write(0, NBinary::INT_32);
        $wav->write('WAVE', NBinary::STRING);

        $wav->write('fmt ', NBinary::STRING);
        $wav->write(16, NBinary::INT_32); // chunksize
        $wav->write(1, NBinary::INT_16); // waveformat
        $wav->write(1, NBinary::INT_16);
        $wav->write($sampFreq, NBinary::INT_32); // samplespersecond
        $wav->write($sampFreq * 2, NBinary::INT_32); // bytespersecond
        $wav->write(2, NBinary::INT_16); // blockalign
        $wav->write(16, NBinary::INT_16); // bitspersample

        $wav->write('data', NBinary::STRING); // dataheader
        $wav->write(0, NBinary::INT_32); // datasize


        $s_1 = 0.0;
        $s_2 = 0.0;

        $f = [];
        $f[] = [0.0, 0.0];
        $f[] = [60.0 / 64.0,  0.0];
        $f[] = [115.0 / 64.0, -52.0 / 64.0];
        $f[] = [98.0 / 64.0, -55.0 / 64.0];
        $f[] = [122.0 / 64.0, -60.0 / 64.0];



        $samples = [];
        while($input->remain() > 0){
            $predict_nr = $input->consume(1, NBinary::INT_8);

            $shift_factor = $predict_nr & 0xf;
            $predict_nr >>= 4;
            $flags = $input->consume(1, NBinary::INT_8);

            if ( $flags == 7 )
                break;
            for ( $i = 0; $i < 28; $i += 2 ) {
                $d = $input->consume(1, NBinary::U_INT_8);
                $s = ( $d & 0xf ) << 12;

                if ( $s & 0x8000 )
                    $s |= 0xffff0000 << 32 >> 32;

                $samples[$i] = floatval( ($s >> $shift_factor) );
                $s = ( $d & 0xf0 ) << 8;
                if ( $s & 0x8000 )
                    $s |= 0xffff0000 << 32 >> 32;
                $samples[$i+1] = (double) ( $s >> $shift_factor  );
            }

            for ( $i = 0; $i < 28; $i++ ) {
                $samples[$i] = $samples[$i] + $s_1 * $f[$predict_nr][0] + $s_2 * $f[$predict_nr][1];
                $s_2 = $s_1;
                $s_1 = $samples[$i];
                $d = (int) ( $samples[$i] + 0.5 );
                $wav->write($d & 0xff, NBinary::INT_8);
                $wav->write($d >> 8, NBinary::INT_8);
            }
        }

        $finalSize = $wav->length();

        $wav->current = 4;
        $wav->overwrite($finalSize - 8, NBinary::INT_32);
        $wav->current = 40;
        $wav->overwrite($finalSize - 44, NBinary::INT_32);

        return $wav->binary;

    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack($pathFilename, $game, $platform)
    {
        return "";
    }


}
