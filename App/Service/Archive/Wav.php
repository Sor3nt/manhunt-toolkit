<?php
namespace App\Service\Archive;

use App\Service\AudioCodec\ImaAdPcma;
use App\Service\CompilerV2\Manhunt2;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Wav extends Archive {

    public $name = 'Wav File (ADPCM/PCM)';

    public static $supported = 'wav';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $binary, $game, $platform ){

        if (!$binary instanceof NBinary) return false;

        if ($binary->consume(4, NBinary::STRING) !== "WAVE") return false;

        $binary->current = 40; // before PCMA Flag

        $isFact = $binary->consume(4, NBinary::STRING);
        $isAdPcm = false;

        //yea i know this check is not an adpcm check... who cares ^^
        if ($isFact === "fact") $isAdPcm = true;

        if ($isAdPcm == false) return true;

        return false;
    }




    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $binary->current = 40; // before PCMA Flag

        $isFact = $binary->consume(4, NBinary::STRING);
        $isAdPcm = false;

        //yea i know this check is not an adpcm check... who cares ^^
        if ($isFact === "fact") $isAdPcm = true;

        $binary->current = 22; // before channel count

        $ini = [];
        $ini['numChannels'] = $binary->consume(2, NBinary::INT_16);
        $ini['defFreq'] = $binary->consume(4, NBinary::INT_32);

        if ($isAdPcm){

            echo "Converting ADPCM to PCM...\n";

            $binary->current = 48; // before FACT data size

            $ini['uncompressedSize'] = $binary->consume(4, NBinary::INT_32);

            $binary->current = 56; // before DATA size
            $dataSize = $binary->consume(4, NBinary::INT_32);
            $data = $binary->consume($dataSize, NBinary::BINARY);

            $data = new NBinary($data);
            $data = $this->decode($data, $ini['numChannels']);
            return $this->generatePCM($data, $ini['numChannels'], $ini['defFreq']);
        }else{
            die("Wav is already decoded.");

        }

    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $binary, $game, $platform){

        $binary->current = 40; // before PCMA Flag

        $isFact = $binary->consume(4, NBinary::STRING);
        $isAdPcm = false;

        //yea i know this check is not an adpcm check... who cares ^^
        if ($isFact === "fact") $isAdPcm = true;

        $binary->current = 22; // before channel count

        $ini = [];
        $ini['numChannels'] = $binary->consume(2, NBinary::INT_16);
        $ini['defFreq'] = $binary->consume(4, NBinary::INT_32);

        if ($isAdPcm){
            die("Wav is already encoded with ADPCM.");
        }else{

            $binary->current = 40; // before DATA size
            $dataSize = $binary->consume(4, NBinary::INT_32);
            $data = $binary->consume($dataSize, NBinary::BINARY);

            $data = new NBinary($data);

            $ini['uncompressedSize'] = $data->length();
            $data = $this->encode($data, $ini['numChannels']);

            return $this->generateADPCM($data, $ini['uncompressedSize'], $ini['numChannels'], $ini['defFreq']);
        }

    }



    public function decode(NBinary $adpcm, $num_channels)
    {
        //c style calc, round bracket wrapped values
        $calcA = (int) (0x24 * $num_channels);
        $calcB = (int) ($adpcm->length() / $calcA);
        $num_samples_per_channel = (int) (0x41 * $calcB);

        $tmpPcm = [];
        $pcmIndex = 0;

        $Converters = [];
        for ($i = 0; $i < $num_channels; $i++) {
            $Converters[] = new ImaAdPcma();
        }

        for ($i = 0; $i < $num_samples_per_channel; $i += 65) {

            for ($c = 0; $c < $num_channels; $c++) {

                $Converters[$c]->predictedValue = $adpcm->consume(2, NBinary::INT_16);
                $Converters[$c]->stepIndex = $adpcm->consume(1, NBinary::U_INT_8);
                $adpcm->current += 1;

                $tmpPcm[] = $Converters[$c]->predictedValue;
            }

            // 4 bytes per channel
            for ($j = 0; $j < 8; $j++) {
                for ($c = 0; $c < $num_channels; $c++) {

                    $temp_buf = $Converters[$c]->decode($adpcm);
                    $pcmIndex += 4;
                    for ($k = 0; $k < 8; $k++) {

                        $tmpPcm[$pcmIndex + ($k * $num_channels + $c)] = $temp_buf[$k];
                    }
                }

                $pcmIndex += 8 * $num_channels;
            }
        }

        $pcm = new NBinary();
        foreach ($tmpPcm as $item) {
            $pcm->write($item, NBinary::INT_16);
        }

        return $pcm;
    }

    public function encode(NBinary $pcm, $num_channels)
    {

//        int iNumOfSamples = $pcm->length() / (2 * $num_channels);

        //c style calc, round bracket wrapped values
        $calcA = (int) (2 * $num_channels);
        $num_samples_per_channel = (int) ($pcm->length() / $calcA);

        $tmpAdPcm = [];
        $adPcmIndex = 0;

        $adPcm = new NBinary();

        $Converters = [];
        for ($i = 0; $i < $num_channels; $i++) {
            $converter = new ImaAdPcma();
            $converter->stepIndex = 0;
            $Converters[] = $converter;
        }

        for ($i = 0; $i < $num_samples_per_channel; $i += 65) {

            for ($c = 0; $c < $num_channels; $c++) {
                $Converters[$c]->predictedValue = $pcm->consume(2, NBinary::INT_16);

                $adPcm->write($Converters[$c]->predictedValue, NBinary::INT_16);
                $adPcm->write($Converters[$c]->stepIndex, NBinary::INT_8);


                $adPcm->write(0, NBinary::INT_8);

                $adPcmIndex += 2;
            }

            // 4 bytes per channel
            for ($j = 0; $j < 8; $j++) {
                for ($c = 0; $c < $num_channels; $c++) {

                    $tmp = [];
                    for ($k = 0; $k < 8; $k++) {

                        $pcm->current = $adPcmIndex + ($k * $num_channels + $c);
                        $tmp[] = $pcm->consume(2, NBinary::INT_16);
                        $adPcmIndex++;

                    }

                    $Converters[$c]->encode($adPcm, 0, $tmp, 8 * 2);

                }

                $adPcmIndex += 8 * $num_channels;
            }

        }

        return $adPcm;
    }



    public function generatePCM(NBinary $data, $numChannels, $defFreq)
    {

        $wav = new NBinary();
        $wav->write('RIFF', NBinary::STRING);
        $wav->write($data->length() + 36, NBinary::INT_32);
        $wav->write('WAVE', NBinary::STRING);

        $wav->write('fmt ', NBinary::STRING);
        $wav->write(16, NBinary::INT_32); // sectionsize
        $wav->write(1, NBinary::INT_16); // waveformat
        $wav->write($numChannels, NBinary::INT_16);
        $wav->write($defFreq, NBinary::INT_32); // samplespersecond
        $wav->write($defFreq * 2, NBinary::INT_32); // bytespersecond
        $wav->write(2, NBinary::INT_16); // blockalign
        $wav->write(16, NBinary::INT_16); // bitspersample

        $wav->write('data', NBinary::STRING); // dataheader
        $wav->write($data->length(), NBinary::INT_32); // datasize
        $wav->write($data->binary, NBinary::BINARY);

        return $wav->binary;
    }

    public function generateADPCM(Nbinary $data, $lengthUncompressedBytes, $numChannels, $defFreq)
    {

        $wav = new NBinary();
        $wav->write('RIFF', NBinary::STRING);
        $wav->write($data->length() + 52, NBinary::INT_32);
        $wav->write('WAVE', NBinary::STRING);

        $wav->write('fmt ', NBinary::STRING);
        $wav->write(20, NBinary::INT_32); // sectionsize
        $wav->write(0x69, NBinary::INT_16); // waveformat
        $wav->write($numChannels, NBinary::INT_16);
        $wav->write($defFreq, NBinary::INT_32); // samplespersecond
        $wav->write($defFreq, NBinary::INT_32); // bytespersecond
        $wav->write(0x24 * $numChannels, NBinary::INT_16); // blockalign
        $wav->write(4, NBinary::INT_16); // bitspersample


        $wav->write(2, NBinary::INT_16); // adpcm bit
        $wav->write(0x64, NBinary::INT_16); // adpcm bit

        $wav->write('fact', NBinary::STRING);
        $wav->write(4, NBinary::INT_32); // factsize
        $wav->write($lengthUncompressedBytes, NBinary::INT_32); // uncompressedsize

        $wav->write('data', NBinary::STRING); // dataheader
        $wav->write($data->length(), NBinary::INT_32); // datasize
        $wav->write($data->binary, NBinary::BINARY);

        return $wav->binary;
    }


}
