<?php
namespace App\Service\Archive\Fsb4;



use App\Service\NBinary;

class Extract {

    public function get( NBinary $binary ){
        $result = [];

        $header = $this->getHeader($binary);
        $sampleHeaders = [];

        $fsbIni = $header;

        $fsbIni['orders'] = [];

        for($i = 0; $i < $header['numSamples']; $i++){

            $sampleHeader = $this->getSampleHeader($binary);
            $sampleHeaders[] = $sampleHeader;

            $fsbSampleIni = $sampleHeader;
            unset($fsbSampleIni['name']);
            unset($fsbSampleIni['lengthCompressedBytes']);
            unset($fsbSampleIni['lengthSamples']);
            unset($fsbSampleIni['size']);
            $result[$sampleHeader['name']. '.json'] = \json_encode($fsbSampleIni, JSON_PRETTY_PRINT);
        }

        foreach ($sampleHeaders as $index => &$sampleHeader) {
            $sampleHeader['data'] = $binary->consume($sampleHeader['lengthCompressedBytes'], NBinary::BINARY);
            $sampleHeader['data'] = $this->convertFSBToWav($sampleHeader);

            $fsbIni['orders'][] = $sampleHeader['name'];
        }

        if ($binary->remain() !== 0){
            die("Export failed, we have remained data!");
        }

        foreach ($sampleHeaders as $wav) {
            $result[$wav['name']] = $wav['data'];
        }

        unset($fsbIni['numSamples']);
        unset($fsbIni['shdrSize']);
        unset($fsbIni['dataSize']);
        $result['fsb4.json'] = \json_encode($fsbIni, JSON_PRETTY_PRINT);

        return $result;
    }

    private function getHeader(NBinary $header ){

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
        if ($mode != 0){
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

    public function getSampleHeader(NBinary $binary){
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

    public function convertFSBToWav( $data ){

//        $adPCMData = $data['data'];
//        unset($data['data']);
        //store the FSB date into a WAV NOTE chunk
//        $data['index'] = $index;
//        $data['fsbVersion'] = $header['fsbVersion'];
//        $note = \json_encode($data);

        $wav = new NBinary();
        $wav->write('RIFF', NBinary::STRING);
        $wav->write($data['lengthCompressedBytes'] + 52, NBinary::INT_32);
        $wav->write('WAVE', NBinary::STRING);

        $wav->write('fmt ', NBinary::STRING);
        $wav->write(20, NBinary::INT_32); // sectionsize
        $wav->write(0x69, NBinary::INT_16); // waveformat
        $wav->write($data['numChannels'], NBinary::INT_16);
        $wav->write($data['defFreq'], NBinary::INT_32); // samplespersecond
        $wav->write($data['defFreq'], NBinary::INT_32); // bytespersecond
        $wav->write(0x24 * $data['numChannels'], NBinary::INT_16); // blockalign
        $wav->write(4, NBinary::INT_16); // bitspersample
        $wav->write(2, NBinary::INT_16); // bit1
        $wav->write(0x64, NBinary::INT_16); // bit2

        $wav->write('fact', NBinary::STRING);
        $wav->write(4, NBinary::INT_32); // factsize
        $wav->write($data['lengthSamples'], NBinary::INT_32); // uncompressedsize

        $wav->write('data', NBinary::STRING); // dataheader
        $wav->write($data['lengthCompressedBytes'], NBinary::INT_32); // datasize
        $wav->write($data['data'], NBinary::BINARY);

        return $wav->binary;
    }

}