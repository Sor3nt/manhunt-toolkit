<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\Binary;
use App\Service\NBinary;
use Symfony\Component\Console\Output\OutputInterface;

class Col extends Archive {
    public $name = 'Collision Matrix';

    public static $supported = 'col';

    /**
     * @param $pathFilename
     * @param NBinary $input
     * @param null $game
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game = null ){

        if (!$input instanceof NBinary) return false;

        if (
            (strpos($input->binary, "min") !== false) &&
            (strpos($input->binary, "max") !== false) &&
            (strpos($input->binary, "center") !== false)
        ) return true;

        return false;
    }



    private $game = false;

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

    private function toInt8($hex){
        return is_int($hex) ? pack("c", $hex) :  current(unpack("c", hex2bin($hex)));
    }

    private function toInt16($hex){
        return is_int($hex) ? pack("s", $hex) : current(unpack("s", hex2bin($hex)));
    }

    private function substr(&$hex, $start, $end){

        $result = substr($hex, $start * 2, $end * 2);
        $hex = substr($hex, $end * 2);
        return $result;

    }

    public function unpack(NBinary $binary, $game = null){

        $data = $binary->hex;

        $entryCount = $this->toInt($this->substr($data, 0, 4));

        $results = [];

        while ($entryCount > 0){

            $result = [];

            $name = $this->substr($data, 0, mb_strpos(hex2bin($data), "\x00"));
            $name = $this->toString($name);

            $result['name'] = $name;

            $misssed = 4 - strlen($name) % 4;
            $this->substr($data, 0, $misssed);

            $x = $this->toFloat(Helper::toBigEndian($this->substr($data, 0, 4)));
            $y = $this->toFloat(Helper::toBigEndian($this->substr($data, 0, 4)));
            $z = $this->toFloat(Helper::toBigEndian($this->substr($data, 0, 4)));

            $result['center'] = [$x, $y, $z];
            $result['radius'] = $this->toFloat($this->substr($data, 0, 4));

            foreach (['min', 'max'] as $section) {
                $x = $this->toFloat($this->substr($data, 0, 4));
                $y = $this->toFloat($this->substr($data, 0, 4));
                $z = $this->toFloat($this->substr($data, 0, 4));

                $result[$section] = [$x, $y, $z];
            }


            $spheresCount = $this->toInt($this->substr($data, 0, 4));

            $result['spheres'] = [];
            if ($spheresCount > 0){


                $spheres = [];
                while($spheresCount > 0){

                    $sphere = [];

                    $x = $this->toFloat($this->substr($data, 0, 4));
                    $y = $this->toFloat($this->substr($data, 0, 4));
                    $z = $this->toFloat($this->substr($data, 0, 4));

                    $sphere['center'] = [$x, $y, $z];
                    $sphere['radius'] = $this->toFloat($this->substr($data, 0, 4));

                    $sphere['surface'] = [
                        'material' => $this->toInt8($this->substr($data, 0, 1)),
                        'flag' => $this->toInt8($this->substr($data, 0, 1)),
                        'brightness' => $this->toInt8($this->substr($data, 0, 1)),
                        'light' => $this->toInt8($this->substr($data, 0, 1))
                    ];

                    $spheresCount--;
                }

                $result['spheres'] = $spheres;
            }



            $linesCount = $this->toInt($this->substr($data, 0, 4));

            $result['lines'] = [];
            if ($linesCount > 0){


                $lines = [];
                while($linesCount > 0){
                    $linePack = [];
                    for ($i = 0; $i < 2; $i++){
                        $x = $this->toFloat($this->substr($data, 0, 4));
                        $y = $this->toFloat($this->substr($data, 0, 4));
                        $z = $this->toFloat($this->substr($data, 0, 4));

                        $linePack[] = [$x, $y, $z];

                    }

                    $lines[] = $linePack;
                    $linesCount--;
                }

                $result['lines'] = $lines;
            }


            $boxesCount = $this->toInt($this->substr($data, 0, 4));

            $result['boxes'] = [];
            if ($boxesCount > 0){
                var_dump($name);
                die("boxesCount todo");
            }


            $vertexCount = $this->toInt($this->substr($data, 0, 4));

            $result['verticals'] = [];
            if ($vertexCount > 0){

                $verticals = [];
                while($vertexCount > 0){
                    $x = $this->toFloat($this->substr($data, 0, 4));
                    $y = $this->toFloat($this->substr($data, 0, 4));
                    $z = $this->toFloat($this->substr($data, 0, 4));

                    $verticals[] = [$x, $y, $z];

                    $vertexCount--;
                }

                $result['verticals'] = $verticals;

            }


            $facesCount = $this->toInt($this->substr($data, 0, 4));

            $result['faces'] = [];
            if ($facesCount > 0){

                $faces = [];
                while($facesCount > 0){
                    $unknown1 = $this->toInt($this->substr($data, 0, 4));
                    $unknown2 = $this->toInt($this->substr($data, 0, 4));
                    $unknown3 = $this->toInt($this->substr($data, 0, 4));

                    $faces[] = [$unknown1, $unknown2, $unknown3];

                    $facesCount--;
                }

                $result['faces'] = $faces;

            }

            $results[] = $result;

            $entryCount--;
        }

        return $results;
    }

    public function pack( $records, $game = null ){

        $data = "";
        $data .= Helper::fromIntToHex(count($records));

        foreach ($records as $record) {

            $name = current(unpack("H*", $record['name'])) . "00";
            $name .= str_repeat('70', (4 - strlen($name) % 4) / 2);
            $data .= $name;


            $data .= Helper::toLittleEndian(Helper::fromFloatToHex( $record['center'][0] ));
            $data .= Helper::toLittleEndian(Helper::fromFloatToHex( $record['center'][1] ));
            $data .= Helper::toLittleEndian(Helper::fromFloatToHex( $record['center'][2] ));

            $data .= Helper::fromFloatToHex( $record['radius'] );

            $data .= Helper::fromFloatToHex( $record['min'][0] );
            $data .= Helper::fromFloatToHex( $record['min'][1] );
            $data .= Helper::fromFloatToHex( $record['min'][2] );

            $data .= Helper::fromFloatToHex( $record['max'][0] );
            $data .= Helper::fromFloatToHex( $record['max'][1] );
            $data .= Helper::fromFloatToHex( $record['max'][2] );


            $data .= Helper::fromFloatToHex( count($record['spheres']) );
            foreach ($record['spheres'] as $sphere) {

                $data .= Helper::fromFloatToHex( $sphere['center'][0] );
                $data .= Helper::fromFloatToHex( $sphere['center'][1] );
                $data .= Helper::fromFloatToHex( $sphere['center'][2] );

                $data .= Helper::fromFloatToHex( $sphere['radius'] );

                $data .= $this->toInt8($sphere['surface']['material'] );
                $data .= $this->toInt8($sphere['surface']['flag'] );
                $data .= $this->toInt8($sphere['surface']['brightness'] );
                $data .= $this->toInt8($sphere['surface']['light'] );
            }

            var_dump($data);


//            var_dump($data);
            exit;

        }



    }

}