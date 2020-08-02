<?php
namespace App\Service\Archive\Fsb3;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Build {


    public function build( Finder $pathFilename, $platform ){

        $samples = [];

        $globalSampleHeader = false;
        foreach ($pathFilename as $file) {
            if ($file->getFilename() !== "fsb3.json") continue;

            $globalSampleHeader = \json_decode($file->getContents(), true);
        }

        if ($globalSampleHeader == false) die('unable to load fsb3.json!');

        $pathFilename->sort(function (\SplFileInfo $a,\SplFileInfo $b ) use ($globalSampleHeader){

            $pathA = str_replace('\\', '/', $a->getPathname());
            $pathB = str_replace('\\', '/', $b->getPathname());

            $pathA = explode("#fsb/", $pathA)[1];
            $pathB = explode("#fsb/", $pathB)[1];

            return array_search($pathA, $globalSampleHeader['orders']) > array_search($pathB, $globalSampleHeader['orders']);

        });

        foreach ($pathFilename as $file) {
            if ($file->getExtension() !== "wav") continue;
            $samples[] = $this->convertWavToFSBSample($file);
        }

        //sort by the given index
//        usort($samples, function ($a, $b) use ($globalSampleHeader){
//            return array_search($a[2], $globalSampleHeader['orders']) > array_search($b[2], $globalSampleHeader['orders']);
//        });

//        var_dump($samples[0][2], $globalSampleHeader['orders'][0]);exit;

        return $this->createFSB($samples, $globalSampleHeader);

    }

    private function createFSB($samples, $globalSampleHeader ){
        $header = new NBinary();
        $header->write('FSB3', NBinary::STRING);
        $header->write(count($samples), NBinary::INT_32);
        $header->write(((count($samples) - 1) * 8) + 80, NBinary::INT_32);

        $dataLen = 0;
        foreach ($samples as $sample) {
            $dataLen += $sample[1]->length();
        }




        $header->write($dataLen, NBinary::INT_32);

        $header->write($globalSampleHeader['version'], NBinary::LITTLE_U_INT_32);

        //mode
        $header->write($globalSampleHeader['mode'], NBinary::LITTLE_U_INT_32);


        $sampleHeader = $this->createSampleHeader($samples[0][1], $samples[0][3], $globalSampleHeader['header']);
        $header->concat($sampleHeader);
        foreach ($samples as $index =>  $sample) {
            if ($index == 0) continue;
            $header->concat($sample[0]);

        }

        foreach ($samples as $sample) {
            $header->concat($sample[1]);
        }


        return $header->binary;
    }



    public function convertWavToFSBSample(SplFileInfo $file){

        $filePathName = $file->getRelativePath();
        $filePathName .= '/' . $file->getFilename();

        $sample = new NBinary($file->getContents());
        $sample->current = 48; // before FACT data size

        $lengthSamples = $sample->consume(4, NBinary::INT_32);

        $sample->current = 56; // before DATA size
        $lengthCompressedBytes = $sample->consume(4, NBinary::INT_32);
        $data = $sample->consume($lengthCompressedBytes, NBinary::BINARY);
        $data = new NBinary($data);
//
//
//        $sample->current = 40; // before PCMA Flag
//
//        $isFact = $sample->consume(4, NBinary::STRING);
//        $isAdPcm = false;
//        if ($isFact === "fact") $isAdPcm = true;
//
//        if ($isAdPcm){
//            $sample->current = 48; // before FACT data size
//
//            $lengthSamples = $sample->consume(4, NBinary::INT_32);
//
//            $sample->current = 56; // before DATA size
//            $lengthCompressedBytes = $sample->consume(4, NBinary::INT_32);
//            $data = $sample->consume($lengthCompressedBytes, NBinary::BINARY);
//
//            $data = new NBinary($data);
//        }else{
//            $sample->current = 22; // before channel count
//            $numChannels = $sample->consume(2, NBinary::INT_16);
//
//            $sample->current = 40; // before DATA size
//            $lengthCompressedBytes = $sample->consume(4, NBinary::INT_32);
//            $data = $sample->consume($lengthCompressedBytes, NBinary::BINARY);
//
//            $data = new NBinary($data);
//
//            $lengthSamples = $data->length();
//            $data = $this->encode($data, $numChannels);
//        }


        $sampleHeader = $this->createSampleHeaderBasic($lengthSamples, $lengthCompressedBytes);
        return [$sampleHeader, $data, $filePathName, $lengthSamples];
    }


    private function createSampleHeaderBasic($lengthSamples, $lengthCompressedBytes){

        $sample = new NBinary();
        $sample->write($lengthSamples, NBinary::INT_32);
        $sample->write($lengthCompressedBytes, NBinary::INT_32);

        return $sample;
    }


    private function createSampleHeader(NBinary $data, $lengthSamples, $settings){

        $sample = new NBinary();
        $sample->write(80, NBinary::INT_16);
        $sample->write(str_pad($settings['name'], 30, "\x00"), NBinary::STRING);

        $sample->write($lengthSamples, NBinary::LITTLE_U_INT_32);
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

}
