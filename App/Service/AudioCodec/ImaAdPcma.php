<?php

namespace App\Service\AudioCodec;

use App\Service\NBinary;


/**
 * Thanks to Sergeanur!
 * Converted his c code to PHP
 * https://github.com/Sergeanur/XboxADPCM/
 *
 * Class ImaAdPcma
 * @package App\Service\AudioCodec
 */
class ImaAdPcma
{

    private $IMA_ADPCMStepTable =
        [
            7, 8, 9, 10, 11, 12, 13, 14,
            16, 17, 19, 21, 23, 25, 28, 31,
            34, 37, 41, 45, 50, 55, 60, 66,
            73, 80, 88, 97, 107, 118, 130, 143,
            157, 173, 190, 209, 230, 253, 279, 307,
            337, 371, 408, 449, 494, 544, 598, 658,
            724, 796, 876, 963, 1060, 1166, 1282, 1411,
            1552, 1707, 1878, 2066, 2272, 2499, 2749, 3024,
            3327, 3660, 4026, 4428, 4871, 5358, 5894, 6484,
            7132, 7845, 8630, 9493, 10442, 11487, 12635, 13899,
            15289, 16818, 18500, 20350, 22385, 24623, 27086, 29794,
            32767
        ];


    private $IMA_ADPCMIndexTable = [-1, -1, -1, -1, 2, 4, 6, 8];

    public $predictedValue;
    public $stepIndex;

//
//public function encodeInit($sample1,$sample2) {
//    $this->predictedValue = $sample1;
//    $delta = $sample2 - $sample1;
//	if ($delta < 0) $delta = - $delta;
//	if ($delta > 32767) $delta = 32767;
//
//	$stepIndex = 0;
//
//	while ($this->IMA_ADPCMStepTable[$stepIndex] < $delta)
//		$stepIndex++;
//
//	$this->stepIndex = $stepIndex;
//}

//
    public function IMA_ADPCMEncode($pcm16)
    {
        $predicedValue = $this->predictedValue;
        $stepIndex = $this->stepIndex;

        $delta = $pcm16 - $predicedValue;

        $value = 0;
        if ($delta >= 0) $value = 0;
        else {
            $value = 8;
            $delta = -$delta;
        }

        $step = $this->IMA_ADPCMStepTable[$stepIndex];
        $diff = $step >> 3;
        if ($delta > $step) {
            $value |= 4;
            $delta -= $step;
            $diff += $step;
        }

        $step >>= 1;
        if ($delta > $step) {
            $value |= 2;
            $delta -= $step;
            $diff += $step;
        }

        $step >>= 1;
        if ($delta > $step) {
            $value |= 1;
            $diff += $step;
        }

        if ($value & 8) $predicedValue -= $diff;
        else $predicedValue += $diff;

        if ($predicedValue < -0x8000) $predicedValue = -0x8000;
        else if ($predicedValue > 0x7fff) $predicedValue = 0x7fff;
        $this->predictedValue = $predicedValue;

        $stepIndex += $this->IMA_ADPCMIndexTable[$value & 7];

        if ($stepIndex < 0) $stepIndex = 0;
        else if ($stepIndex > 88) $stepIndex = 88;
        $this->stepIndex = $stepIndex;

        return $value;
    }


    private function IMA_ADPCMDecode($adpcm)
    {
        $stepIndex = $this->stepIndex;
        $step = $this->IMA_ADPCMStepTable[$stepIndex];
        $stepIndex += $this->IMA_ADPCMIndexTable[$adpcm & 7];

        if ($stepIndex < 0) $stepIndex = 0;
        else if ($stepIndex > 88) $stepIndex = 88;
        $this->stepIndex = $stepIndex;

        $diff = $step >> 3;
        if ($adpcm & 4) $diff += $step;
        if ($adpcm & 2) $diff += $step >> 1;
        if ($adpcm & 1) $diff += $step >> 2;

        $predicedValue = $this->predictedValue;
        if ($adpcm & 8) $predicedValue -= $diff;
        else $predicedValue += $diff;

        if ($predicedValue < -0x8000) $predicedValue = -0x8000;
        else if ($predicedValue > 0x7fff) $predicedValue = 0x7fff;
        $this->predictedValue = $predicedValue;

        return $predicedValue;
    }

//
    public function encode(NBinary $dst, $dstOffset, $srcArray, $srcSize)
    {
        // use given bit offset
//        $dst->current += $dstOffset>>3;

        $bitOffset = $dstOffset & 4;

        // make sure srcSize represents a whole number of samples
        $srcSize &= ~1;

        // calculate end of input buffer
//        $end = $src->current + $srcSize;

        $index = 8;
        $srcArrayIndex = 0;

        $block = new NBinary();
        while ($index--) {
            // encode a pcm value from input buffer
            $adpcm = $this->IMA_ADPCMEncode($srcArray[$srcArrayIndex]);
            $srcArrayIndex++;

//var_dump($adpcm);exit;
            // pick which nibble to write adpcm value to...
            if(!$bitOffset){
                $block->write($adpcm, NBinary::U_INT_8);
                $block->current--;
//                $dst = $adpcm;		// write adpcm value to low nibble
            }else
            {

                $b = $block->consume(1, NBinary::U_INT_8);		// get byte from ouput
                $b &= 0x0f;			// clear bits of high nibble
                $b |= $adpcm << 4;		// or adpcm value into the high nibble

                //$dst++ = $b;	// write value back to output and move on to next byte
                $block->current--;

                $block->overwrite($b, NBinary::U_INT_8);
                $block->current++;
            }

            // toggle which nibble in byte to write to next
            $bitOffset ^= 4;
        }

        $dst->concat($block);

    }


    public function decode(NBinary $src)
    {

        $srcOffset = 0;
        // use given bit offset
        $src->current += $srcOffset >> 3;

        $decodedInt = [];
        $index = 8;

        while ($index--) {
            // get byte from src
            $adpcm = $src->consume(1, NBinary::U_INT_8);

            // pick which nibble holds a adpcm value...
            if ($srcOffset & 4) {
                $adpcm >>= 4;    // use high nibble of byte
            } else {
                $src->current--; // move back a byte (the consume command eat one)
            }

            $decodedInt[] = $this->IMA_ADPCMDecode($adpcm);
            // toggle which nibble in byte to write to next
            $srcOffset ^= 4;
        }

        return $decodedInt;
    }
}