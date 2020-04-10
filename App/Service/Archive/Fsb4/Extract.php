<?php

namespace App\Service\Archive\Fsb4;


use App\Service\Archive\Wav;
use App\Service\AudioCodec\ImaAdPcma;
use App\Service\NBinary;

class Extract
{

    public function get(NBinary $binary)
    {
        $result = [];

        $header = $this->getHeader($binary);
        $sampleHeaders = [];

        $fsbIni = $header;

        $fsbIni['orders'] = [];

        for ($i = 0; $i < $header['numSamples']; $i++) {

            $sampleHeader = $this->getSampleHeader($binary);
            $sampleHeaders[] = $sampleHeader;

            $fsbSampleIni = $sampleHeader;
            unset($fsbSampleIni['name']);
            unset($fsbSampleIni['lengthCompressedBytes']);
            unset($fsbSampleIni['lengthSamples']);
            unset($fsbSampleIni['size']);
            $result['settings/' . $sampleHeader['name'] . '.json'] = \json_encode($fsbSampleIni, JSON_PRETTY_PRINT);
        }

        foreach ($sampleHeaders as $index => &$sampleHeader) {
            $sampleHeader['data'] = $binary->consume($sampleHeader['lengthCompressedBytes'], NBinary::BINARY);
            $sampleHeader['data'] = $this->generateADPCM($sampleHeader);

            $fsbIni['orders'][] = $sampleHeader['name'];
        }

        if ($binary->remain() !== 0) {
            die("Export failed, we have remained data!");
        }

        foreach ($sampleHeaders as $wav) {
            $result[$wav['name']] = $wav['data'];
        }

        unset($fsbIni['numSamples']);
        unset($fsbIni['shdrSize']);
        unset($fsbIni['dataSize']);
        $result['settings/fsb4.json'] = \json_encode($fsbIni, JSON_PRETTY_PRINT);

        return $result;
    }

    private function getHeader(NBinary $header)
    {

        $header->consume(4, NBinary::STRING);

        /* number of samples in the file */
        $numSamples = $header->consume(4, NBinary::INT_32);

        /* size in bytes of all of the sample headers including extended information */
        $shdrSize = $header->consume(4, NBinary::INT_32);

        /* size in bytes of compressed sample data */
        $dataSize = $header->consume(4, NBinary::INT_32);

        /* extended fsb version */
        $extVersion = $header->consume(4, NBinary::LITTLE_U_INT_32);

        $mode = $header->consume(4, NBinary::LITTLE_U_INT_32);
        if ($mode != 0) {
            die("mode is not 0 !!!");
        }

        $zero = $header->consume(8, NBinary::HEX);
        $hash = $header->consume(16, NBinary::HEX);

        $result = [
            'numSamples' => $numSamples,
            'shdrSize' => $shdrSize,
            'dataSize' => $dataSize,
            'extVersion' => $extVersion,
            'mode' => $mode,
            'hash' => $hash
        ];

        return $result;
    }

    public function getSampleHeader(NBinary $binary)
    {
        $size = $binary->consume(2, NBinary::INT_16);
        $name = $binary->consume(30, NBinary::STRING);

        $lengthSamples = $binary->consume(4, NBinary::LITTLE_U_INT_32);
        $lengthCompressedBytes = $binary->consume(4, NBinary::LITTLE_U_INT_32);
        $loopStart = $binary->consume(4, NBinary::LITTLE_U_INT_32);
        $loopEnd = $binary->consume(4, NBinary::LITTLE_U_INT_32);
        $mode = $binary->consume(4, NBinary::LITTLE_U_INT_32);

        $defFreq = $binary->consume(4, NBinary::INT_32);
        $defVol = $binary->consume(2, NBinary::LITTLE_U_INT_16);
        $defPan = $binary->consume(2, NBinary::INT_16);

        $defPri = $binary->consume(2, NBinary::LITTLE_U_INT_16);
        $numChannels = $binary->consume(2, NBinary::LITTLE_U_INT_16);

        $minDistance = $binary->consume(4, NBinary::FLOAT_32);
        $maxDistance = $binary->consume(4, NBinary::FLOAT_32);
        $varFreq = $binary->consume(4, NBinary::INT_32);

        $varVol = $binary->consume(2, NBinary::LITTLE_U_INT_16);
        $varpan = $binary->consume(2, NBinary::INT_16);


        return [
            'size' => $size,
            'name' => $name,
            'lengthSamples' => $lengthSamples,
            'lengthCompressedBytes' => $lengthCompressedBytes,
            'loopStart' => $loopStart,
            'loopEnd' => $loopEnd,
            'mode' => $mode,
            'defFreq' => $defFreq,
            'defVol' => $defVol,
            'defPan' => $defPan,
            'defPri' => $defPri,
            'numChannels' => $numChannels,
            'minDistance' => $minDistance,
            'maxDistance' => $maxDistance,
            'varFreq' => $varFreq,
            'varVol' => $varVol,
            'varpan' => $varpan
        ];
    }


    public function generateADPCM($data)
    {

        $wav = new Wav();
        return $wav->generateADPCM(new NBinary($data['data']), $data['lengthSamples'], $data['numChannels'], $data['defFreq']);

    }



}