<?php

namespace App\Service\AudioCodec;

use App\Service\Archive\Wav;
use App\Service\NBinary;


class AdxPcma
{
    private $prev = [];

    private function convert(NBinary $in, $prevIndex, $coef1, $coef2)
    {
        $out = new NBinary();

        $scaleIn1 = $in->consume(1, NBinary::U_INT_8);
        $scaleIn2 = $in->consume(1, NBinary::U_INT_8);
        $scale = ((($scaleIn1 << 8) | ($scaleIn2))) + 1;
        $s1 = $this->prev[$prevIndex]['s1'];
        $s2 = $this->prev[$prevIndex]['s2'];
        for ($i = 0; $i < 16; $i++) {
            $inI = $in->consume(1, NBinary::U_INT_8);
            $d = $inI >> 4;
            if ($d & 8) $d -= 16;

            $s0 = $d * $scale + (($coef1 * $s1 + $coef2 * $s2) >> 12);


            if ($s0 > 32767) $s0 = 32767;
            else if ($s0 < -32768) $s0 = -32768;

            $out->write($s0, NBinary::INT_16);

            $s2 = $s1;
            $s1 = $s0;

//            $inI = $in->consume(1, NBinary::U_INT_8);
            $d = $inI & 15;
            if ($d & 8) $d -= 16;
            $s0 = $d * $scale + (($coef1 * $s1 + $coef2 * $s2) >> 12);

            if ($s0 > 32767) $s0 = 32767;
            else if ($s0 < -32768) $s0 = -32768;

            $out->write($s0, NBinary::INT_16);

            $s2 = $s1;
            $s1 = $s0;
        }

        $this->prev[$prevIndex]['s1'] = $s1;
        $this->prev[$prevIndex]['s2'] = $s2;

        return $out;
    }

    public function decode(NBinary $src)
    {

        $wav = new NBinary();

        $id = $src->consume(2, NBinary::HEX);
        $copyrightOffset = $src->consume(2, NBinary::BIG_U_INT_16);
        $encodingType = $src->consume(1, NBinary::HEX); //11 ahx
//https://github.com/Isaac-Lozano/radx/blob/master/src/encoder/ahx_encoder.rs
        $blockSize = $src->consume(1, NBinary::INT_8);

        $sampleBitDepth = $src->consume(1, NBinary::INT_8);
        $channelCount = $src->consume(1, NBinary::INT_8);
        $sampleRate = $src->consume(4, NBinary::BIG_U_INT_32);
        $freq = $sampleRate;
        $size = $src->consume(4, NBinary::BIG_U_INT_32);


        $src->current = $copyrightOffset + 4;


        $this->prev[0] = [ 's1' => 0, 's2' => 0];
        $this->prev[1] = [ 's1' => 0, 's2' => 0];



        $x = 500;
        $y = $freq;
        $z = cos(2.0*M_PI*$x/$y);

        $a = M_SQRT2-$z;
        $b = M_SQRT2-1.0;
        $c = ($a-sqrt(($a+$b)*($a-$b)))/$b;

        $coef1 = floor(8192.0*$c);
        $coef2 = floor(-4096.0*$c*$c);

        $round = 0;
        while($size > 0) {

            $outBuff = $this->convert($src,0, $coef1,$coef2);
            if ($size>32) $wsize=32; else $wsize = $size;
            $size -= $wsize;

            $wav->concat($outBuff);

            $round++;
        }


        $wavHandler = new Wav();
        $wav = $wavHandler->generatePCM($wav, $channelCount, $freq);

        return $wav;
    }
}