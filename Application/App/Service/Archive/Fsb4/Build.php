<?php
namespace App\Service\Archive\Fsb4;

use App\MHT;
use App\Service\AudioCodec\ImaAdPcma;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function json_decode;

class Build {

    private function getSettingsFolder(SplFileInfo $file){

        return substr($file->getPath(), 0, strpos($file->getPath(), '#fsb') + 4) . '/settings';

    }

    public function build( Finder $pathFilename, $platform ){

        $samples = [];


        $headerIni = false;


        foreach ($pathFilename as $file) {

            if ($headerIni === false){
                $headerIni = \json_decode(file_get_contents($this->getSettingsFolder($file) . '/fsb4.json'), true);
            }


            if ($file->getExtension() !== "wav") continue;
            $samples[] = $this->convertWavToFSBSample($file);
        }

        if ($headerIni === false){
            die('fsb4.json not found');
        }

        //sort by the given index
        usort($samples, function ($a, $b) use ($headerIni){
            return array_search($a[2]['name'], $headerIni['orders']) > array_search($b[2]['name'], $headerIni['orders']);
        });

        return $this->createFSB($samples, $headerIni);

    }

    private function createFSB($samples, $headerIni ){
        $header = new NBinary();
        $header->write('FSB4', NBinary::STRING);
        $header->write(count($samples), NBinary::INT_32);
        $header->write(count($samples) * 80, NBinary::INT_32);

        $dataLen = 0;
        foreach ($samples as $sample) {
            $dataLen += $sample[1]->length();
        }
        $header->write($dataLen, NBinary::INT_32);

        $header->write($headerIni['version'], NBinary::LITTLE_U_INT_32);

        //mode
        $header->write(0, NBinary::LITTLE_U_INT_32);
        //zero
        $header->write(str_repeat("\x00", 8), NBinary::BINARY);

        //hash ???
        $header->write($headerIni['hash'], NBinary::HEX);
//        $header->write(str_repeat("\x00", 16), NBinary::BINARY);
        foreach ($samples as $sample) {
            $header->concat($sample[0]);
        }

        foreach ($samples as $sample) {
            $header->concat($sample[1]);
        }

        return $header->binary;
    }


    private function createSampleHeader(NBinary $data, $settings){

        $sample = new NBinary();
        $sample->write(80, NBinary::INT_16);
        $sample->write(str_pad($settings['name'], 30, "\x00"), NBinary::STRING);

        $sample->write($settings['uncompressedSize'], NBinary::LITTLE_U_INT_32);
        $sample->write($data->length(), NBinary::LITTLE_U_INT_32);
        $sample->write($settings['loopStart'], NBinary::LITTLE_U_INT_32);
        $sample->write($settings['loopEnd'], NBinary::LITTLE_U_INT_32);
        $sample->write($settings['mode'], NBinary::LITTLE_U_INT_32);

        $sample->write($settings['defFreq'], NBinary::INT_32);
        $sample->write($settings['defVol'], NBinary::LITTLE_U_INT_16);
        $sample->write($settings['defPan'], NBinary::INT_16);

        $sample->write($settings['defPri'], NBinary::LITTLE_U_INT_16);
        $sample->write($settings['numChannels'], NBinary::LITTLE_U_INT_16);

        $sample->write($settings['minDistance'], NBinary::FLOAT_32);
        $sample->write($settings['maxDistance'], NBinary::FLOAT_32);
        $sample->write($settings['varVol'], NBinary::INT_32);
        $sample->write($settings['varFreq'], NBinary::LITTLE_U_INT_16);
        $sample->write($settings['varPan'], NBinary::INT_16);

        return $sample;
    }

    public function convertWavToFSBSample(SplFileInfo $file ){

        $iniPath = $this->getSettingsFolder($file) . '/' . $file->getFilename() . '.json';
        $ini = \json_decode(file_get_contents($iniPath), true);
        $ini['name'] = $file->getFilename();

        $sample = new NBinary($file->getContents());
        $sample->current = 40; // before PCMA Flag

        $isFact = $sample->consume(4, NBinary::STRING);
        $isAdPcm = false;
        if ($isFact === "fact") $isAdPcm = true;

        if ($isAdPcm){
            $sample->current = 48; // before FACT data size

            $ini['uncompressedSize'] = $sample->consume(4, NBinary::INT_32);

            $sample->current = 56; // before DATA size
            $dataSize = $sample->consume(4, NBinary::INT_32);
            $data = $sample->consume($dataSize, NBinary::BINARY);

            $data = new NBinary($data);
        }else{
            $sample->current = 40; // before DATA size
            $dataSize = $sample->consume(4, NBinary::INT_32);
            $data = $sample->consume($dataSize, NBinary::BINARY);

            $data = new NBinary($data);

            $ini['uncompressedSize'] = $data->length();
            $data = $this->encode($data, $ini['numChannels']);
        }

        $sampleHeader = $this->createSampleHeader($data, $ini);

        return [$sampleHeader, $data, $ini];
    }


    public function encode(NBinary $pcm, $num_channels)
    {

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
}
