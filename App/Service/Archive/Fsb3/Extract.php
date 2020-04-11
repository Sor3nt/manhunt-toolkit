<?php

namespace App\Service\Archive\Fsb3;


use App\Service\Archive\Wav;
use App\Service\NBinary;

class Extract
{
    
    public $modes = [
        "FSOUND_LOOP_OFF" => 0x00000001, /* For non looping samples. */
        "FSOUND_LOOP_NORMAL" => 0x00000002, /* For forward looping samples. */
        "FSOUND_LOOP_BIDI" => 0x00000004, /* For bidirectional looping samples. (no effect if in hardware). */
        "FSOUND_8BITS" => 0x00000008, /* For 8 bit samples. */
        "FSOUND_16BITS" => 0x00000010, /* For 16 bit samples. */
        "FSOUND_MONO" => 0x00000020, /* For mono samples. */
        "FSOUND_STEREO" => 0x00000040, /* For stereo samples. */
        "FSOUND_UNSIGNED" => 0x00000080, /* For user created source data containing unsigned samples. */
        "FSOUND_SIGNED" => 0x00000100, /* For user created source data containing signed data. */
        "FSOUND_DELTA" => 0x00000200, /* For user created source data stored as delta values. */
        "FSOUND_IT214" => 0x00000400, /* For user created source data stored using IT214 compression. */
        "FSOUND_IT215" => 0x00000800, /* For user created source data stored using IT215 compression. */
        "FSOUND_HW3D" => 0x00001000, /* Attempts to make samples use 3d hardware acceleration. (if the card supports it) */
        "FSOUND_2D" => 0x00002000, /* Tells software (not hardware) based sample not to be included in 3d processing. */
        "FSOUND_STREAMABLE" => 0x00004000, /* For a streamimg sound where you feed the data to it. */
        "FSOUND_LOADMEMORY" => 0x00008000, /*"name" will be interpreted as a pointer to data for streaming and samples. */
        "FSOUND_LOADRAW" => 0x00010000, /* Will ignore file format and treat as raw pcm. */
        "FSOUND_MPEGACCURATE" => 0x00020000, /* For FSOUND_Stream_Open - for accurate FSOUND_Stream_GetLengthMs/FSOUND_Stream_SetTime. WARNING, see FSOUND_Stream_Open for inital opening time performance issues. */
        "FSOUND_FORCEMONO" => 0x00040000, /* For forcing stereo streams and samples to be mono - needed if using FSOUND_HW3D and stereo data - incurs a small speed hit for streams */
        "FSOUND_HW2D" => 0x00080000, /* 2D hardware sounds. allows hardware specific effects */
        "FSOUND_ENABLEFX" => 0x00100000, /* Allows DX8 FX to be played back on a sound. Requires DirectX 8 - Note these sounds cannot be played more than once, be 8 bit, be less than a certain size, or have a changing frequency */
        "FSOUND_MPEGHALFRATE" => 0x00200000, /* For FMODCE only - decodes mpeg streams using a lower quality decode, but faster execution */
        "FSOUND_IMAADPCM" => 0x00400000, /* Contents are stored compressed as IMA ADPCM */
        "FSOUND_VAG" => 0x00800000, /* For PS2 only - Contents are compressed as Sony VAG format */
        "FSOUND_XMA" => 0x01000000,
        "FSOUND_GCADPCM" => 0x02000000, /* For Gamecube only - Contents are compressed as Gamecube DSP-ADPCM format */
        "FSOUND_MULTICHANNEL" => 0x04000000, /* For PS2 and Gamecube only - Contents are interleaved into a multi-channel (more than stereo) format */
        "FSOUND_USECORE0" => 0x08000000, /* For PS2 only - Sample/Stream is forced to use hardware voices 00-23 */
        "FSOUND_USECORE1" => 0x10000000, /* For PS2 only - Sample/Stream is forced to use hardware voices 24-47 */
        "FSOUND_LOADMEMORYIOP" => 0x20000000, /* For PS2 only -"name" will be interpreted as a pointer to data for streaming and samples. The address provided will be an IOP address */
        "FSOUND_IGNORETAGS" => 0x40000000, /* Skips id3v2 etc tag checks when opening a stream, to reduce seek/read overhead when opening files (helps with CD performance) */
        "FSOUND_STREAM_NET" => 0x80000000, /* Specifies an internet stream */
//        "FSOUND_NORMAL" => (0x00000010 | 0x00000100 | 0x00000020)
    ];

