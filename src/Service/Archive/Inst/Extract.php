<?php
namespace App\Service\Archive\Inst;

use App\Service\NBinary;

class Extract {

    private $binary;
    
    public function __construct( $binary )
    {
        $this->binary = new NBinary( $binary );
    }

    public function get(){

        // detect the platform
        $placementsBinary = $this->binary->get(4);

        if ($this->binary->unpack($placementsBinary, NBinary::INT_32) > 100000){
            $this->binary->numericBigEndian = true;
        }

        $placements = $this->binary->consume(4, NBinary::INT_32);

        $sizesLength = $placements * 4;

        //split sizes (header) from content
        $sizes = $this->binary->consume($sizesLength, NBinary::HEX);
        $sizes = str_split($sizes, 8);

        //extract every record
        $records = [];

        foreach ($sizes as $size) {
            $size = $this->binary->unpack(hex2bin($size), NBinary::INT_32);

            $block = new NBinary( $this->binary->consume($size, NBinary::BINARY) );

            $block->numericBigEndian = $this->binary->numericBigEndian;

            $records[] = $this->parseRecord( $block );
        }

        return $records;
    }


    private function parseRecord( NBinary $binary ){

        /**
         * Find the  Record
         */
        $glgRecord = $binary->getString();

        /**
         * Find the internal name
         */
        $internalName = $binary->getString();


        /**
         * Find the position and rotation
         */

        $x = $binary->consume(4, NBinary::FLOAT_32);
        $y = $binary->consume(4, NBinary::FLOAT_32);
        $z = $binary->consume(4, NBinary::FLOAT_32);


        $rotationX = $binary->consume(4, NBinary::FLOAT_32);
        $rotationY = $binary->consume(4, NBinary::FLOAT_32);
        $rotationZ = $binary->consume(4, NBinary::FLOAT_32);
        $rotationW = $binary->consume(4, NBinary::FLOAT_32);

        /**
         * Find the entity class
         */
        $entityClass = $binary->getString();


        /**
         * Find parameters
         */
        $params = [];

        while($binary->remain() > 0) {

            if ($binary->remain() >= 12){
                $maybeType  = trim($binary->get(4, 4));


                if (in_array($maybeType, [ 'flo', 'boo', 'str', 'int' ])){
                    $game = "mh2";
                }else{
                    $game = "mh1";
                }

            }else{
                $game = "mh1";

            }


            if ($game == "mh1"){
                while($binary->remain() > 0) {

                    $value  = $binary->consume(4, NBinary::INT_32);

                    $params[] = [
                        'value' => $value
                    ];
                }
            }else{

                $parameterId  = $binary->consume(4, NBinary::HEX);

                $type = $binary->consume(4, NBinary::STRING);

                // float, boolean, integer are always 4-byte long
                // string need to be calculated
                switch ($type) {
                    case 'flo':
                        $value = $binary->consume(4, NBinary::FLOAT_32);
                        break;
                    case 'boo':
                    case 'int':
                        $value = $binary->consume(4, NBinary::INT_32);
                        break;
                    case 'str':

                        $value = $binary->getString();

                        break;
                    default:
                        var_dump($internalName, $glgRecord);
                        die("type unknown " . $type);
                }

                $params[] = [
                    'parameterId' => $parameterId,
                    'type' => $type,
                    'value' => $value
                ];
            }




        };

        return [
            'record' => $glgRecord,
            'internalName' => $internalName,
            'entityClass' => $entityClass,
            'position' => [
                'x' => $x,
                'y' => $y * -1,
                'z' => $z * -1
            ],
            'rotation' => [
                'x' => $rotationX,
                'y' => $rotationY,
                'z' => $rotationZ,
                'w' => $rotationW,
            ],
            'parameters' => $params
        ];
    }

}