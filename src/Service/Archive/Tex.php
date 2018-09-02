<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\Binary;

class Tex {

    private function toInt8($hex){
        return is_int($hex) ? pack("c", $hex) :  current(unpack("c", hex2bin($hex)));
    }

    private function toInt16($hex){
        return is_int($hex) ? pack("s", $hex) : current(unpack("s", hex2bin($hex)));
    }

    private function toString( $hex ){
        $hex = str_replace('00', '', $hex);
        return hex2bin($hex);
    }

    private function toInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));
    }

    private function toFloat( $hex ){
        return (float) current(unpack("f", hex2bin($hex)));
    }


    private function substr(&$hex, $start, $end){

        $result = substr($hex, $start * 2, $end * 2);
        $hex = substr($hex, $end * 2);
        return $result;
    }


    public function unpack($entry, $outputTo){

        $fullEntry = $entry;

        $headerType = $this->toString($this->substr($entry, 0, 4));
        if ($headerType !== "TCDT")
            throw new \Exception(
                sprintf('Expected TCDT got: %s', $headerType)
            );

        /**
         * Parse Header
         */
        $constNumber = $this->toInt($this->substr($entry, 0, 4));
        $fileSize = $this->toInt($this->substr($entry, 0, 4));
        $indexTableOffset = $this->toInt($this->substr($entry, 0, 4));
        $indexTableOffset2 = $this->toInt($this->substr($entry, 0, 4));
        $numIndex = $this->toInt($this->substr($entry, 0, 4));

        $unknown1 = $this->toInt($this->substr($entry, 0, 4));
        $unknown2 = $this->toInt($this->substr($entry, 0, 4));
        $numTextures = $this->toInt($this->substr($entry, 0, 4));

        $firstTextureOffset = $this->toInt($this->substr($entry, 0, 4));
        $lastTextureOffset = $this->toInt($this->substr($entry, 0, 4));
        $unknown3 = $this->toInt($this->substr($entry, 0, 4));

        $padding = $this->substr($entry, 0, 16);

        $nextTextureOffset = $firstTextureOffset;

        while($numTextures > 0){

            $firstTextureOffset = substr($fullEntry, $nextTextureOffset * 2);

            $nextTextureOffset = $this->toInt($this->substr($firstTextureOffset, 0, 4));
            $prevTextureOffset = $this->toInt($this->substr($firstTextureOffset, 0, 4));

            $textureName = $this->substr($firstTextureOffset, 0, 32);

            $textureName = hex2bin($textureName);
            $textureName = mb_substr($textureName, 0, mb_strpos($textureName, "\x00"));

            $alphaFlagPart = $this->substr($firstTextureOffset, 0, 32);
            $alphaFlag = $this->substr($alphaFlagPart, 0, 1);

            $textureWidth = $this->toInt($this->substr($firstTextureOffset, 0, 4));
            $textureHeight = $this->toInt($this->substr($firstTextureOffset, 0, 4));
            $bitPerPixel = $this->toInt($this->substr($firstTextureOffset, 0, 4));
            $pitchOrLinearSize = $this->toInt($this->substr($firstTextureOffset, 0, 4));

            $flags = $this->toInt16($this->substr($firstTextureOffset, 0, 2));
            $unknown4 = $this->toInt16($this->substr($firstTextureOffset, 0, 2));

            $mipMapCount = $this->toInt8($this->substr($firstTextureOffset, 0, 1));
            $padding = $this->substr($firstTextureOffset, 0, 3);

            $texturesDataOffset = $this->toInt($this->substr($firstTextureOffset, 0, 4));
            $paletteDataOffset = $this->toInt($this->substr($firstTextureOffset, 0, 4));
            $ddsFileSize  = $this->toInt($this->substr($firstTextureOffset, 0, 4));

            $padding = $this->substr($firstTextureOffset, 0, 20);

            $ddsFile = $this->substr($firstTextureOffset, 0, $ddsFileSize);

            file_put_contents($outputTo . $textureName . ".dds", hex2bin($ddsFile));

            $numTextures--;
        }
    }

    public function pack( $executions, $envExecutions, $paddings ){

        die("no packing support right now");

    }
}