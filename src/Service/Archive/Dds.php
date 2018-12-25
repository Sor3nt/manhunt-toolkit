<?php
namespace App\Service\Archive;

use App\Service\Archive\Mls\Build;
use App\Service\Archive\Mls\Extract;
use App\Service\NBinary;

class Dds
{

    public function decode($data)
    {
        $data = new NBinary($data);

        return [
            'magic' => $data->consume(4, NBinary::BINARY),
            'size' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'flags' => $this->convertHeaderFlags($data->consume(4, NBinary::LITTLE_U_INT_32)),
            'height' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'width' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'pitchOrLinearSize' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'depth' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'mipMapCount' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'reserved' => $data->consume(11 * 4, NBinary::HEX),

            //pixel format

            'format_size' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'format_flags' => $this->readDDSPixelFormatFlags($data->consume(4, NBinary::LITTLE_U_INT_32)),
            'format' => $data->consume(4, NBinary::STRING),
            'RGBBitCount' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'RBitMask' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'GBitMask' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'BBitMask' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'ABitMask' => $data->consume(4, NBinary::LITTLE_U_INT_32),


            'caps' => $this->readHeaderCaps($data->consume(4, NBinary::LITTLE_U_INT_32)),
            'caps2' => $this->readHeaderCaps2($data->consume(4, NBinary::LITTLE_U_INT_32)),
            'caps3' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'caps4' => $data->consume(4, NBinary::LITTLE_U_INT_32),
            'reserved2' => $data->consume(4, NBinary::LITTLE_U_INT_32),


            'data' => $data->binary

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