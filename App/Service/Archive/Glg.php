<?php
namespace App\Service\Archive;

use App\MHT;
use App\Service\Archive\Glg\EntityTypeData;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class Glg extends Archive {
    public $name = 'Settings File';

    public static $supported = ['glg', 'ini', 'json'];

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
            if (
                strpos($file->getFilename(), ".glg") !== false ||
                strpos($file->getFilename(), ".ini") !== false
            ) return true;
        }

        return false;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array|string|null
     * @throws \Exception
     */
    public function unpack(NBinary $binary, $game, $platform){

        $contentAsLower = strtolower($binary->binary);

        if (
            strpos($contentAsLower, 'record') !== false &&
            strpos($contentAsLower, 'class') !== false &&
            strpos($contentAsLower, 'model') !== false
        ){

            $ecs = (new EntityTypeData())->parse($binary);

            $results = [];

            $dummies = 0;

            foreach ($ecs as $name => $ec) {

                switch ($ec->class){
                    case MHT::DUMMY: $results[ 'Dummy/' . $ec->index . '#' . $ec->get('name') . (++$dummies) ] = $ec; break;
                    case MHT::EC_PLAYER: $results[ 'Player/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_CROPS: $results[ 'Crops/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_MOVER: $results[ 'Movers/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_WEAPON: $results[ 'Weapons/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_BASIC: $results[ 'Basic/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_SWITCH: $results[ 'Switches/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_USEABLE: $results[ 'Useable/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_SHOT: $results[ 'Shots/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_TRIGGER: $results[ 'Triggers/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_COLLECTABLE: $results[ 'Collectables/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_PEDHEAD: $results[ 'Hunters/Heads/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_SLIDEDOOR: $results[ 'Doors/Sliding/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_DOOR: $results[ 'Doors/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_RESPONDER: $results[ 'Responders/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_ENTITYSOUND: $results[ 'Sounds/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_ENVIRONMENTAL_EXECUTION: $results[ 'EnvExecution/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_HUNTER:

                        //todo, lookup the PED with the right option...
                        if ($ec->get('head') == "no_hed"){
                            $results[ 'Hunters/BodyWithHead/' . $ec->index . '#' . $ec->get('name') ] = $ec;

                        }else{
                            $results[ 'Hunters/Body/' . $ec->index . '#' . $ec->get('name') . '_' . $ec->get('head') ] = $ec;
                        }

                        break;
                    case MHT::EC_ENTITYLIGHT: $results[ 'Lights/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;
                    case MHT::EC_HELICOPTER:
                    case 'other': $results[ 'Others/' . $ec->index . '#' . $ec->get('name') ] = $ec; break;

                    default:
                        die("unknown class " . $ec->class);

                }
            }

            return $results;

        }

        //it is already unzipped via NBinary
        return $binary->binary;
    }

    /**
     * @param $records
     * @param $game
     * @param $platform
     * @return null|string
     */
    public function pack( $records, $game, $platform ){

        $result = [];
        /** @var Finder $records */

        $records->sort(function(\SplFileInfo $a, \SplFileInfo $b){
            return explode("#", $a->getFilename())[0] > explode("#", $b->getFilename())[0];
        });



        foreach ($records as $record) {
            $result[] = $record->getContents();
        }


        return implode("\n", $result) . "\n";
    }


}