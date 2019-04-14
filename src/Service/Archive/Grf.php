<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Grf extends Archive {
    public $name = 'AI Map Path';

    public static $supported = 'grf';


    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){


        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $fourCC = $binary->consume(4, NBinary::BINARY);
        $const = $binary->consume(4, NBinary::INT_32);

        $entryCount = $binary->consume(4, NBinary::INT_32);
        $entries = [];

        for($i = 0; $i < $entryCount; $i++){

            $name = $binary->getString();
            $type = $binary->consume(4, NBinary::INT_32);


            $position = $binary->readXYZ();

            $always3f = $binary->consume(4, NBinary::FLOAT_32);

            $commandName = $binary->getString();

            $zero = $binary->consume(4, NBinary::INT_32);


            $unknownCount = $binary->consume(4, NBinary::INT_32);
            $unknown = [];
            for($x = 0; $x < $unknownCount; $x++){
                $unknown[] = $binary->consume(4, NBinary::INT_32);
            }

            $unknownMatrixCount = $binary->consume(4, NBinary::INT_32);
            $unknownMatrix = [];
            for($x = 0; $x < $unknownMatrixCount; $x++){
                $unknownMatrix[] = $binary->readXYZ(4, NBinary::INT_32);
            }

            $unknownNumber = $binary->consume(4, NBinary::INT_32);
            $zero2 = $binary->consume(4, NBinary::INT_32);

//            if ($unknownNumber != 0) var_dump($unknownMatrix, $unknownNumber, "\n");

            //todo hack, something is wrong....
            $isZero = $binary->consume(4, NBinary::INT_32);

            if ($isZero != 0){
                $binary->current -= 4;
            }

            $entries[] = [
                'name' => $name,
                'commandName' => $commandName,
                'unknownNumber' => $unknownNumber,
                'type' => $type,
                'xyz' => $position,
                'unknown' => $unknown,
                'unknownMatrix' => $unknownMatrix,
            ];

        }


        $offsetCount = $binary->consume(4, NBinary::INT_32);
        $offsets = [];
        for($i = 0; $i < $offsetCount; $i++){
            $offset = [
                'name' => $binary->getString(),
                'entries' => []
            ];

            $entryCount = $binary->consume(4, NBinary::INT_32);
            for($x = 0; $x < $entryCount; $x++){
                $index = $binary->consume(4, NBinary::INT_32);
                $entry = $entries[$index - 1];

                foreach ($entry['unknownMatrix'] as &$matrix) {
                    $matrix[0] = [
                        'index' => $index - 1,
                        'type' => $entries[$matrix[0] - 1]['type'],
                        'xyz' => $entries[$matrix[0] - 1]['xyz']

                    ];
                }

                $offset['entries'][] = $entry;
            }

            $offsets[] = $offset;
        }


        $results = [];
        foreach ($offsets as $offset) {
            $results[ $offset['name'] . '.json' ] = $offset;
        }

        return $results;
    }


    /**
     * @param Finder $pathFilename
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $pathFilename, $game, $platform ){

        $binary = new NBinary();
        $binary->write($pathFilename->count(), NBinary::INT_32);


        return $binary->binary;
    }
}