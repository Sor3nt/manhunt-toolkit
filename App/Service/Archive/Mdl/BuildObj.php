<?php
namespace App\Service\Archive\Mdl;

use App\Service\NBinary;

class BuildObj {

    private $offsets = [];
    private $offsetTable = [];

    public function build( $mdls ){

        $binary = new NBinary();
        foreach ($mdls as $mdlIndex => $mdl) {

            if (count($mdl['objects'])){

                foreach ($mdl['objects'] as $index => $object) {

//                    if ($object['boneTransDataIndex']){
//
//                        foreach ($object['boneTransDataIndex']['matrix'] as $matrix) {
//                            $binary->write($matrix, NBinary::HEX);
//                        }
//
//                    }

                    $this->createObject($binary, $object['object']);

                }

            }


            file_put_contents("test.obj", $binary->binary);
            exit;
            return $binary->binary;

        }

    }



    private function createObject(NBinary $binary, $object ){


        foreach ($object['vertex'] as $vertex) {
            $binary->write('v ', NBinary::BINARY);
            $binary->write(round($vertex['x'],4), NBinary::BINARY);
            $binary->write(',', NBinary::BINARY);
            $binary->write(round($vertex['y'],4), NBinary::BINARY);
            $binary->write(',', NBinary::BINARY);
            $binary->write(round($vertex['z'],4), NBinary::BINARY);
            $binary->write("\n", NBinary::BINARY);
        }

        $faces = array_chunk($object['faceindex'], 3);

        foreach ($faces as $index => $facePairs) {
            $vx = $facePairs[0] / 32768;
            $vy = $facePairs[1] / 32768;
            $vz = $facePairs[2] / 32768;

            var_dump($vx);
            var_dump($vy);
            var_dump($vz);
            exit;
        }

//var_dump($object['faceindex']);
//exit;
        foreach ($faces as $index => $facePair) {

            $binary->write('s ', NBinary::BINARY);
            $binary->write(($index + 1) . "\n", NBinary::BINARY);

            $binary->write('f ', NBinary::BINARY);

            $faces = array_chunk($facePair, 3);
            foreach ($faces as $face) {
                $binary->write($face[0], NBinary::BINARY);
                $binary->write('/', NBinary::BINARY);
                $binary->write($face[1], NBinary::BINARY);
                $binary->write('/', NBinary::BINARY);
                $binary->write($face[2], NBinary::BINARY);
                $binary->write(' ', NBinary::BINARY);

            }

            $binary->write("\n", NBinary::BINARY);
        }

    }


    private function createNormal($normal ){
        $binary = new NBinary();
        $binary->write($normal['x'], NBinary::INT_16);
        $binary->write($normal['y'], NBinary::INT_16);
        $binary->write($normal['z'], NBinary::INT_16);
        $binary->write(0, NBinary::INT_16);

        return $binary;
    }


}
