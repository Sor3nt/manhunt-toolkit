<?php

/**
 * All the work was done by https://github.com/winterheart/ManhuntRIBber
 * i just ported it
 */

namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Rib extends \App\Service\Archive\Archive
{

    public $mono = false;
    public $name = 'RIB Audio (Manhunt 1)';

    public static $supported = 'rib';

    // Taken from ADPCM reference
    private array $adpcm_step_table = [
        7, 8, 9, 10, 11, 12, 13, 14, 16, 17,    // 10
        19, 21, 23, 25, 28, 31, 34, 37, 41, 45,    // 20
        50, 55, 60, 66, 73, 80, 88, 97, 107, 118,   // 30
        130, 143, 157, 173, 190, 209, 230, 253, 279, 307,   // 40
        337, 371, 408, 449, 494, 544, 598, 658, 724, 796,   // 50
        876, 963, 1060, 1166, 1282, 1411, 1552, 1707, 1878, 2066,  // 60
        2272, 2499, 2749, 3024, 3327, 3660, 4026, 4428, 4871, 5358,  // 70
        5894, 6484, 7132, 7845, 8630, 9493, 10442, 11487, 12635, 13899, // 80
        15289, 16818, 18500, 20350, 22385, 24623, 27086, 29794, 32767         // 89
    ];

    // Taken from ADPCM reference
    private array $adpcm_index_table = [
        -1, -1, -1, -1, 2, 4, 6, 8, // 8
        -1, -1, -1, -1, 2, 4, 6, 8, // 16
    ];

    private int $interleave = 0x10000;
    private int $chunkSize = 0x400;
    private int $nb_chunks_in_interleave;
    private int $nb_chunk_encoded;
    private int $nb_chuck_decoded;
    private int $nb_channels = 2;  // Mono/Stereo

    public function __constructor(){
        $this->recalc();
    }

    private function recalc(){
        $this->nb_chunks_in_interleave = $this->interleave / $this->chunkSize;
        $this->nb_chunk_encoded = $this->chunkSize - 4;
        $this->nb_chuck_decoded = 2 * $this->nb_chunk_encoded + 1;
    }

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack($pathFilename, $input, $game, $platform)
    {

        if ($input instanceof Finder) return false;
        if (!$input instanceof NBinary) return false;

        if( strpos($pathFilename, "#RIB.wav") !== false){
            return true;
        }
        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform)
    {


        //DDEATH channels=1 size 0x400 mono false
        //MUSIC; ATTIC_M channels=1 size 0x400 mono true
        //MUSIC; ATTIC_L channels=1 size 0x400 mono true
        //MUSIC; ATTIC_D channels=1 size 0x400 mono false


        $is_mono = $this->mono;

        $result = new NBinary();

        if ($is_mono) {
            $this->chunkSize = 0x200;
            $this->nb_channels = 1;
        }

        $this->recalc();

        $input_size = $binary->length();

        $nb_samples = $input_size / ($this->nb_channels * $this->interleave);
        $nb_chunks = $this->interleave / $this->chunkSize;

//        $requiredSize = $this->nb_channels * $nb_chunks *  $nb_samples * $this->chunk_size;

        for ($i = 0; $i < $nb_samples; $i++) {

            $outputs = array_fill(0, $this->nb_channels, []);

            for ($ch = 0; $ch < $this->nb_channels; $ch++) {
                for ($j = 0; $j < $nb_chunks; $j++) {
                    $this->adpcm_rib_decode_frame($binary, $outputs[$ch]);
                }
            }

            for ($j = 0; $j < $this->nb_chuck_decoded * $nb_chunks; $j++) {
                for ($ch = 0; $ch < $this->nb_channels; $ch++) {
                    $result->write($outputs[$ch][$j], NBinary::INT_16);
                }
            }
        }

        $wav = Wav::generatePCM(
            $result,
            $this->nb_channels,
            $is_mono ? 22100 : 44100,
            $is_mono ? 88200 : 176400,
            $is_mono ? 2 : 4
        );

        return $wav;
//
//        $repack = $this->pack($wav, $game, $platform);
//
//        if ($repack->binary !== $binary->binary)
//            die("repack invalid!!");
//        else
//            echo "Valid!\n";
//
//        return ['test.wav' => $wav, 'repack.rib' => $repack->binary, 'og.rib' => $binary->binary];
    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return NBinary
     */
    public function pack($pathFilename, $game, $platform)
    {

        /** @var NBinary $input */
        $input = $pathFilename;

        /** @var NBinary[] $outputs */
        /** @var NBinary[] $inputs */
        $outputs = [];
        $inputs = [];
        $result = new NBinary();


        $input->current = 22;
        $channels = $input->consume(2, NBinary::INT_16);
        $input->current = 44;

        if ($channels === 1) $this->chunkSize = 0x200;

        for($t = 0; $t < $channels; $t++){
            $outputs[] = new NBinary();
            $inputs[] = new NBinary();
        }

        $this->recalc();

        while($input->remain() !== 0){
            for ($ch = 0; $ch < $channels; $ch++) {
                $r = $input->consume(2, NBinary::INT_16);
                $inputs[$ch]->write($r, NBinary::INT_16);
            }
        }

        foreach ($inputs as $_input) {
            $_input->current = 0;
        }

        $nb_interleaves_per_ch = floor($inputs[0]->length() / (4 * $this->nb_chunk_encoded * $this->nb_chunks_in_interleave));

        for ($ch = 0; $ch < $channels; $ch++) {

            $ADPCMChannelStatus = [
                'predictor' => 0,
                'step_index' => 0,
                'prev_sample' => 0
            ];

            for ($sample = 0; $sample < $nb_interleaves_per_ch * $this->nb_chunks_in_interleave; $sample++) {
                $inputs[$ch]->current = $sample * ($this->nb_chuck_decoded * 2);

                $chunk = $inputs[$ch]->consume($this->nb_chuck_decoded * 2, NBinary::BINARY);
                $chunk = new NBinary($chunk);

                $this->adpcm_rib_encode_frame($ADPCMChannelStatus, $chunk, $outputs[$ch] );
            }
        }

        for ($i = 0; $i < $nb_interleaves_per_ch * $this->interleave; $i += $this->interleave) {
            for ($ch = 0; $ch < $channels; $ch++) {

                $outputs[$ch]->current = $i;

                $dat = new NBinary($outputs[$ch]->consume($this->interleave, NBinary::BINARY));
                while($dat->remain() > 0){
                    $result->write($dat->consume(1, NBinary::INT_8), NBinary::INT_8);
                }
            }
        }
        return $result;
    }

    private function adpcm_clip_int16($a)
    {
        if ($a < -0x8000 || $a > 0x7FFF) {
            return ($a >> 31) ^ 0x7FFF;
        }

        return $a;
    }

    private function clamp($value, $min, $max)
    {
        return max($min, min($max, $value));
    }

    private function adpcm_ima_qt_expand_nibble(array &$ADPCMChannelStatus, int $nibble)
    {

        if (!isset($this->adpcm_step_table[$ADPCMChannelStatus['step_index']])){
            die("invalid step_index");
        }

        $step = $this->adpcm_step_table[$ADPCMChannelStatus['step_index']];
        $step_index = $ADPCMChannelStatus['step_index'] + $this->adpcm_index_table[$nibble];
        $step_index = $this->clamp($step_index, 0, 88);

        $diff = $step >> 3;
        if ($nibble & 4) $diff += $step;
        if ($nibble & 2) $diff += $step >> 1;
        if ($nibble & 1) $diff += $step >> 2;

        if ($nibble & 8) $predictor = $ADPCMChannelStatus['predictor'] - $diff;
        else $predictor = $ADPCMChannelStatus['predictor'] + $diff;

        $ADPCMChannelStatus['predictor'] = $this->adpcm_clip_int16($predictor);
        $ADPCMChannelStatus['step_index'] = $step_index;

        return $ADPCMChannelStatus['predictor'];
    }

    private function adpcm_ima_qt_compress_sample(array &$ADPCMChannelStatus, int $sample)
    {
        $delta = $sample - $ADPCMChannelStatus['prev_sample'];
        $step = $this->adpcm_step_table[$ADPCMChannelStatus['step_index']];
        $nibble = 8 * ($delta < 0);

        $delta = abs($delta);
        $diff = $delta + ($step >> 3);

        if ($delta >= $step) {
            $nibble |= 4;
            $delta -= $step;
        }
        $step >>= 1;
        if ($delta >= $step) {
            $nibble |= 2;
            $delta -= $step;
        }
        $step >>= 1;
        if ($delta >= $step) {
            $nibble |= 1;
            $delta -= $step;
        }
        $diff -= $delta;

        if ($nibble & 8) $ADPCMChannelStatus['prev_sample'] -= $diff;
        else $ADPCMChannelStatus['prev_sample'] += $diff;

        $ADPCMChannelStatus['prev_sample'] = $this->adpcm_clip_int16($ADPCMChannelStatus['prev_sample']);
        $ADPCMChannelStatus['step_index'] = $this->clamp($ADPCMChannelStatus['step_index'] + $this->adpcm_index_table[$nibble], 0, 88);

        return $nibble;
    }

    private function adpcm_rib_decode_frame(NBinary $in_stream, array &$out_stream) {
        $ADPCMChannelStatus = [];

        $ADPCMChannelStatus['predictor'] = $in_stream->consume(2, NBinary::INT_16);
        $ADPCMChannelStatus['step_index'] = $in_stream->consume(1, NBinary::U_INT_8);
        $in_stream->current += 1;

        $out_stream[] = $ADPCMChannelStatus['predictor'];

        for ($pos = 0; $pos < $this->chunkSize - 4; $pos++) {
            $byte = $in_stream->consume(1, NBinary::U_INT_8);
            $out_stream[] = $this->adpcm_ima_qt_expand_nibble($ADPCMChannelStatus, $byte & 0x0f);
            $out_stream[] = $this->adpcm_ima_qt_expand_nibble($ADPCMChannelStatus, $byte >> 4);
        }
    }

    private function adpcm_rib_encode_frame(array &$ADPCMChannelStatus, NBinary $input, NBinary $output) {

        $ADPCMChannelStatus['prev_sample'] = $input->consume(2, NBinary::INT_16);
//var_dump("step_index", $ADPCMChannelStatus['step_index'] & 0xFF);
        $output->write($ADPCMChannelStatus['prev_sample'] & 0xFF, NBinary::INT_8);
        $output->write(($ADPCMChannelStatus['prev_sample'] >> 8) & 0xFF, NBinary::U_INT_8);
        $output->write($ADPCMChannelStatus['step_index'], NBinary::U_INT_8);
        $output->write(0, NBinary::INT_8);

        while ($input->remain() > 0) {

            if ($input->remain() < 4){
                die("em no input ?! remain " . $input->remain());
            }

            $r = $input->consume(2, NBinary::INT_16);
            $r2 = $input->consume(2, NBinary::INT_16);


            $nibble1 = $this->adpcm_ima_qt_compress_sample($ADPCMChannelStatus, $r);
            $nibble2 = $this->adpcm_ima_qt_compress_sample($ADPCMChannelStatus, $r2);

            $output->write(($nibble2 << 4) | $nibble1, NBinary::U_INT_8);
        }

        return 0;
    }

}

