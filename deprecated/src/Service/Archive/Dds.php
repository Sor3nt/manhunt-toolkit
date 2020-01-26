<?php
namespace App\Service\Archive;

use App\Service\NBinary;

class Dds extends Archive
{
    public $name = 'DirectDraw Surface';

    public static $supported = 'dds';

    /**
     * @param $data
     * @param $game
     * @param $platform
     * @throws \Exception
     */
    public function pack($data, $game, $platform){
        throw new \Exception('Packing of DDS is not implemented');
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform)
    {

        return [
            'magic' => $binary->consume(4, NBinary::BINARY),
            'size' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'flags' => $this->convertHeaderFlags($binary->consume(4, NBinary::LITTLE_U_INT_32)),
            'height' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'width' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'pitchOrLinearSize' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'depth' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'mipMapCount' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'reserved' => $binary->consume(11 * 4, NBinary::HEX),

            //pixel format

            'format_size' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'format_flags' => $this->readDDSPixelFormatFlags($binary->consume(4, NBinary::LITTLE_U_INT_32)),
            'format' => $binary->consume(4, NBinary::STRING),
            'RGBBitCount' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'RBitMask' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'GBitMask' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'BBitMask' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'ABitMask' => $binary->consume(4, NBinary::LITTLE_U_INT_32),


            'caps' => $this->readHeaderCaps($binary->consume(4, NBinary::LITTLE_U_INT_32)),
            'caps2' => $this->readHeaderCaps2($binary->consume(4, NBinary::LITTLE_U_INT_32)),
            'caps3' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'caps4' => $binary->consume(4, NBinary::LITTLE_U_INT_32),
            'reserved2' => $binary->consume(4, NBinary::LITTLE_U_INT_32),


            'data' => $binary->consume( $binary->remain(), NBinary::BINARY)

        ];

    }


    private function readHeaderCaps($caps)
    {
        if ($caps == 0) {
            return "";
        }


        $flagsFound = 0;

        for ($i = 0; $i < 32; $i++) {
            $flag = $caps & (1 << $i);

            if ($flag != 0) {
                $flagsFound++;
            }
        }

        $results = [];

        for ($i = 0; $i < 32; $i++) {
            $flag = $caps & (1 << $i);

            switch ($flag) {
                case 0x8:
                    $results[] = "DDSCAPS_COMPLEX";
                    break;
                case 0x400000:
                    $results[] = "DDSCAPS_MIPMAP";
                    break;
                case 0x1000:
                    $results[] = "DDSCAPS_TEXTURE";
                    break;
            }

        }

        return $results;
    }


    private function readHeaderCaps2($caps)
    {

        if ($caps == 0) {
            return "";
        }


        $flagsFound = 0;

        for ($i = 0; $i < 32; $i++) {
            $flag = $caps & (1 << $i);

            if ($flag != 0) {
                $flagsFound++;
            }
        }

        $results = [];

        for ($i = 0; $i < 32; $i++) {
            $flag = $caps & (1 << $i);

            switch ($flag) {
                case 0x200:
                    $results[] = "DDSCAPS2_CUBEMAP";
                    break;
                case 0x400:
                    $results[] = "DDSCAPS2_CUBEMAP_POSITIVEX";
                    break;
                case 0x800:
                    $results[] = "DDSCAPS2_CUBEMAP_NEGATIVEX";
                    break;
                case 0x1000:
                    $results[] = "DDSCAPS2_CUBEMAP_POSITIVEY";
                    break;
                case 0x2000:
                    $results[] = "DDSCAPS2_CUBEMAP_NEGATIVEY";
                    break;
                case 0x4000:
                    $results[] = "DDSCAPS2_CUBEMAP_POSITIVEZ";
                    break;
                case 0x8000:
                    $results[] = "DDSCAPS2_CUBEMAP_NEGATIVEZ";
                    break;
                case 0x200000:
                    $results[] = "DDSCAPS2_VOLUME";
                    break;
            }

        }

        return $results;
    }

    private function readDDSPixelFormatFlags($flags)
    {

        if ($flags == 0) {
            return "";
        }


        $flagsFound = 0;

        for ($i = 0; $i < 32; $i++) {
            $flag = $flags & (1 << $i);

            if ($flag != 0) {
                $flagsFound++;
            }
        }

        $results = [];

        for ($i = 0; $i < 32; $i++) {
            $flag = $flags & (1 << $i);

            switch ($flag) {
                case 0x1:
                    $results[] = "DDPF_ALPHAPIXELS";
                    break;
                case 0x2:
                    $results[] = "DDPF_ALPHA";
                    break;
                case 0x4:
                    $results[] = "DDPF_FOURCC";
                    break;
                case 0x40:
                    $results[] = "DDPF_RGB";
                    break;
                case 0x200:
                    $results[] = "DDPF_YUV";
                    break;
                case 0x20000:
                    $results[] = "DDPF_LUMINANCE";
                    break;
            }

        }

        return $results;

    }

    private function convertHeaderFlags($flags)
    {
        if ($flags == 0) {
            return "";
        }

        $flagsFound = 0;

        for ($i = 0; $i < 32; $i++) {
            $flag = $flags & (1 << $i);

            if ($flag != 0) {
                $flagsFound++;
            }
        }

        $results = [];

        for ($i = 0; $i < 32; $i++) {
            $flag = $flags & (1 << $i);

            switch ($flag) {
                case 0x1:
                    $results[] = "DDSD_CAPS";
                    break;
                case 0x2:
                    $results[] = "DDSD_HEIGHT";
                    break;
                case 0x4:
                    $results[] = "DDSD_WIDTH";
                    break;
                case 0x8:
                    $results[] = "DDSD_PITCH";
                    break;
                case 0x1000:
                    $results[] = "DDSD_PIXELFORMAT";
                    break;
                case 0x20000:
                    $results[] = "DDSD_MIPMAPCOUNT";
                    break;
                case 0x80000:
                    $results[] = "DDSD_LINEARSIZE";
                    break;
                case 0x800000:
                    $results[] = "DDSD_DEPTH";
                    break;
            }

        }

        return $results;
    }


}