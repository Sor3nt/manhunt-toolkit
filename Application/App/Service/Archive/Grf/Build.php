<?php
namespace App\Service\Archive\Grf;

use App\MHT;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Build {
    
    /** @var  NBinary */
    private $binary;

    private $game;

    public $keepOrder = true;

    public function build( Finder $files, $game )
    {
        $this->game = $game;
        $result = new NBinary();

        if ($game == MHT::GAME_MANHUNT_2){
            $result->write("GNIA", NBinary::STRING);
            $result->write(1, NBinary::INT_32);
        }


        $this->generateAreaBlock($files, $result);
        $this->generatePathBlock($files, $result);
        $this->generateNameBlock($files, $result);

        return $result->binary;
    }

    public function generateNameBlock(Finder $files, NBinary $result){
        $entries = [];

        $files->sortByName();

        foreach ($files as $file) {
            if (strpos($file->getFilenameWithoutExtension(), '#') === false) continue;
            $name = explode("#", $file->getFilenameWithoutExtension())[1];
            if (substr($name, 0, 5) === "area_"){
                $name = substr($name, 5);
                $entries[] = $name;
            }
        }


        $result->write(count($entries), NBinary::INT_32);
        foreach ($entries as $entry) {
            $result->write($entry, NBinary::STRING);
            $result->write("\x00", NBinary::BINARY);
            $result->write($result->getPadding( "\x70"), NBinary::BINARY);
        }
    }


    public function generatePathBlock(Finder $files, NBinary $output){
        $pathEntries = [];

        foreach ($files as $file) {
            if (strpos($file->getFilename(),  "path_") !== false){
                $pathEntries[] = \json_decode($file->getContents(), true);
            }
        }

        if ($this->keepOrder){
            usort($pathEntries, function( $a, $b){
                return $a['order'] > $b['order'];
            });
        }

        $output->write(count($pathEntries), NBinary::INT_32);

        foreach ($pathEntries as $pathEntry) {
            $output->write($pathEntry['name'], NBinary::STRING);
            $output->write("\x00", NBinary::BINARY);
            $output->write($output->getPadding( "\x70"), NBinary::BINARY);

            $this->writeBlock($output, $pathEntry['entries']);
        }
    }

    public function generateAreaBlock(Finder $files, NBinary $result){

        $areaEntries = [];
        foreach ($files as $file) {

            if (strpos($file->getFilename(),  "area_") !== false){
                $entries = \json_decode($file->getContents(), true);
                foreach ($entries as $entry) {
                    $areaEntries[] = $entry;
                }
            }

        }

        usort($areaEntries, function( $a, $b){
            return $a['linkId'] > $b['linkId'];
        });

        $result->write(count($areaEntries), NBinary::INT_32);

        foreach ($areaEntries as $areaEntry) {

            $result->write($areaEntry['id'], NBinary::STRING);
            $result->write("\x00", NBinary::BINARY);
            $result->write($result->getPadding( "\x70"), NBinary::BINARY);

            $result->write($areaEntry['groupIndex'], NBinary::INT_32);

            $result->writeXYZ($areaEntry['position']);

            $result->write($areaEntry['speed'], NBinary::FLOAT_32);

            $result->write($areaEntry['nodeName'], NBinary::STRING);
            $result->write("\x00", NBinary::BINARY);
            $result->write($result->getPadding( "\x70"), NBinary::BINARY);

            $this->writeBlock($result, $areaEntry['unknown']);

            if ($this->game == MHT::GAME_MANHUNT_2){
                $this->writeBlock($result, $areaEntry['unknown2']);
            }



            $this->writeWaypointBlock($result, $areaEntry['entries']);
        }
    }


    /**
     * @param NBinary $result
     * @param $data
     * @param string $type
     */
    private function writeBlock(NBinary $result,  $data, $type = NBinary::INT_32){

        $result->write(count($data), NBinary::INT_32);
        foreach ($data as $item) {
            $result->write($item, $type);
        }
    }

    /**
     * @param NBinary $result
     * @param $datas
     */
    private function writeWaypointBlock(NBinary $result, $datas){

        $result->write(count($datas), NBinary::INT_32);

        foreach ($datas as $data) {
            $result->write($data['linkId'], NBinary::INT_32);
            $result->write($data['type'], NBinary::INT_32);

            $this->writeBlock($result, $data['unknown']);
        }

        if ($this->game == MHT::GAME_MANHUNT_2){
            $result->write(0, NBinary::INT_32);
            $result->write(0, NBinary::INT_32);
        }
    }

}