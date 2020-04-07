<?php
namespace App\Service\Archive\Fsb4;

use App\MHT;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function json_decode;

class Build {

    public function build( Finder $pathFilename, $platform ){

        $samples = [];


        $headerIni = false;

        foreach ($pathFilename as $file) {

            if ($headerIni === false){
                $settingName = str_replace($file->getFilename(), "fsb4.json", $file->getRealPath());
                $headerIni = \json_decode(file_get_contents($settingName), true);
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

        $header->write($headerIni['extVersion'], NBinary::LITTLE_U_INT_32);

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
        $sample->write($settings['varpan'], NBinary::INT_16);

        return $sample;
    }

    public function convertWavToFSBSample(SplFileInfo $file ){

        $ini = \json_decode(file_get_contents($file->getRealPath() . '.json'), true);
        $ini['name'] = $file->getFilename();


        $sample = new NBinary($file->getContents());
        $sample->current = 48; // before FACT data size

        $ini['uncompressedSize'] = $sample->consume(4, NBinary::INT_32);
//var_dump($uncompressedsize);exit;

        $sample->current = 56; // before DATA size
        $dataSize = $sample->consume(4, NBinary::INT_32);
        $data = $sample->consume($dataSize, NBinary::BINARY);
        $data = new NBinary($data);


        $sampleHeader = $this->createSampleHeader($data, $ini);

        return [$sampleHeader, $data, $ini];
    }
}
