<?php
namespace App\Service\Archive\Textures;

use App\MHT;
use App\Service\Archive\Archive;
use App\Service\Archive\Textures\Image;
use App\Service\NBinary;

abstract class Texture extends Archive {

    public function parseTexture($startOffset, NBinary &$binary, $platform = MHT::PLATFORM_PC){
        $binary->jumpTo($startOffset);

        $texture = [];

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = true;

        $texture['nextOffset'] = $binary->consume(4,  NBinary::INT_32);
        $texture['prevOffset'] = $binary->consume(4,  NBinary::INT_32);

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = false;

        switch ($platform){
            case MHT::PLATFORM_PC:
                $nameSize = 32;
                break;
            case MHT::PLATFORM_PS2:
            case MHT::PLATFORM_PSP:
                $nameSize = 64;
                break;
            case MHT::PLATFORM_WII:
                $nameSize = 96;
                break;
            default:
                die("name size not available");
                break;
        }


        $texture['name'] = $binary->consume($nameSize,  NBinary::BINARY);

        if ($platform == MHT::PLATFORM_PC){
            $texture['alphaFlags'] = $binary->consume(32,  NBinary::HEX);
        }

        $texture['width'] = $binary->consume(4,  NBinary::INT_32);
        $texture['height'] = $binary->consume(4,  NBinary::INT_32);
        $texture['bitPerPixel'] = $binary->consume(4,  NBinary::INT_32);

        if ($platform == MHT::PLATFORM_PC){
            $texture['pitchOrLinearSize'] = $binary->consume(4,  NBinary::INT_32);
            $texture['flags'] = $binary->consume(4,  NBinary::HEX);
            $texture['mipMapCount'] = $binary->consume(1,  NBinary::INT_8);
            $texture['unknownMinMap'] = $binary->consume(3,  NBinary::HEX);
        }else{
            $texture['rasterFormat'] = $binary->consume(4,  NBinary::HEX);
            $texture['unknown'] = $binary->consume(4,  NBinary::INT_32);
            $texture['unknown2'] = $binary->consume(4,  NBinary::INT_32);
        }

        $texture['name'] = $binary->unpack($texture['name'], NBinary::STRING);

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = true;

        $texture['dataOffset'] = $binary->consume(4,  NBinary::INT_32);

        if ($platform == MHT::PLATFORM_WII) $binary->numericBigEndian = false;

        if ($platform == MHT::PLATFORM_PC || $platform == MHT::PLATFORM_PSP || $platform == MHT::PLATFORM_PS2){
            $texture['paletteOffset'] = $binary->consume(4,  NBinary::INT_32);
        }

        if ($platform != MHT::PLATFORM_PC){
            $texture['unknown3'] = $binary->consume(4,  NBinary::INT_32);
        }

        $texture['dataSize'] = $binary->consume(4,  NBinary::INT_32);
        $texture['empty'] = $binary->consume(20,  NBinary::HEX);

        if (isset($texture['paletteOffset'])){
            $texture['palette'] = false;

            if ($texture['paletteOffset'] > 0 && isset($texture['rasterFormat'])){
                $binary->jumpTo($texture['paletteOffset']);

                $texture['palette'] = $binary->consume($this->getPaletteSize($texture['rasterFormat'], $texture['bitPerPixel']), NBinary::BINARY);
            }
        }


        if ($platform == MHT::PLATFORM_PS2 || $platform == MHT::PLATFORM_PSP) {
            $texture['dataSize'] = $this->getRasterSize($texture['rasterFormat'], $texture['width'], $texture['height'], $texture['bitPerPixel']);
            $binary->jumpTo($texture['dataOffset']);

        }else if ($platform == MHT::PLATFORM_WII) {

            $binary->numericBigEndian = true;

            $ident = $binary->consume(4,  NBinary::INT_32);
            $numTexHeaders = $binary->consume(4,  NBinary::INT_32);

            $offsetTexHeader = $binary->consume(4,  NBinary::INT_32);
            $offsetToHeader = $binary->consume(4,  NBinary::INT_32);

            $unknown1 = $binary->consume(4,  NBinary::INT_32);

            if ($numTexHeaders == 2){
                $unknown1a = $binary->consume(4,  NBinary::INT_32);
                $unknown1b = $binary->consume(4,  NBinary::INT_32);

                //TODO ALPHA CHANNELS
            }

            $width = $binary->consume(2,  NBinary::INT_16);
            $height = $binary->consume(2,  NBinary::INT_16);

            $texture['width'] = $width;
            $texture['height'] = $height;

            $binary->jumpTo($texture['dataOffset'] + (64 * $numTexHeaders) ) ;

        }else{
            $binary->jumpTo($texture['dataOffset']);
        }

        $texture['data'] = $binary->consume($texture['dataSize'], NBinary::BINARY);


        return $texture;

    }


    public function getRasterSize( $format, $width, $height, $bpp ){
        if ($format == "80000000" && $bpp == 32) return $width * $height;
        if ($format == "80000000" && $bpp == 8) return $width * $height;
        if ($format == "08000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "10000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "80000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "00010000" && $bpp == 8) return $width * $height;
        if ($format == "20000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "00010000" && $bpp == 4) return $width * $height;
        if ($format == "40000000" && $bpp == 4) return ($width * $height) / 2;
        if ($format == "40000000" && $bpp == 8) return $width * $height;
        if ($format == "20000000" && $bpp == 8) return $width * $height;
        if ($format == "10000000" && $bpp == 8) return $width * $height;
        if ($format == "00010000" && $bpp == 32) return $width * $height;
        if ($format == "00020000" && $bpp == 8) return $width * $height;

        die(sprintf("Unknown raster format %s bpp: %s", $format, $bpp));
    }


    private function getPaletteSize( $format, $bpp ){
        if ($format == "00010000" && $bpp == 8) return 1024;
        if ($format == "10000000" && $bpp == 8) return 1024;
        if ($format == "20000000" && $bpp == 8) return 1024;
        if ($format == "40000000" && $bpp == 8) return 1024;
        if ($format == "80000000" && $bpp == 32) return 1024;
        if ($format == "80000000" && $bpp == 8) return 1024;
        if ($format == "80000000" && $bpp == 4) return 1024;
        if ($format == "00010000" && $bpp == 32) return 1024;

        if ($format == "08000000" && $bpp == 4) return 64;
        if ($format == "10000000" && $bpp == 4) return 64;
        if ($format == "20000000" && $bpp == 4) return 64;
        if ($format == "40000000" && $bpp == 4) return 64;
        if ($format == "00010000" && $bpp == 4) return 64;
        if ($format == "00020000" && $bpp == 8) return 1024;

        die(sprintf("Unknown palette format %s bpp: %s", $format, $bpp));
    }


    /**
     * Parse regular PC/WII/PSP/PS2 Texture header
     *
     * @param NBinary $binary
     * @return array
     */
    public function parseHeader( NBinary &$binary ){

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
        ];

    }


}