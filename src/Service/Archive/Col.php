<?php
namespace App\Service\Archive;

use App\Service\NBinary;

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

        if (!$input instanceof NBinary) return false;

        if (
            (strpos($input->binary, "min") !== false) &&
            (strpos($input->binary, "max") !== false) &&
            (strpos($input->binary, "center") !== false)
        ) return true;

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
            $result['faces']     = $this->parseSimpleBlock($binary);

            $results[] = $result;

            $entryCount--;
        }

        return $results;
    }

    /**
     * @param NBinary $binary
     * @return array
     */
    public function parseSimpleBlock( NBinary $binary){

        $count = $binary->consume(4, NBinary::INT_32);

        if ($count > 0){
            $values = [];
            while($count > 0){

                $values[] = $binary->readXYZ();

                $count--;
            }

            return $values;
        }

        return [];
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $binary, $game, $platform ){

        $records = \json_decode($binary->binary, true);

        $binary = new NBinary();
        $binary->write(count($records), NBinary::INT_32);

        foreach ($records as $record) {

            $binary->write($record['name'] . "\x00", NBinary::BINARY);
            $binary->write($binary->getPadding("\x70"), NBinary::BINARY);
//return $binary->binary;

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
                $binary->writeXYZ($face);
            }

        }

        return $binary->binary;
    }

}