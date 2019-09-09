<?php
namespace App\Service\Archive\Glg\EntityTypeData;

use App\MHT;

class Ec {

    public $class           = null;
    public $name            = null;

    protected $map = [
        'CLASS' => false,
        'MODEL' => false,
        'ANIMATION_BLOCK' => false,
        'COLLISION_DATA' => false,
        'LOD_DATA' => false,
        'MATERIAL' => false,
        'ARMOUR_CLASS' => false,
        'HIT_POINTS' => false,
        'DEPTHOFFSET' => false,
        'SPAWN' => false,
        'PHYSICS' => false,
        'DAMAGE_FX' => false,
        'TEXTURE_ANIM' => false,
        'DAMAGE_SPAWN' => false,
        'SPAWN_VELOCITY' => false,
        'BND_SPH_BOOST' => false,
        'SPAWN_POSITION' => false,
        'EXPLODED_MODEL' => false,
        'EXPLODE_VELOCITY' => false,
        'EXPLODE_PART_COUNT' => false,
        'EXPLODE_PART_MODEL' => false,
        'GAS_PUDDLE_INCREMENT' => false,
        'ATTRACTSCROWS' => false,
        'GAS_CAN_CREATION_TIME_OF_ENVEXEC_OBJ' => false,
        'GAS_CAN' => false,
        'ENV_EXECUTE_FACE_EXPRESSION_TIME' => false,
        'ENV_EXECUTE_HEAD_EXPLODE_TIME' => false,
        'TREEINWIND' => false,
        'BUSHINWIND' => false,
        'NO_COL_ON_DAMAGE' => false,
        'FORCE_VISIBLE' => false,
        'SMASHABLE' => false,
        'MIRROR' => false,
        'ADDITIVEBLEND' => false,
        'NODEPTHWRITE' => false,
        'TRANSPARENT' => false,
        'DONT_LIGHT' => false,
        'LOCKED' => false,
        'SMASHABLE4' => false,
        'SHADOW' => false,
        'KICKABLE' => false,
        'ROTATELIKEFANX' => false,
        'EXPLODE_ON_DEATH' => false,
        'COLLECTABLE_TYPE' => false,
        'HOLSTER_SLOT' => false,
        'HOTSPOT_RADIUS' => false,
        'SELECT_DELAY' => false,
        'HOLSTER_TRANSLATION' => false,
        'HOLSTER_ROTATION' => false,
        'DISPLAY_SCALE' => false,
        'MODEL_DISPLAY_ANIM' => false,
        'USEABLE_CLASS' => false,
        'MUST_ALIGN' => false,
        'FLARE_LIFETIME' => false,
        'LEADER' => false,
        'IMPULSE_LIMIT' => false,
        'STATE_SOUNDS' => false,
        'HEAD' => false,
        'HEAD_SELECTION' => false,
        'VOICE' => false,
        'KO_TIME' => false,
        'STUN_TIME' => false,
        'WALK_SPEED' => false,
        'RUN_SPEED' => false,
        'RUN_TIME' => false,
        'SWING_PAUSE' => false,
        'HIT_ACCURACY' => false,
        'MAX_LEG_DAMAGE' => false,
        'MAX_ARM_DAMAGE' => false,
        'BLOCK_DAMAGE' => false,
        'MELEE_TYPE' => false,
        'TRAIT_TYPE' => false,
        'SCARABLE' => false,
        'HEAD_EXECUTE' => false,
        'SKIN_TEX' => false,
        'BODY_TYPE' => false,
        'ATTACH_ENTITY_NAME' => false,
        'ATTACH_ENTITY_TO_HELPER' => false,
        'EXPLODE_BIT_MODEL_1' => false,
        'EXPLODE_BIT_MODEL_2' => false,
        'EXPLODE_BIT_MODEL_3' => false,
        'EXPLODE_BIT_MODEL_4' => false,
        'EXPLODEABLE' => false,
        'WALKCYCLE_RFOOT' => false,
        'WALKCYCLE_LFOOT' => false,
        'CAM_POSITION' => false,
        'KO_RECOVERY_SPEED' => false,
        'STICK_DEAD_ZONE' => false,
        'MOVE_THRESHOLDS' => false,
        'MOVE_TRANS_SPEED' => false,
        'SNEAK_WALK_SPEED' => false,
        'SNEAK_RUN_SPEED' => false,
        'SPRINT_SPEED' => false,
        'CROUCH_FORWARD_SPEED' => false,
        'CROUCH_BACKWARD_SPEED' => false,
        'CROUCH_SIDEWAYS_SPEED' => false,
        'AIM_AXIS_WIDTH' => false,
        'MOVE_AXIS_WIDTH' => false,
        'AIM_ZONE_1' => false,
        'AIM_ZONE_2' => false,
        'AIM_ZONE_3' => false,
        'AIM_ZONE_4' => false,
        'AIM_ZONE_5' => false,
        'AIM_ZONE_6' => false,
        'AIM_ZONE_7' => false,
        'AIM_ZONE_8' => false,
        'AIM_ZONE_9' => false,
        'AIM_ZONE_10' => false,
        'VERTICAL_AIM_LIMIT' => false,
        'CROSSHAIR_SIZES' => false,
        'TURN_PAUSE' => false,
        'TURN_ACCELERATION' => false,
        'EXTRA_TURN_SPEED' => false,
        'CAM_RECENTRE_SPEED' => false,
        'CAM_STAIR_SPEED' => false,
        'ZOOM_AIM_SCALE' => false,
        'ZOOM_AIM_SCALE_MOVING' => false,
        'ZOOM_LEVELS' => false,
        'ZOOM_SPEED' => false,
        'ZOOM_MOVE_SCALES' => false,
        'ZOOM_MAX_ZONES' => false,
        'THROW_CHARGE_TIME' => false,
        'MIN_RELEASE_TIME' => false,
        'MIN_THROW_VELOCITY' => false,
        'MAX_THROW_VELOCITY' => false,
        'CROUCH_RECOIL' => false,
        'MAX_QUICK_TURN_SPEED' => false,
        'INVERT_STICK' => false,
        'AIM_LOCKON_ANGLES' => false,
        'AIM_LOCKON_DIST' => false,
        'AIM_ZONE_1_MONKEY' => false,
        'AIM_ZONE_2_MONKEY' => false,
        'AIM_ZONE_3_MONKEY' => false,
        'AIM_ZONE_4_MONKEY' => false,
        'AIM_ZONE_5_MONKEY' => false,
        'AIM_ZONE_6_MONKEY' => false,
        'AIM_ZONE_7_MONKEY' => false,
        'AIM_ZONE_8_MONKEY' => false,
        'AIM_ZONE_9_MONKEY' => false,
        'AIM_ZONE_10_MONKEY' => false,
        'TURN_PAUSE_MONKEY' => false,
        'EXTRA_TURN_SPEED_MONKEY' => false,
        'TURN_ACCELERATION_MONKEY' => false,
        'MONKEY_STAND_TURN_SCALE' => false,
        'MONKEY_WALK_TURN_SCALE' => false,
        'MONKEY_RUN_TURN_SCALE' => false,
        'MONKEY_SPRINT_TURN_SCALE' => false,
        'MONKEY_TURN_ANGLE' => false,
        'MONKEY_MOVE_ANGLE' => false,
        'MONKEY_WALK_ANGLE' => false,
        'RUN_THRESHOLD' => false,
        'STAMINA_TOTAL_SPRINT_TIME' => false,
        'STAMINA_NO_SPRINT_TIME' => false,
        'STAMINA_RECOVERY_MOVING' => false,
        'STAMINA_RECOVERY_RUNNING' => false,
        'STAMINA_RECOVERY_STILL' => false,
        'STAMINA_RECOVERY_PAUSE' => false,
        'WIGGLE_TIME_DECAY' => false,
        'EXECUTE_STAGE_TIMES' => false,
        'QTM_LENGTH' => false,
        'QTM_PROBABILITY_BASE' => false,
        'QTM_PROBABILITY_STAMINA_MODIFIER' => false,
        'QTM_PROBABILITY_DEAD_BODY_MODIFIER' => false,
        'QTM_PROBABILITY_SOUND_MODIFIER' => false,
        'WALL_ATTACK_DAMAGE_MULTIPLIER' => false,
        'GETUP_BUTTON_NUMPRESS' => false,
        'LOD_DISTANCE' => false,
        'LOD_INFO' => false,
        'WEAPON' => false,
        'KICK_WEAPON' => false,
        'OBSTRUCT_WEAPON' => false,
        'HOLSTER_MODEL' => false,
        'ATTACHABLE' => false,
        'FIRE_DELAY' => false,
        'USE_DELAY' => false,
        'SMASHABLE2' => false,
        'HOTSPOT_HORIZ_DIST' => false,
        'HOTSPOT_VERT_DIST' => false,
        'FOCUS_RADIUS' => false,
        'USE_ITEM' => false,
        'MULTIPLE' => false,
        'USEABLE_ANIM' => false,
        'SEARCHABLE' => false,
        'DESTROY_TIME' => false,
        'MIRROR_USEABLE' => false,
        'MIRROR_USEABLE_OFFSET' => false,
        'DESTROY_WHEN_USED' => false,
        'TURN_OFF_COLLISIONS' => false,
        'INTERUPTABLE' => false,
        'WIND_UP' => false,
        'EXECUTIONS_FAIL' => false,
        'SWITCH_MODEL' => false,
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

        foreach ($record as $entry) {

            if (array_key_exists($entry['attr'] , $this->map)){

                $this->map[$entry['attr']] = isset($entry['value']) ? $entry['value'] : true;
            }else{
                var_dump($this->map);
                throw new \Exception(sprintf('Unknown Attribute %s for Record Class %s', $entry['attr'], $this->class));
            }

        }
    }


    public function get($name)
    {

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $name, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $name = strtoupper(implode('_', $ret));

        if (isset($this->map[$name])) return $this->map[$name];

        if ($name == "NAME") return $this->name;
        return false;
    }

    public function __toString()
    {
        $entries = [];

        foreach ($this->map as $attr => $value) {
            if (!is_null($value)){

                if ($value === true) {
                    $entries[] = sprintf('%s', $attr);
                }else if (is_array($value)){
                    foreach ($value as $single) {
                        $entries[] = sprintf('%s %s', $attr, $single);
                    }
                }elseif ($value !== false) {
                    $entries[] = sprintf('%s %s', $attr, $value);
                }

            }
        }

        $record = sprintf("\nRECORD %s\n    ", $this->name);
        $record .= implode("\n    ", $entries) . "\n";
        $record .= "END";

        return $record;
    }

}
