<?php
namespace App\Service\Archive;

use App\Service\NBinary;

class Tex {

    private function parseHeader( NBinary &$binary ){

        return [
            'magic'             => $binary->consume(4,  NBinary::STRING),
            'constNumber'       => $binary->consume(4,  NBinary::INT_32),
            'fileSize'          => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset'  => $binary->consume(4,  NBinary::INT_32),
            'indexTableOffset2' => $binary->consume(4,  NBinary::INT_32),
            'numIndex'          => $binary->consume(4,  NBinary::INT_32),
            'unknown'           => $binary->consume(8,  NBinary::HEX),
            'numTextures'       => $binary->consume(4,  NBinary::INT_32),
            'firstOffset'       => $binary->consume(4,  NBinary::INT_32),
            'lastTOffset'       => $binary->consume(4,  NBinary::INT_32)
//            'unknown2'          => $binary->consume(20, NBinary::HEX),
        ];


    }

    private function parseTexture( $startOffset, NBinary &$binary ){

        $binary->jumpTo($startOffset);

        $texture = [
            'nextOffset'        => $binary->consume(4,  NBinary::INT_32),
            'prevOffset'        => $binary->consume(4,  NBinary::INT_32),
            'name'              => $binary->consume(32, NBinary::STRING),
            'alphaFlags'        => $binary->consume(32, NBinary::HEX),
            'width'             => $binary->consume(4,  NBinary::INT_32),
            'height'            => $binary->consume(4,  NBinary::INT_32),
            'bitPerPixel'       => $binary->consume(4,  NBinary::INT_32),
            'pitchOrLinearSize' => $binary->consume(4,  NBinary::INT_32),
            'flags'             => $binary->consume(4,  NBinary::HEX),
            'mipMapCount'       => $binary->consume(1,  NBinary::INT_8),
            'unknown'           => $binary->consume(3,  NBinary::HEX),
            'dataOffset'        => $binary->consume(4,  NBinary::INT_32),
            'paletteOffset'     => $binary->consume(4,  NBinary::INT_32),
            'size'              => $binary->consume(4,  NBinary::INT_32),
            'unknown2'          => $binary->consume(20, NBinary::BINARY)
        ];

        $binary->jumpTo($texture['dataOffset']);

        $texture['data'] = $binary->consume($texture['size'], NBinary::BINARY);

        return $texture;
    }

    public function unpack($binary){

        $binary = new NBinary($binary);
        $header = $this->parseHeader($binary);

        $currentOffset = $header['firstOffset'];

        $textures = [];
        while($header['numTextures'] > 0) {
            $texture = $this->parseTexture($currentOffset, $binary);
            $textures[] = $texture;

            $currentOffset = $texture['nextOffset'];

            $header['numTextures']--;
        }


        return $textures;
    }

    public function pack( ){

        die("Packing it not supported right now.");

    }
}