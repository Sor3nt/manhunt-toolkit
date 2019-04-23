<?php
namespace App\Service\Archive\Glg;


use App\MHT;
use App\Service\Archive\Glg\EntityTypeData\Ec;
use App\Service\Archive\Glg\EntityTypeData\EcBasic;
use App\Service\Archive\Glg\EntityTypeData\EcCollectable;
use App\Service\Archive\Glg\EntityTypeData\EcDoor;
use App\Service\Archive\Glg\EntityTypeData\EcEntityLight;
use App\Service\Archive\Glg\EntityTypeData\EcEntitySound;
use App\Service\Archive\Glg\EntityTypeData\EcHunter;
use App\Service\Archive\Glg\EntityTypeData\EcMover;
use App\Service\Archive\Glg\EntityTypeData\EcPedHead;
use App\Service\Archive\Glg\EntityTypeData\EcPlayer;
use App\Service\Archive\Glg\EntityTypeData\EcResponder;
use App\Service\Archive\Glg\EntityTypeData\EcShot;
use App\Service\Archive\Glg\EntityTypeData\EcSlideDoor;
use App\Service\Archive\Glg\EntityTypeData\EcSwitch;
use App\Service\Archive\Glg\EntityTypeData\EcTrigger;
use App\Service\Archive\Glg\EntityTypeData\EcUseable;
use App\Service\Archive\Glg\EntityTypeData\EcWeapon;
use App\Service\NBinary;
use Symfony\Component\Finder\Finder;

class EntityTypeData {

    /**
     * @param NBinary $binary
     * @return Ec[]
     */
    public function parse( NBinary $binary){

        $records = $this->convertRecords($binary->binary);

        $types = [];
        foreach ($records as $name => $record) {

            if (strtolower($name) == "dummy") continue;

            $class = $this->getClass( $record );

            switch ($class){
                case MHT::EC_BASIC:
                    $types[] = new EcBasic( $name, $record );
                    break;

                case MHT::EC_ENTITYLIGHT:
                    $types[] = new EcEntityLight( $name, $record );
                    break;


                case MHT::EC_HUNTER:
                    $types[] = new EcHunter( $name, $record );
                    break;


                case MHT::EC_PEDHEAD:
                    $types[] = new EcPedHead( $name, $record );
                    break;

                case MHT::EC_SLIDEDOOR:
                    $types[] = new EcSlideDoor( $name, $record );
                    break;


                case MHT::EC_DOOR:
                    $types[] = new EcDoor( $name, $record );
                    break;


                case MHT::EC_WEAPON:
                    $types[] = new EcWeapon( $name, $record );
                    break;

                case MHT::EC_RESPONDER:
                    $types[] = new EcResponder( $name, $record );
                    break;

                case MHT::EC_ENTITYSOUND:
                    $types[] = new EcEntitySound( $name, $record );
                    break;

                case MHT::EC_SWITCH:
                    $types[] = new EcSwitch( $name, $record );
                    break;

                case MHT::EC_COLLECTABLE:
                    $types[] = new EcCollectable( $name, $record );
                    break;

                case MHT::EC_TRIGGER:
                    $types[] = new EcTrigger( $name, $record );
                    break;

                case MHT::EC_SHOT:
                    $types[] = new EcShot( $name, $record );
                    break;

                case MHT::EC_PLAYER:
                    $types[] = new EcPlayer( $name, $record );
                    break;

                case MHT::EC_MOVER:
                    $types[] = new EcMover( $name, $record );
                    break;

                case MHT::EC_USEABLE:
                    $types[] = new EcUseable( $name, $record );
                    break;


                default:
                    var_dump($record);
                    var_dump($class . " not implemented");
                    exit;
                    break;
            }


        }

        return $types;

    }

    private function getClass( $record ){
        foreach ($record as $entry) {
            if ($entry['attr'] == "CLASS") return $entry['value'];
        }
    }


    public function convertRecords( $text ){

        $result = [];

        preg_match_all('/RECORD\s(.*\s)*?END/mi', $text, $matches);
        foreach ($matches[0] as $match) {
            preg_match('/RECORD\s(.*)((.*\s*)*)END/i', $match, $entry);

            $options = [];
            $optionsRaw = explode("\n", $entry[2]);

            foreach ($optionsRaw as $singleOption) {
                $singleOption = trim($singleOption);
                if (empty($singleOption)) continue;

                if (strpos($singleOption, ' ') !== false){

                    $attr = substr($singleOption, 0 ,strpos($singleOption, ' '));
                    $value = substr($singleOption, strpos($singleOption, ' ') + 1);


                    $options[] = [
                        'attr' => $attr,
                        'value' => $value
                    ];
                }else{
                    $options[] = [
                        'attr' => $singleOption
                    ];
                }


            }


            $result[ trim($entry[1]) ] = $options;

        }

        return $result;
    }
}
