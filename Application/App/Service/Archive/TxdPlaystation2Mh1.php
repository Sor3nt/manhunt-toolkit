<?php

namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Textures\Image;
use App\Service\Archive\Textures\Playstation;
use App\Service\NBinary;

class TxdPlaystation2Mh1 extends Archive
{
    public $name = 'Textures (PS2)';

    public static $validationMap = [
        [0, 4, NBinary::HEX, ['16000000']]
    ];

    private $playstation;
    private $textureCount;

    public function __construct()
    {
        $this->playstation = new Playstation();
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
        return false;
    }

    private function parseHeader(NBinary $binary)
    {
        return [
            'id' => $binary->consume(4, NBinary::INT_32),
            'size' => $binary->consume(4, NBinary::INT_32),
            'version' => $binary->consume(4, NBinary::INT_32)
        ];
    }


    private function readChunk(NBinary $binary)
    {
        $header = $this->parseHeader($binary);
        if ($header['size'] == 0) return ['type' => 'na'];

        switch ($header['id']){
            case 3:
            case 22:
                $binary->current += 4 * 4; //id3: skip chunk + unknown, id22: skip chunk, texture count and deviceId
                break;

            case 21:

                $binary->current += 20;  // skip platform

                $nameHeader = $this->parseHeader($binary);
                $name = $binary->consume($nameHeader['size'], NBinary::STRING);

                $alphaNameHeader = $this->parseHeader($binary);
                $alphaName = $binary->consume($alphaNameHeader['size'], NBinary::STRING);

                $binary->current += 6 * 4;  // skip 2 chunks

                $width = [$binary->consume(4, NBinary::INT_32)];
                $height = [$binary->consume(4, NBinary::INT_32)];
                $depth = $binary->consume(4, NBinary::INT_32);
                $rasterFormat = $binary->consume(4, NBinary::INT_32);

                $binary->current += 8 * 4; //4*uiTex + 4*miptbp
                $dataSize = $binary->consume(4, NBinary::INT_32);
                $binary->current += 6 * 4; //paletteDataSize, uiGpuDataAlignedSize, uiSkyMipmapVal, chunk header

                $hasHeader = ($rasterFormat & 0x20000);

                $end = $binary->current + $dataSize;

                $texels = [];
                $swizzleWidth = [];
                $swizzleHeight = [];
                $i = 0;
                while($binary->current < $end){

                    if ($i > 0) {
                        $width[] = $width[$i-1]/2;
                        $height[] = $height[$i-1]/2;
                    }

                    if ($hasHeader){
                        $binary->current += 8*4;
                        $swizzleWidth[] = $binary->consume(4, NBinary::INT_32);
                        $swizzleHeight[] = $binary->consume(4, NBinary::INT_32);
                        $binary->current += 6*4;
                        $dataSize = $binary->consume(4, NBinary::INT_32) * 0x10;
                        $binary->current += 3*4;
                    }else{
                        $swizzleWidth[] = $width[$i];
                        $swizzleHeight[] = $height[$i];
                        $dataSize = $height[$i]*$height[$i]*$depth/8;
                    }

                    $texels[] = $binary->consume($dataSize, NBinary::BINARY);

                    $i++;
                }

                $palette = false;
                if ($rasterFormat & 0x2000 || $rasterFormat & 0x4000) {
                    $unkh2 = 0;
                    $unkh3 = 0;
                    $unkh4 = 0;
                    if ($hasHeader){
                        $binary->current += 8*4;
                        $unkh2 = $binary->consume(4, NBinary::INT_32);
                        $unkh3 = $binary->consume(4, NBinary::INT_32);
                        $binary->current += 6*4;
                        $unkh4 = $binary->consume(4, NBinary::INT_32);
                        $binary->current += 3*4;
                    }

                    $paletteSize = ($rasterFormat & 0x2000) ? 0x100 : 0x10;
                    $palette = $binary->consume($paletteSize * 4, NBinary::BINARY);

                    if ($unkh2 == 8 && $unkh3 == 3 && $unkh4 == 6)
                        $binary->current += 0x20;
                }

                return [
                    'type' => 'texture',
                    'swizzleMask' => $swizzleHeight[0] != $height[0] ? 0x1 : 0x00,
                    'name' => $name,
                    'width' => $width[0],
                    'height' => $height[0],
                    'bitPerPixel' => $depth,
                    'rasterFormat' => $rasterFormat,
                    'alphaName' => $alphaName,
                    'palette' => $palette,
                    'data' => $texels[0],
                ];

        }

        return ['type' => 'na'];
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    public function unpack(NBinary $binary, $game, $platform)
    {
        $textures = [];
        do {
            $result = $this->readChunk($binary);

            switch ($result['type']){
                case 'texture':
                    $bmpRgba = $this->playstation->convertToRgba($result, MHT::PLATFORM_PS2);
                    $textures[ $result['name'] . '.png'] = (new Image())->rgbaToImage($bmpRgba, $result['width'], $result['height']);
                    break;
            }
        }while($binary->remain());

        return $textures;
    }

    public function pack($data, $game, $platform)
    {
        die("Packing it not supported.");
    }
}