    public function get(NBinary $binary)
    {
        $result = [];

        $header = $this->getHeader($binary);

        $fsbIni = $header;

        $fsbIni['orders'] = [];

        $dataOffset = $binary->current + $header['shdrSize'];

        //sizeof 80
        $globalSampleHeader = $this->getSampleHeader($binary);
        $sampleHeaderBasic = $this->getSampleHeaderBasic($binary, $header['numSamples']);


        $globalSampleHeader['moreSize'] = $globalSampleHeader['size'] - 80;
        $globalSampleHeader['moresize_dump'] = "";

        if ($globalSampleHeader['moreSize'] > 0){
            $globalSampleHeader['moresize_dump'] = $binary->consume($globalSampleHeader['moreSize'], NBinary::BINARY);
        }

        array_unshift($sampleHeaderBasic, $globalSampleHeader);

        $binary->current = $dataOffset;
        $files = [];
        foreach ($sampleHeaderBasic as &$sampleHeader) {
            $files[] = $binary->consume($sampleHeader['lengthCompressedBytes'], NBinary::BINARY);

        }
//        var_dump($binary->current);exit;

        $waves = [];
        foreach ($files as $index => &$file) {

            $basic = $globalSampleHeader;
            $basic['lengthCompressedBytes'] = $sampleHeaderBasic[$index]['lengthCompressedBytes'];
            $basic['lengthSamples'] = $sampleHeaderBasic[$index]['lengthSamples'];
//            $basic['moreSize'] = $sampleHeaderBasic[$index]['moreSize'];
//            $basic['moresize_dump'] = $sampleHeaderBasic[$index]['moresize_dump'];
            $basic['data'] = $file;


            if (in_array("FSOUND_IMAADPCM", $globalSampleHeader['modes'])){
                 $waves[] = $this->generateADPCM($basic);

            //Gamecube/Wii
            }else if (in_array("FSOUND_GCADPCM", $globalSampleHeader['modes'])){
                $waves[] = $this->generateGenH($basic);

            }else{
                var_dump($globalSampleHeader['modes']);
                die("unknown format");
            }
        }

        if ($binary->remain() !== 0) {
            echo("NOTE: Export not completed, we have remained data!");
        }

        if ($header['numSamples'] == 1) {
            $result[$globalSampleHeader['name']] = $waves[0];
            $fsbIni['orders'][] = $globalSampleHeader['name'];
        } else {
            foreach ($waves as $index => $wav) {
                $result[$index . '.wav'] = $wav;
                $fsbIni['orders'][] = $index . '.wav';
            }

        }

        unset($fsbIni['numSamples']);
        unset($fsbIni['shdrSize']);
        unset($fsbIni['dataSize']);
        unset($globalSampleHeader['lengthSamples']);
        unset($globalSampleHeader['lengthCompressedBytes']);
        $fsbIni['header'] = $globalSampleHeader;
        $result['fsb3.json'] = \json_encode($fsbIni, JSON_PRETTY_PRINT);

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

        $result = [
            'numSamples' => $numSamples,
            'shdrSize' => $shdrSize,
            'dataSize' => $dataSize,
            'extVersion' => $extVersion,
            'mode' => $mode
        ];

        return $result;
    }

    public function getSampleHeaderBasic(NBinary $binary, $numSamples)
    {

        $samples = [];
        for ($i = 1; $i < $numSamples; $i++) {
            $samples[] = [
                'lengthSamples' => $binary->consume(4, NBinary::INT_32),
                'lengthCompressedBytes' => $binary->consume(4, NBinary::INT_32)
            ];
        }

        return $samples;
    }

    /**
     * @param NBinary $binary
     * @return array
     *
     * SizeOf(2+30+4+4+4+4+4+4+2+2+2+2+4+4+4+4 == 80)
     */
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
        $unknown1 = $binary->consume(4, NBinary::INT_32);
        $unknown2 = $binary->consume(4, NBinary::INT_32);

        $modes = [];
        foreach ($this->modes as $modeName => $val){
            if ($mode & $val) $modes[] = $modeName;
        }

