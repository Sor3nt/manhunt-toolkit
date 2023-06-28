<?php

namespace App\Service\Archive;

use App\MHT;
use App\Service\Helper;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Font extends Archive
{

    public $name = 'Font Coordinates';

    public static $validationMap = [
        [0, 6, NBinary::STRING, ['<FONT>']]
    ];


    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack($pathFilename, $input, $game, $platform)
    {

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {

            if (strpos($file->getPathname(), "FONT#DAT") !== false)
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
        $fonts = [];
        do {
            $binary->current += 6; //skip <FONT>

            $charCount = $binary->consume(4, NBinary::INT_32);
            $matrixCount = $binary->consume(4, NBinary::INT_32, 8); //skip zero and font index
            $font = [
                'unkInt' => $binary->consume(4, NBinary::INT_32),
                //center...
                'fontHeight' => $binary->consume(4, NBinary::FLOAT_32),
                'charInfoTable' => [],
            ];

            //a table of keycodes; if -1 its not mapped...
            $charTable = [];
            for ($i = 0; $i <= $charCount; $i++) {
                $char = $binary->consume(4, NBinary::INT_32);
                if ($char === 4294967295) $char = -1; //todo we hit some max signed int  issues here -.-

                $charTable[] = $char;
            }

            for ($i = 0; $i < $matrixCount; $i++) {

                //todo change this to get utf-16 support as well...
                $codeHex = $binary->consume(1, NBinary::HEX);
                $binary->current += 3;

                $info = [
//                    'index' => $charTable[$code],
//                    'info' => $code > 31 && $code < 126 ? hex2bin($codeHex) : 'np', //np = not printable char
                    'code' => "0x" . $codeHex,

                    //note width is calculated by "(x2 - x1) / 2)"
                    //its not width... its center
                    'width' => $binary->consume(4, NBinary::FLOAT_32),
                    'position' => [
                        //skip "width" value
                        'x1' => $binary->consume(4, NBinary::FLOAT_32),
                        'y1' => $binary->consume(4, NBinary::FLOAT_32),
                        'x2' => $binary->consume(4, NBinary::FLOAT_32),
                        'y2' => $binary->consume(4, NBinary::FLOAT_32)
                    ]
                ];

                $font['charInfoTable'][] = $info;
            }

            $fonts[] = $font;
        }while($binary->remain());

        return $fonts;
    }

    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return array
     */
    public function pack($pathFilename, $game, $platform)
    {
        $maxWidth = 256;
        $return = [];

        $font = [];
        foreach ($pathFilename as $file) {

            $fontIndex = explode("FONT#DAT" . DIRECTORY_SEPARATOR, $file->getPathname())[1];
            $fontIndex = explode(DIRECTORY_SEPARATOR, $fontIndex)[0];
            $fontIndex = (int)str_replace('font', '', $fontIndex);

            $code = explode(".", $file->getFilename())[0];
            $size = getimagesize($file->getPathname());

            $font[$fontIndex][$code] = [
                'file' => $file->getPathname(),
                'codeHex' => str_replace("0x", '', $code) . '000000',
                'width' => $size[0],
                'height' => $size[1],
            ];
        }

        sort($font);

        $result = new NBinary();
        foreach ($font as $fontIndex => $fontChars) {
            ksort($fontChars);

            $result->concat($this->createFont($fontIndex, $fontChars, $maxWidth));

            $texture = $this->getTexture($fontChars, $maxWidth);

            $fileNames = [0 => "t16plus.png", 1 => "font2.png", 2 => "font1.png"];
            $return[$fileNames[$fontIndex]] = $texture;
        }

        $return['FONT.DAT'] = $result->binary;
        return $return;
    }

    private function createFont(int $fontId, array $chars, int $maxWidth = 256)
    {
        $font = new NBinary();
        list($maxWidth, $maxHeight) = $this->getTextureResolution($chars, $maxWidth);
        $charKeys = array_keys($chars);
        $asDec = hexdec(str_replace('0x', '', end($charKeys) ));

        //Write header
        {
            $font->write('<FONT>', NBinary::STRING);
//            $font->write(count($chars), NBinary::INT_32);
            $font->write($asDec, NBinary::INT_32);
            $font->write(0, NBinary::INT_32);
            $font->write($fontId, NBinary::INT_32);

            $font->write(count($chars), NBinary::INT_32);

            //todo: what is this ?!
            $unknownFontCode = [0 => 26, 1 => 30, 2 => 31];
            $font->write($unknownFontCode[$fontId], NBinary::INT_32);
            $font->write($this->getMaxCharHeight($chars) / 2 / $maxHeight, NBinary::FLOAT_32);
        }

        //Write usage matrix
        {

            $usedIndex = 0;
            for($keyCode = 0; $keyCode <= $asDec; $keyCode++){
                $hexKeyCode = sprintf('%02x', $keyCode);

                //has the currenct keycode a image ?
                if (isset($chars['0x' . $hexKeyCode])){
                    $font->write($usedIndex, NBinary::INT_32);
                    $usedIndex++;
                }else{
                    //todo should be -1 uint32 ?
                    $font->write(4294967295, NBinary::INT_32);
                }
            }
        }

        //Write character matrix
        $this->loopCharsRespectWidth($chars, $maxWidth, function (int $x, int $y, array $settings) use ($font, $maxWidth, $maxHeight)  {
            $font->write($settings['codeHex'], NBinary::HEX);

            //center of the char
            $font->write(($settings['width'] / 2) / $maxWidth, NBinary::FLOAT_32);

            //x1,y1,x2,y2
            $font->write($x / $maxWidth, NBinary::FLOAT_32);
            $font->write($y / $maxHeight, NBinary::FLOAT_32);
            $font->write(($x + $settings['width']) / $maxWidth, NBinary::FLOAT_32);
            $font->write(($y + $settings['height']) / $maxHeight, NBinary::FLOAT_32);
        });

        return $font;
    }


    private function prepareAlpha($resource)
    {
        imageAlphaBlending($resource, true);
        imageSaveAlpha($resource, true);

        imagefill( $resource,
            0,
            0,
            imagecolorallocatealpha( $resource, 0, 0, 0, 127 ));
    }

    private function getTexture(array $chars, int $maxWidth = 256): string
    {
        list($maxWidth, $maxHeight) = $this->getTextureResolution($chars, $maxWidth);

        $image = imagecreatetruecolor($maxWidth, $maxHeight);
        $this->prepareAlpha($image);

        $this->loopCharsRespectWidth($chars, $maxWidth, function (int $x, int $y, array $settings) use ($image, $maxWidth, $maxHeight)  {
            $charImage = imagecreatefrompng($settings['file']);
            $this->prepareAlpha($charImage);
            imagecopy($image, $charImage, $x, $y, 0, 0, $settings['width'], $settings['height']);
        });

        ob_start();
        imagepng($image, null, 0);
        $image_data = ob_get_contents();
        ob_end_clean();

        return $image_data;
    }

    /**
     * @param array $chars
     * @param int $maxWidth
     * @return int[]
     */
    private function getTextureResolution(array $chars, int $maxWidth = 256) : array
    {
        $maxHeight = 0;

        $this->loopCharsRespectWidth($chars, $maxWidth, function (int $x, int $y, array $settings) use (&$maxHeight)  {
            $maxHeight = $y;
        });

        if ($maxHeight < 256)
            return [$maxWidth, 256];

        return [$maxWidth, 512];
    }

    /**
     * @param array $chars
     * @return int
     */
    private function getMaxCharHeight(array $chars ) : int
    {
        return max(array_column($chars, 'height'));
    }


    private function loopCharsRespectWidth( array $chars, int $width, callable $callback)
    {
        $x = 1;
        $y = 1;
        $maxCharHeight = $this->getMaxCharHeight($chars);

        foreach ($chars as $settings) {
            if ($x + $settings['width'] >= $width){
                $x = 1;
                $y += $settings['height'];
            }

            $callback($x, $y, $settings);
            $x += $settings['width'];
        }
    }
}
