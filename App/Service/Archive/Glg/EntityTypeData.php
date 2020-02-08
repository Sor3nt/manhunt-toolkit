<?php
namespace App\Service\Archive\Glg;


use App\MHT;
use App\Service\Archive\Glg\EntityTypeData\Dummy;
use App\Service\Archive\Glg\EntityTypeData\Ec;
use App\Service\Archive\Glg\EntityTypeData\EcBasic;
use App\Service\Archive\Glg\EntityTypeData\EcCollectable;
use App\Service\Archive\Glg\EntityTypeData\EcCrops;
use App\Service\Archive\Glg\EntityTypeData\EcDoor;
use App\Service\Archive\Glg\EntityTypeData\EcEntityLight;
use App\Service\Archive\Glg\EntityTypeData\EcEntitySound;
use App\Service\Archive\Glg\EntityTypeData\EcEnvironmentalExecution;
use App\Service\Archive\Glg\EntityTypeData\EcHelicopter;
use App\Service\Archive\Glg\EntityTypeData\EcHunter;
use App\Service\Archive\Glg\EntityTypeData\EcMover;
use App\Service\Archive\Glg\EntityTypeData\EcOther;
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
     * @throws \Exception
     */
    public function parse( NBinary $binary){

        $records = $this->convertRecords($binary->binary);

        $types = [];
        foreach ($records as $index => $record) {

            $name = $record['name'];
            
            $class = $this->getClass( $record );

            switch ($class){
                case MHT::DUMMY:
                    $type = new Dummy( $name, $record );
                    break;
                case MHT::EC_BASIC:
                    $type = new EcBasic( $name, $record );
                    break;

                case MHT::EC_ENTITYLIGHT:
                    $type = new EcEntityLight( $name, $record );
                    break;


                case MHT::EC_HUNTER:
                    $type = new EcHunter( $name, $record );
                    break;

                case MHT::EC_CROPS:
                    $type = new EcCrops( $name, $record );
                    break;


                case MHT::EC_PEDHEAD:
                    $type = new EcPedHead( $name, $record );
                    break;

                case MHT::EC_SLIDEDOOR:
                    $type = new EcSlideDoor( $name, $record );
                    break;


                case MHT::EC_DOOR:
                    $type = new EcDoor( $name, $record );
                    break;


                case MHT::EC_WEAPON:
                    $type = new EcWeapon( $name, $record );
                    break;

                case MHT::EC_RESPONDER:
                    $type = new EcResponder( $name, $record );
                    break;

                case MHT::EC_ENTITYSOUND:
                    $type = new EcEntitySound( $name, $record );
                    break;

                case MHT::EC_SWITCH:
                    $type = new EcSwitch( $name, $record );
                    break;

                case MHT::EC_COLLECTABLE:
                    $type = new EcCollectable( $name, $record );
                    break;

                case MHT::EC_TRIGGER:
                    $type = new EcTrigger( $name, $record );
                    break;

                case MHT::EC_SHOT:
                    $type = new EcShot( $name, $record );
                    break;

                case MHT::EC_PLAYER:
                    $type = new EcPlayer( $name, $record );
                    break;

                case MHT::EC_MOVER:
                    $type = new EcMover( $name, $record );
                    break;

                case MHT::EC_USEABLE:
                    $type = new EcUseable( $name, $record );
                    break;

                case MHT::EC_ENVIRONMENTAL_EXECUTION:
                    $type = new EcEnvironmentalExecution( $name, $record );
                    break;

                case MHT::EC_HELICOPTER:
                    $type = new EcHelicopter( $name, $record );
                    break;

                case '':
                    $type = new EcOther( $name, $record );
                    break;


                default:
                    var_dump($record);
                    var_dump($class);
                    var_dump(" not implemented");
                    exit;
                    break;
            }

            $type->index = $index;
            $types[] = $type;
        }

        return $types;

    }

    private function getClass( $record ){
        foreach ($record['options'] as $entry) {
            if ($entry['attr'] == "CLASS") return $entry['value'];
        }
        
        return "DUMMY";
    }


    public function convertRecords( $text ){

        $result = [];


        preg_match_all('/(\#FORCE\n)?RECORD\s(.*\s)*?END/mi', $text, $matches);

        foreach ($matches[0] as $match) {

            $force = false;
            if (substr($match, 0, 6) == "#FORCE"){
                $force = true;
            }


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
                        'attr' => trim($attr),
                        'value' => trim($value)
                    ];
                }else{
                    $options[] = [
                        'attr' => $singleOption
                    ];
                }


            }


            $result[] = [
                'name' => trim($entry[1]),
                'options' => $options,
                'force' => $force
            ];

        }

        return $result;
    }
}