        return [
            'size' => $size,
            'name' => $name,
            'lengthSamples' => $lengthSamples,
            'lengthCompressedBytes' => $lengthCompressedBytes,
            'loopStart' => $loopStart,
            'loopEnd' => $loopEnd,
            'mode' => $mode,
            'modes' => $modes,
            'defFreq' => $defFreq,
            'defVol' => $defVol,
            'defPan' => $defPan,
            'defPri' => $defPri,
            'numChannels' => $numChannels,
            'minDistance' => $minDistance,
            'maxDistance' => $maxDistance,
            'unknown1' => $unknown1,
            'unknown2' => $unknown2
        ];
    }

    public function generateADPCM($data)
    {

        $wav = new Wav();
        return $wav->generateADPCM(new NBinary($data['data']), $data['lengthSamples'], $data['numChannels'], $data['defFreq']);
    }


    private function fwi16(NBinary $fd, $num) {
        $fd->write(($num      ) & 0xff, NBinary::BINARY);
        $fd->write(($num >> 8 ) & 0xff, NBinary::BINARY);
    }

    public function generateGenH($data)
    {

        $raw = new NBinary($data['data']);


        $wav = new NBinary();
        $wav->write('GENH', NBinary::STRING);
        $wav->write($data['numChannels'], NBinary::INT_32);
        $wav->write(2, NBinary::INT_32); // interleave
        $wav->write($data['defFreq'], NBinary::INT_32);

        $wav->write(0xffffffff, NBinary::INT_32); //loop start
        $wav->write((($raw->length()*14)/8)/$data['numChannels'], NBinary::INT_32); //loop end

        $wav->write(12, NBinary::INT_32); //codec
        $wav->write(0x80 + ($data['numChannels'] * 32), NBinary::INT_32); //start_offset
        $wav->write(0x80 + ($data['numChannels'] * 32), NBinary::INT_32); //header_size
        $wav->write(0x80 , NBinary::INT_32); //coef[0]
        $wav->write(0x80 + 32 , NBinary::INT_32); //coef[1]
        $wav->write(1 , NBinary::INT_32); //dsp_interleave_type
        $wav->write(0 , NBinary::INT_32); //coef_type
        $wav->write(0x80 , NBinary::INT_32); //coef_splitted[0]
        $wav->write(0x80 + 32 , NBinary::INT_32); //coef_splitted[1]

        while($wav->current < 0x80){
            $wav->write("\x00" , NBinary::BINARY);
        }

        $coeff = new NBinary($data['moresize_dump']);
        $coeffsz = $data['moreSize'];

        for($i = 0; $i < $data['numChannels']; $i++) {
            if($coeff->length() && ($coeffsz >= 0x2e)) {
                $wav->write($coeff->consume(32, NBinary::BINARY), NBinary::BINARY);
                $coeff->current -= 32;

                $coeff->current   += 0x2e;
                $coeffsz -= 0x2e;
            } else {
                for($j = 0; $j < 16; $j++){
                    $this->fwi16($wav, 0);
                }
            }
        }

        $wav->write($data['data'], NBinary::BINARY);

//
//        $wav->write('fmt ', NBinary::STRING);
//        $wav->write(20, NBinary::INT_32); // sectionsize
//        $wav->write(0x69, NBinary::INT_16); // waveformat
//        $wav->write($numChannels, NBinary::INT_16);
//        $wav->write($defFreq, NBinary::INT_32); // samplespersecond
//        $wav->write($defFreq, NBinary::INT_32); // bytespersecond
//        $wav->write(0x24 * $numChannels, NBinary::INT_16); // blockalign
//        $wav->write(4, NBinary::INT_16); // bitspersample
//
//
//        $wav->write(2, NBinary::INT_16); // adpcm bit
//        $wav->write(0x64, NBinary::INT_16); // adpcm bit
//
//        $wav->write('fact', NBinary::STRING);
//        $wav->write(4, NBinary::INT_32); // factsize
//        $wav->write($lengthUncompressedBytes, NBinary::INT_32); // uncompressedsize
//
//        $wav->write('data', NBinary::STRING); // dataheader
//        $wav->write($data->length(), NBinary::INT_32); // datasize
//        $wav->write($data->binary, NBinary::BINARY);

        return $wav->binary;
    }

