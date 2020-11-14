<?php

namespace App\Service\AudioCodec;

use App\Service\NBinary;

define("BYTES_PER_FRAME", 8);
define("SAMPLES_PER_FRAME", 14);
define("NIBBLES_PER_FRAME", 16);
define("SHRT_MAX", 32767);
define("SHRT_MIN", -32767 - 1);

/**
 * Thanks to Thealexbarney!
 * Converted his c code to PHP
 * https://github.com/Thealexbarney/DspTool/blob/master/dsptool/decode.c
 *
 * Class GcAdPcma
 * @package App\Service\AudioCodec
 */
class GcAdPcma
{

    private function DivideByRoundUp($dividend, $divisor)
    {
        return round($dividend + $divisor - 1) / $divisor;
    }

    private function GetHighNibble($value)
    {
        return $value >> 4 & 0xF;
    }

    private function GetLowNibble($value)
    {
        return $value & 0xF;
    }

    private function Clamp16($value)
    {
        if ($value > SHRT_MAX)
            return SHRT_MAX;
        if ($value < SHRT_MIN)
            return SHRT_MIN;
        return $value;
    }

    public function decode(NBinary $src, $cxt, $samples)
    {
        $dst = new NBinary();

        $hist1 = $cxt->yn1;
        $hist2 = $cxt->yn2;
        $coefs = $cxt->coef;
        $frameCount = $this->DivideByRoundUp($samples, SAMPLES_PER_FRAME);
        $samplesRemaining = $samples;

        for ($i = 0; $i < $frameCount; $i++) {
            $val = $src->consume(1, NBinary::U_INT_8);
            $predictor = $this->GetHighNibble($val);
            $scale = 1 << $this->GetLowNibble($val);
            $coef1 = $coefs[$predictor * 2];
            $coef2 = $coefs[$predictor * 2 + 1];

            $samplesToRead = MIN(SAMPLES_PER_FRAME, $samplesRemaining);

            for ($s = 0; $s < $samplesToRead; $s++) {
                $val = $src->consume(1, NBinary::U_INT_8);

                if ($s % 2 == 0) {
                    $sample = $this->GetHighNibble($val);
                    $src->current--;
                } else {
                    $sample = $this->GetLowNibble($val);
                }
//            $sample = $s % 2 == 0 ? $this->GetHighNibble($val) : GetLowNibble($val);
                $sample = $sample >= 8 ? $sample - 16 : $sample;
                $sample = ((($scale * $sample) << 11) + 1024 + ($coef1 * $hist1 + $coef2 * $hist2)) >> 11;
                $finalSample = $this->Clamp16($sample);

                $hist2 = $hist1;
                $hist1 = $finalSample;

                $dst->write($finalSample, NBinary::INT_16);
            }

            $samplesRemaining -= $samplesToRead;
        }

        return $dst->binary;
    }
//
//private function getLoopContext(uint8_t* src, ADPCMINFO* $cxt, uint32_t samples)
//{
//    $hist1 = $cxt->yn1;
//	$hist2 = $cxt->yn2;
//	$coefs = $cxt->coef;
//	$ps = 0;
//	$frameCount = DivideByRoundUp(samples, SAMPLES_PER_FRAME);
//	$samplesRemaining = samples;
//
//	for ($i = 0; i < frameCount; i++)
//	{
//        ps = *src;
//		$predictor = GetHighNibble(*src);
//		$scale = 1 << GetLowNibble(*src++);
//		$coef1 = coefs[predictor * 2];
//		$coef2 = coefs[predictor * 2 + 1];
//
//		$samplesToRead = MIN(SAMPLES_PER_FRAME, samplesRemaining);
//
//		for ($s = 0; s < samplesToRead; s++)
//		{
//            $sample = s % 2 == 0 ? GetHighNibble(*src) : GetLowNibble(*src++);
//			sample = sample >= 8 ? sample - 16 : sample;
//			sample = (((scale * sample) << 11) + 1024 + (coef1 * hist1 + coef2 * hist2)) >> 11;
//			$finalSample = Clamp16(sample);
//
//			hist2 = hist1;
//			hist1 = finalSample;
//		}
//		samplesRemaining -= samplesToRead;
//	}
//
//	cxt->loop_pred_scale = ps;
//	cxt->loop_yn1 = hist1;
//	cxt->loop_yn2 = hist2;
//}
}