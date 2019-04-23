<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class EcWeapon {

    public $class           = MHT::EC_WEAPON;
    public $name            = null;

    protected $map = [
        'CLASS' => null,
        'COLLECTABLE_TYPE' => null,
        'ANIMATION_BLOCK' => null,
        'HOLSTER_SLOT' => null,
        'MODEL' => null,
        'MODEL_HOLSTER_ANIM' => null,
        'WEAPON' => null,
        'KICK_WEAPON' => null,
        'OBSTRUCT_WEAPON' => null,
        'COLLISION_DATA' => null,
        'PHYSICS' => null,
        'HOTSPOT_RADIUS' => null,
        'LOD_DATA' => [],
        'FIRE_SPEED' => null,
        'HAND_ANIM' => null,
        'SELECT_DELAY' => null,
        'HOLSTER_TRANSLATION' => null,
        'HOLSTER_ROTATION' => null,
        'MODEL_DISPLAY_ANIM' => null,

        'BND_SPH_BOOST' => null,
        'SHELL_NUMBER' => null,
        'SHELL_EJECT_POS' => null,
        'SHELL_EJECT_DELAY_FIRE' => null,
        'SHELL_VELOCITY' => null,
        'SHELL_VEL_RAND' => null,
        'SHELL_SPIN' => null,
        'SHELL_RADIUS' => null,
        'SHELL_EJECT_DELAY_RELOAD' => null,
        'GROUND_ROTATION_EULER' => null,
        'GROUND_TRANSLATION' => null,

        'FLASH_MODEL' => null,
        'FLASH_POS' => null,
        'OBSTRUCT_POINT' => null,
        'OBSTRUCT_POINT_ZOOM' => null,
        'DISPLAY_SCALE' => null,

        'STRAP1_TRANSLATION' => null,
        'STRAP1_ROTATION' => null,
        'STRAP2_TRANSLATION' => null,
        'STRAP2_ROTATION' => null,

        'DESTRUCT_AFTER_NUM_USES' => null,
        'DESTRUCT_EFFECT' => null,
        'DESTRUCT_SOUND' => null,

        'TRANSPARENT' => false,
        'LOD_DISTANCE' => [],
        'LOD_INFO' => [],

        'executions' => []
    ];

    protected $executionMap = [
        'EXECUTE_HUNTER_WEAPON_DROP_TIME' => null,
        'EXECUTE_PLAYER_WEAPON_DESTROY_TIME' => null,
        'EXECUTE_TVP' => null,
        'EXECUTE_NUM_TVPS' => null,
        'EXECUTE_DIE_POSE_ROTATION' => null,
        'EXECUTE_FACE_EXPRESSION_TIME' => null,
        'EXECUTE_MODEL' => null,
        'EXECUTE_MODEL_ANIM' => null,
        'EXECUTE_DIE_POSE_FACES_DOWN' => false,
        'EXECUTE_HEAD_EXPLODE_TIME' => false,
        'NEXT_EXECUTION' => false
    ];


    /**
     * EcBasic constructor.
     * @param $name
     * @param $record
     * @throws \Exception
     */
    public function __construct( $name,  $record )
    {

        $this->name = $name;

        $executionMap = array_merge([], $this->executionMap);

        foreach ($record as $entry) {

            if (array_key_exists($entry['attr'] , $this->map)) {

                if ($this->map[$entry['attr']] === false) {
                    $this->map[$entry['attr']] = true;
                } else if (is_array($this->map[$entry['attr']])) {
                    $this->map[$entry['attr']][] = $entry['value'];
                } else {
                    $this->map[$entry['attr']] = $entry['value'];
                }
            }else if (array_key_exists($entry['attr'] , $executionMap)){


                if ($this->executionMap[$entry['attr']] === false) {
                    $executionMap[$entry['attr']] = true;
                } else if (is_array($this->executionMap[$entry['attr']])) {
                    $executionMap[$entry['attr']][] = $entry['value'];
                } else {
                    $executionMap[$entry['attr']] = $entry['value'];
                }

            }else{
//                var_dump($this->name, $entry['value']);
                throw new \Exception(sprintf('Unknown Attribute %s for Record Class %s', $entry['attr'], $this->class));
            }

            if ($entry['attr'] == "NEXT_EXECUTION"){
                $this->map['executions'][] = $executionMap;
                $executionMap = array_merge([], $this->executionMap);
            }
        }
    }


    public function __toString()
    {
        $entries = [ ];

        foreach ($this->map as $attr => $value) {
            if (!is_null($value)){

                if ($attr == "executions"){
                    foreach ($value as $execution) {
                        foreach ($execution as $exeAttr => $exeValue) {

                            if ($exeValue === true) {
                                $entries[] = sprintf('%s', $exeAttr);
                            }else if (is_array($exeValue)){
                                foreach ($exeValue as $single) {
                                    $entries[] = sprintf('%s %s', $exeAttr, $single);
                                }
                            }else{
                                $entries[] = sprintf('%s %s', $exeAttr, $exeValue);
                            }

                        }
                    }
                }else{

                    if ($value === true) {
                        $entries[] = sprintf('%s', $attr);
                    }else if (is_array($value)){
                        foreach ($value as $single) {
                            $entries[] = sprintf('%s %s', $attr, $single);
                        }
                    }else{
                        $entries[] = sprintf('%s %s', $attr, $value);
                    }
                }


            }
        }


        $record = sprintf("\nRECORD %s\n    ", $this->name);
        $record .= implode("\n    ", $entries) . "\n";
        $record .= "END";

        return $record;
    }


    public function get($name)
    {

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $name, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $name = strtoupper(implode('_', $ret));
//        $cc = str_replace('_', '', ucwords($name, '_'));

        if (isset($this->map[$name])) return $this->map[$name];
        foreach ($this->map['executions'] as $execution) {
            if (isset($execution[$name])) return $execution[$name];

        }
        return false;
    }
}
