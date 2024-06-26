<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Col extends Archive {
    public $name = 'Collision Matrix';

    public static $supported = 'col';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){

        if (!$input instanceof Finder) return false;

        foreach ($input as $file) {
            $ext = strtolower($file->getExtension());
            if ($ext !== "json") return false;

            return strpos($file->getContents(), 'center') !== false && strpos($file->getContents(), 'radius') !== false && strpos($file->getContents(), 'min') !== false && strpos($file->getContents(), 'max') !== false;
        }

        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $entryCount = $binary->consume(4, NBinary::INT_32);

        $results = [];

        while ($entryCount > 0){

            $name = $binary->getString();

            $result = [
                'name' => $name,
                'center' => $binary->readXYZ(),
                'radius' => $binary->consume(4, NBinary::FLOAT_32),
                'min' => $binary->readXYZ(),
                'max' => $binary->readXYZ()
            ];

            $spheresCount = $binary->consume(4, NBinary::INT_32);

            $result['spheres'] = [];
            if ($spheresCount > 0){

                while($spheresCount > 0){

                    $sphere = [];
                    $sphere['center'] = $binary->readXYZ();
                    $sphere['radius'] = $binary->consume(4, NBinary::FLOAT_32);

                    $sphere['surface'] = [
                        'material' => $binary->consume(1, NBinary::INT_8),
                        'flag' => $binary->consume(1, NBinary::INT_8),
                        'brightness' => $binary->consume(1, NBinary::INT_8),
                        'light' => $binary->consume(1, NBinary::INT_8),
                    ];

                    $result['spheres'][] = $sphere;
                    $spheresCount--;
                }

            }

            $linesCount = $binary->consume(4, NBinary::INT_32);

            $result['lines'] = [];
            if ($linesCount > 0){

                $lines = [];
                while($linesCount > 0){
                    $linePack = [];
                    for ($i = 0; $i < 2; $i++){

                        $linePack[] = $binary->readXYZ();

                    }

                    $lines[] = $linePack;
                    $linesCount--;
                }

                $result['lines'] = $lines;
            }


            $boxesCount = $binary->consume(4, NBinary::INT_32);

            $result['boxes'] = [];
            if ($boxesCount > 0){
                var_dump($name);
                die("boxesCount todo");
            }

            $result['verticals'] = $this->parseSimpleBlock($binary);
            $result['faces']     = $this->parseSimpleBlock($binary, NBinary::INT_32);

            $results[$name . '.json'] = $result;

            $entryCount--;
        }

        return $results;
    }

    public function swapEndianness($hex) {
        return implode('', array_reverse(str_split($hex, 2)));
    }

    /**
     * @param NBinary $binary
     * @return array
     */
    public function parseSimpleBlock( NBinary $binary, $format = NBinary::FLOAT_32){

        $count = $binary->consume(4, NBinary::INT_32);

        //$binary->numericBigEndian = true;

        if ($count > 0){
            $values = [];
            while($count > 0){

                $values[] = $binary->readXYZ(4, $format);

                $count--;
            }

            return $values;
        }
        $binary->numericBigEndian = false;

        return [];
    }

    /**
     * @param array|Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform ){

        if ($pathFilename instanceof Finder){
            $pathFilename = $pathFilename->sortByName();
        }

        $binary = new NBinary();
        if ($pathFilename instanceof Finder) {
            $binary->write($pathFilename->count(), NBinary::INT_32);
        }else{
            $binary->write(count($pathFilename), NBinary::INT_32);
        }

        foreach ($pathFilename as $file) {
            if ($file instanceof SplFileInfo){
                $record = \json_decode($file->getContents(), true);
            }else{
                $record = $file;
            }

            $binary->write($record['name'] . "\x00", NBinary::BINARY);
            $binary->write($binary->getPadding("\x70"), NBinary::BINARY);

            //bounds
            $binary->writeXYZ($record['center']);
            $binary->write($record['radius'], NBinary::FLOAT_32);
            $binary->writeXYZ($record['min']);
            $binary->writeXYZ($record['max']);

            $binary->write(count($record['spheres']), NBinary::INT_32);

            foreach ($record['spheres'] as $sphere) {

                $binary->writeXYZ($sphere['center']);
                $binary->write($sphere['radius'], NBinary::FLOAT_32);

                $binary->write($sphere['surface']['material'], NBinary::INT_8);
                $binary->write($sphere['surface']['flag'], NBinary::INT_8);
                $binary->write($sphere['surface']['brightness'], NBinary::INT_8);
                $binary->write($sphere['surface']['light'], NBinary::INT_8);
            }

            $binary->write(count($record['lines']), NBinary::INT_32);
            foreach ($record['lines'] as $linePack) {
                foreach ($linePack as $line) {
                    $binary->writeXYZ($line);
                }
            }

            $binary->write(count($record['boxes']), NBinary::INT_32);

            if (count($record['boxes']) > 0){
                die("box todo");
            }

            $binary->write(count($record['verticals']), NBinary::INT_32);
            foreach ($record['verticals'] as $vertical) {
                $binary->writeXYZ($vertical);
            }

            $binary->write(count($record['faces']), NBinary::INT_32);
            foreach ($record['faces'] as $face) {
                $binary->writeXYZ($face, NBinary::INT_32);
            }
        }

        return $binary->binary;
    }

}