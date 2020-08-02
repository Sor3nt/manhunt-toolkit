<?php
namespace App\Service\Archive\Fsb;

use App\Service\Archive\Wav;
use App\Service\NBinary;

const FSOUND_FSB_VERSION_3_1 = 0x00030001;

/**
 * Class PhpFsbExt
 * @package App\Service\Archive\Fsb
 *
 * Based on the awesome FsbExt library from Luigi Auriemma
 * Thanks to gdawg for the implementation :)
 * https://github.com/gdawg/fsbext
 */
class PhpFsbExt {

    public $debug = false;

    private function log($msg){
        if($this->debug) echo $msg . "\n";
    }

    public $headerModes = [
        'FSOUND_FSB_SOURCE_FORMAT' => 0x00000001, /* all samples stored in their original compressed format */
        'FSOUND_FSB_SOURCE_BASICHEADERS' => 0x00000002, /* samples should use the basic header structure */
        'UNK_BIG_ENDIAN_SAMPLES' => 0x08,
        'UNK_ALIGNED_FILES' => 0x40,
    ];

    public $sampleModes = [
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

    private function resolveHeaderMode($mode){
        $modes = [];
        foreach ($this->headerModes as $modeName => $val){
            if ($mode & $val) $modes[] = $modeName;
        }

        return [$mode, $modes];
    }

    private function resolveSampleMode($mode){
        $modes = [];
        foreach ($this->sampleModes as $modeName => $val){
            if ($mode & $val){
                $modes[] = $modeName;
            }
        }

        return [$mode, $modes];
    }

    /**
     * @param NBinary $header
     * @return array
     */
    private function FSOUND_FSB_HEADER_FSB3(NBinary $header)
    {
        $result = [
            'size' => 24,

            /* number of samples in the file */
            'numSamples' => $header->consume(4, NBinary::INT_32),

            /* size in bytes of all of the sample headers including extended information */
            'shdrSize' => $header->consume(4, NBinary::INT_32),

            /* size in bytes of compressed sample data */
            'dataSize' => $header->consume(4, NBinary::INT_32),

            /* extended fsb version */
            'version' => $header->consume(4, NBinary::INT_32),

            'mode' => $this->resolveHeaderMode($header->consume(4, NBinary::LITTLE_U_INT_32))
        ];

        return $result;
    }


    /**
     * @param NBinary $binary
     * @return array
     */
    public function FSOUND_FSB_SAMPLE_HEADER_BASIC(NBinary $binary)
    {

        return [
            'lengthSamples' => $binary->consume(4, NBinary::INT_32),
            'lengthCompressedBytes' => $binary->consume(4, NBinary::INT_32)
        ];
    }


    /**
     * @param NBinary $binary
     * @return array
     */
    public function FSOUND_FSB_SAMPLE_HEADER_3_1(NBinary $binary)
    {
        return [
            'size' => $binary->consume(2, NBinary::INT_16),
            'name' => $binary->consume(30, NBinary::STRING),
            'lengthSamples' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'lengthCompressedBytes' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'loopStart' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'loopEnd' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'mode' => $this->resolveSampleMode($binary->consume(4, NBinary::LITTLE_U_INT_32)),
            'defFreq' => $binary->consume(4, NBinary::INT_32),
            'defVol' => $binary->consume(2, NBinary::LITTLE_U_INT_16),
            'defPan' => $binary->consume(2, NBinary::INT_16),
            'defPri' => $binary->consume(2, NBinary::LITTLE_U_INT_16),
            'numChannels' => $binary->consume(2, NBinary::LITTLE_U_INT_16),
            'minDistance' => $binary->consume(4, NBinary::FLOAT_32),
            'maxDistance' => $binary->consume(4, NBinary::FLOAT_32),
            'varVol' => $binary->consume(4, NBinary::INT_32),
            'varFreq' => $binary->consume(2, NBinary::LITTLE_U_INT_16),
            'varPan' => $binary->consume(2, NBinary::INT_16)
        ];
    }

    /**
     * @param NBinary $header
     * @return array
     */
    private function FSOUND_FSB_HEADER_FSB4(NBinary $header)
    {
        $result = [
            'size' => 48,

            /* number of samples in the file */
            'numSamples' => $header->consume(4, NBinary::INT_32),

            /* size in bytes of all of the sample headers including extended information */
            'shdrSize' => $header->consume(4, NBinary::INT_32),

            /* size in bytes of compressed sample data */
            'dataSize' => $header->consume(4, NBinary::INT_32),

            /* extended fsb version */
            'version' => $header->consume(4, NBinary::INT_32),

            'mode' => $this->resolveHeaderMode($header->consume(4, NBinary::LITTLE_U_INT_32)),

            'zero' => $header->consume(8, NBinary::HEX),

            'hash' => $header->consume(16, NBinary::HEX)
        ];

        return $result;
    }


    private function getFileWithCorrectExtension($name, $mode ){

        if (strpos($name, ".") !== false){
            $name = explode(".", $name)[0];
        }

        if (in_array("FSOUND_IMAADPCM", $mode) !== false) return $name . ".wav";
        if (in_array("FSOUND_GCADPCM", $mode) !== false) return $name . ".genh";

        return $name . ".unk";
    }


    /**
     * @param NBinary $binary
     * @return array
     * @throws \Exception
     */
    public function encode(NBinary $binary){

        $fourCC = $binary->consume(3, NBinary::STRING);
        if ($fourCC !== "FSB") throw new \Exception('Not a valid FSB file');

        $version = (int) $binary->consume(1, NBinary::BINARY);

        switch ($version){

            case 3:
            case 4:
                $fsbHeader = $version == 3 ?
                    $this->FSOUND_FSB_HEADER_FSB3($binary) :
                    $this->FSOUND_FSB_HEADER_FSB4($binary)
                ;

                $fileOff = $fsbHeader['size'] + $fsbHeader['shdrSize'];
                $headMode = $fsbHeader['mode'][1];

                break;

            default:
                throw new \Exception(sprintf('FSB%s Format is not supported', $version));
        }

        $fsbIni = $fsbHeader;

        unset($fsbIni['numSamples']);
        unset($fsbIni['shdrSize']);
        unset($fsbIni['dataSize']);
        $fsbIni['mode'] = $fsbIni['mode'][0];
        $fsbIni['orders'] = [];

        $this->log("\n");

        $this->log(
            sprintf(
                "- FSB%s version %s.%s mode(s) %s\n",
                $version,
                ($fsbHeader['version'] >> 16) & 0xffff, $fsbHeader['version'] & 0xffff,
                implode(", ", $headMode)
            )
        );

        $mode = $size = $moresize = 0 ;
        $freq = 44100;
        $bits = 16;
        $chans = 1;


        $this->log(sprintf(
            "\nFilename                         Size       Mode frequency channels bits\n".
            "========================================================================\n"
        ));

        $files = [];
        for($i = 0; $i < $fsbHeader['numSamples']; $i++){

            switch ($version){

                case 3:

                    if (
                        in_array('FSOUND_FSB_SOURCE_BASICHEADERS', $headMode) &&
                        $i
                    ){

                        $fsb = $this->FSOUND_FSB_SAMPLE_HEADER_BASIC($binary);

                        $name = $i;
                        $size = $fsb['lengthCompressedBytes'];
                        $samples = $fsb['lengthSamples'];

                    }else{
                        if ($fsbHeader['version'] == FSOUND_FSB_VERSION_3_1){
                            $fs = $this->FSOUND_FSB_SAMPLE_HEADER_3_1($binary);

                            $name = $fs['name'];
                            $freq = $fs['defFreq'];
                            $chans = $fs['numChannels'];
                            $mode = $fs['mode'][1];
                            $size = $fs['lengthCompressedBytes'];
                            $samples = $fs['lengthSamples'];

                            $moresize = $fs['size'] - 80;

                            unset($fs['lengthSamples']);
                            unset($fs['lengthCompressedBytes']);
                            $fs['mode'] = $fs['mode'][0];
                            $fsbIni['header'] = $fs;
                        }else{
                            throw new \Exception('FSOUND_FSB_SAMPLE_HEADER_2 not implemented!');
                        }
                    }

                    break;

                case 4:
                    if (
                        in_array('FSOUND_FSB_SOURCE_BASICHEADERS', $headMode) &&
                        $i
                    ){
                        $fsb = $this->FSOUND_FSB_SAMPLE_HEADER_BASIC($binary);

                        $name = $i . '.wav';
                        $size = $fsb['lengthCompressedBytes'];
                        $samples = $fsb['lengthSamples'];

                    }else{
                        $fs = $this->FSOUND_FSB_SAMPLE_HEADER_3_1($binary);

                        $name = $fs['name'];
                        $freq = $fs['defFreq'];
                        $chans = $fs['numChannels'];
                        $mode = $fs['mode'][1];
                        $size = $fs['lengthCompressedBytes'];
                        $samples = $fs['lengthSamples'];

                        $moresize = $fs['size'] - 80;


                        unset($fs['name']);
                        unset($fs['lengthCompressedBytes']);
                        unset($fs['lengthSamples']);
                        unset($fs['size']);
                        $fs['mode'] = $fs['mode'][0];
                        $files['settings/' . $name . '.json'] = \json_encode($fs, JSON_PRETTY_PRINT);
                    }

                    break;

                default:
                    throw new \Exception(sprintf('FSB Format "%s" is not supported', $version));
            }

            $this->log(
                sprintf(
                    "%-32s %-10u %s %d %d %d",
                    $name, $size, implode(",", $mode), $freq, $chans, $bits
                )
            );


            $moresize_dump = "";
            if ($moresize > 0){
                $moresize_dump = $binary->consume($moresize, NBinary::BINARY);
            }

            $current_offset = $binary->current;
            $binary->current = $fileOff;


            $name = $this->getFileWithCorrectExtension($name, $mode);

            $fsbIni['orders'][] = $name;
            $files[$name] = $this->extract_file($binary, $freq, $chans, $bits, $size, $moresize_dump, $moresize, $samples, $mode);

            $fileOff += $size;

            $binary->current = $current_offset;
        }

        $fsbFile = 'fsb' . $version . '.json';
        if ($version == 4) $fsbFile = 'settings/' . $fsbFile;

        $files[$fsbFile] = \json_encode($fsbIni, JSON_PRETTY_PRINT);

        return $files;

    }

    /**
     * @param NBinary $binary
     * @param $name
     * @param $freq
     * @param $chans
     * @param $bits
     * @param $size
     * @param $moresize_dump
     * @param $moresize
     * @param $samples
     * @param $mode
     * @return string|null
     * @throws \Exception
     */
    private function extract_file(NBinary $binary, $freq, $chans, $bits, $size, $moresize_dump, $moresize, $samples, $mode){
        $raw = new NBinary($binary->consume($size, NBinary::BINARY));

        switch (true){

            case in_array("FSOUND_IMAADPCM", $mode) !== false:
                $wav = new Wav();
                return $wav->generateADPCM($raw, $samples, $chans, $freq);

                break;

            case in_array("FSOUND_GCADPCM", $mode) !== false:
                return $this->generateGenH($raw, $chans, $freq, $moresize_dump, $moresize);
                break;

            default:
                throw new \Exception('Unknown Codec!');
                break;

        }

    }


    private function fwi16(NBinary $fd, $num) {
        $fd->write(($num      ) & 0xff, NBinary::BINARY);
        $fd->write(($num >> 8 ) & 0xff, NBinary::BINARY);
    }

    public function generateGenH(NBinary $raw, $numChannels, $defFreq, $moresize_dump, $moresize)
    {


        $genH = new NBinary();
        $genH->write('GENH', NBinary::STRING);
        $genH->write($numChannels, NBinary::INT_32);
        $genH->write(2, NBinary::INT_32); // interleave
        $genH->write($defFreq, NBinary::INT_32);

        $genH->write(0xffffffff, NBinary::INT_32); //loop start
        $genH->write((($raw->length()*14)/8)/$numChannels, NBinary::INT_32); //loop end

        $genH->write(12, NBinary::INT_32); //codec
        $genH->write(0x80 + ($numChannels * 32), NBinary::INT_32); //start_offset
        $genH->write(0x80 + ($numChannels * 32), NBinary::INT_32); //header_size
        $genH->write(0x80 , NBinary::INT_32); //coef[0]
        $genH->write(0x80 + 32 , NBinary::INT_32); //coef[1]
        $genH->write(1 , NBinary::INT_32); //dsp_interleave_type
        $genH->write(0 , NBinary::INT_32); //coef_type
        $genH->write(0x80 , NBinary::INT_32); //coef_splitted[0]
        $genH->write(0x80 + 32 , NBinary::INT_32); //coef_splitted[1]

        while($genH->current < 0x80){
            $genH->write("\x00" , NBinary::BINARY);
        }

        $coeff = new NBinary($moresize_dump);
        $coeffsz = $moresize;

        for($i = 0; $i < $numChannels; $i++) {
            if($coeff->length() && ($coeffsz >= 0x2e)) {
                $genH->write($coeff->consume(32, NBinary::BINARY), NBinary::BINARY);
                $coeff->current -= 32;

                $coeff->current += 0x2e;
                $coeffsz -= 0x2e;
            } else {
                for($j = 0; $j < 16; $j++){
                    $this->fwi16($genH, 0);
                }
            }
        }

        $genH->write($raw->binary, NBinary::BINARY);

        return $genH->binary;
    }



}