//    private $adpcm_history1_32;
//    private $adpcm_step_index;
//    private $offset;
//    private $ADPCMTable = [
//    7, 8, 9, 10, 11, 12, 13, 14,
//    16, 17, 19, 21, 23, 25, 28, 31,
//    34, 37, 41, 45, 50, 55, 60, 66,
//    73, 80, 88, 97, 107, 118, 130, 143,
//    157, 173, 190, 209, 230, 253, 279, 307,
//    337, 371, 408, 449, 494, 544, 598, 658,
//    724, 796, 876, 963, 1060, 1166, 1282, 1411,
//    1552, 1707, 1878, 2066, 2272, 2499, 2749, 3024,
//    3327, 3660, 4026, 4428, 4871, 5358, 5894, 6484,
//    7132, 7845, 8630, 9493, 10442, 11487, 12635, 13899,
//    15289, 16818, 18500, 20350, 22385, 24623, 27086, 29794,
//    32767
//    ];
//
//    public function decode_fsb_ima($vgmstream, NBinary $stream, $outbuf, $channelspacing, $first_sample, $samples_to_do, $channel)
//    {
//        $sample_count = 0;
//
//        $hist1 = $this->adpcm_history1_32;
//        $step_index = $this->adpcm_step_index;
//
//        /* internal interleave (configurable size), mixed channels */
//        $block_samples = (0x24 - 0x4) * 2;
//        $first_sample = $first_sample % $block_samples;
//
//        /* interleaved header (all hist per channel + all step_index+reserved per channel) */
//        if ($first_sample == 0) {
//            $hist_offset = $this->offset + 0x02 * $channel + 0x00;
//            $step_offset = $this->offset + 0x02 * $channel + 0x02 * $vgmstream->channels;
//
//            $stream->current = $hist_offset;
//            $hist1 = $stream->consume(2, NBinary::INT_16);
//
//            $stream->current = $step_offset;
//            $step_index = $stream->consume(1, NBinary::U_INT_8);
//
////            $hist1 = read_16bitLE($hist_offset, $stream->streamfile);
////            $step_index = read_8bit($step_offset, $stream->streamfile);
//            if ($step_index < 0) $step_index = 0;
//            if ($step_index > 88) $step_index = 88;
//
//            /* write header sample (even samples per block, skips last nibble) */
//            $outbuf[$sample_count] = $hist1;
//            $sample_count += $channelspacing;
//            $first_sample += 1;
//            $samples_to_do -= 1;
//        }
//
//        /* decode nibbles (layout: 2 bytes/2*2 nibbles per channel) */
//        for ($i = $first_sample; $i < $first_sample + $samples_to_do; $i++) {
//            $byte_offset = $this->offset + 0x04 * $vgmstream->channels + 0x02 * $channel + ($i - 1) / 4 * 2 * $vgmstream->channels + (($i - 1) % 4) / 2;
//            $nibble_shift = (($i - 1) & 1 ? 4 : 0); /* low nibble first */
//
//            /* must skip last nibble per official decoder, probably not needed though */
//            if ($i < $block_samples) {
//                $this->std_ima_expand_nibble($stream, $byte_offset, $nibble_shift, $hist1, $step_index);
//                $outbuf[$sample_count] = $hist1;
//                $sample_count += $channelspacing;
//            }
//        }
//
//        /* internal interleave: increment offset on complete frame */
//        if ($i == $block_samples) {
//            $this->offset += 0x24 * $vgmstream->channels;
//        }
//
//        $this->adpcm_history1_32 = $hist1;
//        $this->adpcm_step_index = $step_index;
//    }
//
//    /* Original IMA expansion, using shift+ADDs to avoid MULs (slow back then) */
//    public function std_ima_expand_nibble(NBinary $stream, $byte_offset, $nibble_shift, $hist1, $step_index) {
//
//    /* simplified through math from:
//     *  - diff = (code + 1/2) * (step / 4)
//     *   > diff = ((step * nibble) + (step / 2)) / 4
//     *    > diff = (step * nibble / 4) + (step / 8)
//     * final diff = [signed] (step / 8) + (step / 4) + (step / 2) + (step) [when code = 4+2+1] */
//
//        $stream->current = $byte_offset;
//        $code = $stream->consume(1, NBinary::U_INT_8);
//
//
//        $sample_nibble = ($code >> $nibble_shift)&0xf; /* ADPCM code */
//    $sample_decoded = $hist1; /* predictor value */
//    $step = $this->ADPCMTable[$step_index]; /* current step */
//
//    $delta = $step >> 3;
//    if ($sample_nibble & 1) $delta += $step >> 2;
//    if ($sample_nibble & 2) $delta += $step >> 1;
//    if ($sample_nibble & 4) $delta += $step;
//    if ($sample_nibble & 8) $delta = -$delta;
//    $sample_decoded += $delta;
//
//    $hist1 = clamp16($sample_decoded);
//    $step_index += $IMA_IndexTable[$sample_nibble];
//    if ($step_index < 0) $step_index=0;
//    if ($step_index > 88) $step_index=88;
//}
//
//public function CLAMP16( $io )
//{
//if ( (int16_t) io != io )\
//io = (io >> 31) ^ 0x7FFF;\
//}

}