<?php
namespace App\Service\Archive;

use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Dff extends Archive {

    public $name = '3D Models (Manhunt 1)';

    public static $supported = 'dff';

    /**
     * @param $pathFilename
     * @param $input
     * @param $game
     * @param $platform
     * @return bool
     */
    public static function canPack( $pathFilename, $input, $game, $platform ){


        if (!$input instanceof Finder) return false;
        if($input->files()->count() == 0) return false;

        foreach ($input as $file) {
            $extension = strtolower($file->getExtension());

            if ($extension !== "dff") return false;
        }

        return true;
    }


    private $offset = 0;

    /**
     * @param NBinary $binary
     * @return array
     */
    private function getBlock( NBinary $binary ){

        $id = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        //Dummy
        $binary->consume(4, NBinary::BINARY);

        return [ $id, $size ];
    }

    /**
     * @param NBinary $binary
     * @return array
     * @throws \Exception
     */
    private function getEntry( NBinary $binary ){
        list(, $size) = $this->getBlock($binary);
        $size += 12;

        list(, $structSize) = $this->getBlock($binary);

        $binary->jumpTo($structSize + 12, false);

        list(, $struct2Size) = $this->getBlock($binary);

        $binary->jumpTo($struct2Size, false);

        $this->getBlock($binary);
        list($id, $nSize) = $this->getBlock($binary);

        $name = false;
        if ($id == 3){
            list($sId, $sSize) = $this->getBlock($binary);

            if ($sId == 286){
                $binary->jumpTo($sSize, false);

                list(, $s2Size) = $this->getBlock($binary);

                $name = $binary->consume($s2Size, NBinary::STRING);
            }else{
                $name = $binary->consume($sSize, NBinary::STRING);
            }
        }else if ($id == 286){
            $binary->jumpTo($nSize, false);

            list($s2Id, $s2Size) = $this->getBlock($binary);

            if ($s2Id == 3){
                list($s3Id, $s3Size) = $this->getBlock($binary);

                if ($s3Id == 286){
                    $binary->jumpTo($s3Size, false);

                    list(, $s4Size) = $this->getBlock($binary);

                    $name = $binary->consume($s4Size, NBinary::STRING);
               }else{
                    $name = $binary->consume($s3Size, NBinary::STRING);
               }

            }else{
                $name = $binary->consume($s2Size, NBinary::STRING);
            }

        }else if ($id == 39056126){
            $name = $binary->consume($nSize, NBinary::STRING);
        }

        if ($name == false){
            throw new \Exception('Name not found!');
        }

        return [
            'name' => $name,
            'offset' => $this->offset,
            'size' => $size
        ];

    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     */
    public function unpack(NBinary $binary, $game, $platform){

        $fileSIZE = $binary->length();

        $results = [];

        do{
            $binary->jumpTo($this->offset);

            $entry = $this->getEntry($binary);

            $binary->jumpTo($entry['offset']);
            $entry['data'] = $binary->consume($entry['size'], NBinary::BINARY);

            $this->offset += $entry['size'];

            $results[ $entry['name'] ] = $entry['data'];

        }while( $this->offset < $fileSIZE );

        return $results;
    }

    private function prepareData( Finder $finder ){

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getContents();
        }

        return $files;
    }

    /**
     * @param $files
     * @param $game
     * @param $platform
     * @return string
     */
    public function pack( $files, $game, $platform ){

        $files = $this->prepareData($files);

        $binary = "";

        foreach ($files as $data) {
            $binary .= $data;
        }

        return $binary;
    }



}