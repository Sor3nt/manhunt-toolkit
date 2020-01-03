<?php

namespace App\Service\CompilerV2;

use App\Service\Compiler\Token;

class Manhunt2 extends ManhuntDefault
{

    public function __construct()
    {
        $this->functionEventDefinition = array_merge($this->functionEventDefinition, [
            '__default__' => '68000000'
        ]);
    }


    public $constants = [

        'mover_accel_none' => [
            'offset' => '03000000'
        ],
        'ec_player' => [
            'offset' => '0f020000'
        ],
        'ec_hunter' => [
            'offset' => '1f000000'
        ],
        'difficulty_easy' => [
            'offset' => '00000000'
        ],

        'difficulty_hard' => [
            'offset' => '02000000'
        ],

        'ct_sniper_rifle' => [
            'offset' => '32000000'
        ],
        'weather_rainy' => [
            'offset' => '02000000'
        ],
        'ct_torch' => [
            'offset' => '06000000'
        ],
        'ct_knife' => [
            'offset' => '12000000'
        ],
        'ct_shard' => [
            'offset' => '13000000'
        ],
        'ct_broken_bottle' => [
            'offset' => '14000000'
        ],
        'ct_bottle' => [
            'offset' => '16000000'
        ],
        'ct_pipe' => [
            'offset' => '17000000'
        ],
        'ct_cleaver' => [
            'offset' => '18000000'
        ],
        'ct_wooden_bar' => [
            'offset' => '19000000'
        ],
        'ct_crowbar' => [
            'offset' => '1a000000'
        ],
        'ct_sickle' => [
            'offset' => '1b000000'
        ],
        'ct_axe' => [
            'offset' => '1e000000'
        ],
        'ct_icepick' => [
            'offset' => '1f000000'
        ],
        'ct_machete' => [
            'offset' => '20000000'
        ],
        'ct_small_bat' => [
            'offset' => '21000000'
        ],
        'ct_pliers' => [
            'offset' => '22000000'
        ],
        'ct_baseball_bat' => [
            'offset' => '23000000'
        ],
        'ct_shovel' => [
            'offset' => '72000000'
        ],
        'ct_w_baseball_bat' => [
            'offset' => '24000000'
        ],
        'ct_baseball_bat_blades' => [
            'offset' => '27000000'
        ],
        'ct_fire_axe' => [
            'offset' => '25000000'
        ],
        'ct_chainsaw' => [
            'offset' => '5a000000'
        ],
        'ct_nailgun' => [
            'offset' => '5b000000'
        ],
        'ct_6shooter' => [
            'offset' => '28000000'
        ],
        'ct_glock' => [
            'offset' => '29000000'
        ],
        'ct_glock_silenced' => [
            'offset' => '2a000000'
        ],
        'ct_glock_torch' => [
            'offset' => '2b000000'
        ],
        'ct_uzi' => [
            'offset' => '2c000000'
        ],
        'ct_uzi_torch' => [
            'offset' => '2d000000'
        ],
        'ct_shotgun' => [
            'offset' => '2e000000'
        ],
        'ct_shotgun_torch' => [
            'offset' => '2f000000'
        ],
        'ct_desert_eagle' => [
            'offset' => '30000000'
        ],
        'ct_tranq_rifle' => [
            'offset' => '33000000'
        ],
        'ct_sawnoff' => [
            'offset' => '34000000'
        ],
        'ct_colt_commando' => [
            'offset' => '31000000'
        ],
        'ct_grenade' => [
            'offset' => '35000000'
        ],
        'ct_tripwire' => [
            'offset' => '1000000'
        ],
        'ct_tear_gas' => [
            'offset' => '38000000'
        ],
        'ct_flash' => [
            'offset' => '39000000'
        ],
        'ct_gasoline' => [
            'offset' => '02000000'
        ],
        'ct_water' => [
            'offset' => '03000000'
        ],
        'ct_lighter' => [
            'offset' => '04000000'
        ],
        'ct_fists' => [
            'offset' => '10000000'
        ],
        'ct_molotov' => [
            'offset' => '36000000'
        ],
        'ct_expmolotov' => [
            'offset' => '37000000'
        ],
        'ct_firework' => [
            'offset' => '3b000000'
        ],
        'ct_brick_half' => [
            'offset' => '3a000000'
        ],
        'ct_can' => [
            'offset' => '5d000000'
        ],
        'ct_rag' => [
            'offset' => '3d000000'
        ],
        'ct_chlorine' => [
            'offset' => '3e000000'
        ],
        'ct_meths' => [
            'offset' => '3f000000'
        ],
        'ct_hcc' => [
            'offset' => '40000000'
        ],
        'ct_cash' => [
            'offset' => '05000000'
        ],
        'ct_d_beer_guy' => [
            'offset' => '41000000'
        ],
        'ct_d_merc_leader' => [
            'offset' => '42000000'
        ],
        'ct_d_smiley' => [
            'offset' => '43000000'
        ],
        'ct_d_huntlord' => [
            'offset' => '44000000'
        ],
        'ct_cane' => [
            'offset' => '1d000000'
        ],
        'ct_nightstick' => [
            'offset' => '1c000000'
        ],
        'ct_k_dust' => [
            'offset' => '11000000'
        ],
        'ct_e_l_sight' => [
            'offset' => '45000000'
        ],
        'ct_s_silencer' => [
            'offset' => '46000000'
        ],
        'ct_radio' => [
            'offset' => '47000000'
        ],
        'ct_bar_key' => [
            'offset' => '48000000'
        ],
        'ct_syard_comb' => [
            'offset' => '49000000'
        ],
        'ct_camera' => [
            'offset' => '4a000000'
        ],
        'ct_body_p1' => [
            'offset' => '4b000000'
        ],
        'ct_body_p2' => [
            'offset' => '4c000000'
        ],
        'ct_prec_key' => [
            'offset' => '4d000000'
        ],
        'ct_prec_card' => [
            'offset' => '4e000000'
        ],
        'ct_prec_docs' => [
            'offset' => '4f000000'
        ],
        'ct_pharm_hand' => [
            'offset' => '50000000'
        ],
        'ct_est_g_key' => [
            'offset' => '51000000'
        ],
        'ct_est_a_key' => [
            'offset' => '52000000'
        ],
        'ct_doll' => [
            'offset' => '53000000'
        ],
        'ct_n_vision' => [
            'offset' => '07000000'
        ],
        'ct_painkillers' => [
            'offset' => '08000000'
        ],
        'ct_g_first_aid' => [
            'offset' => '09000000'
        ],
        'ct_y_first_aid' => [
            'offset' => '10000000'
        ],
        'ct_speed_boost' => [
            'offset' => '0b000000'
        ],
        'ct_strength_boost' => [
            'offset' => '0c000000'
        ],
        'ct_shooting_boost' => [
            'offset' => '0d000000'
        ],
        'ct_reflexes_boost' => [
            'offset' => '0e000000'
        ],
        'ct_health_boost' => [
            'offset' => '0f000000'
        ],
        'ct_antidote' => [
            'offset' => '54000000'
        ],
        'ct_juryblades' => [
            'offset' => '15000000'
        ],
        'ct_hockey_stick' => [
            'offset' => '26000000'
        ],
        'ct_bag' => [
            'offset' => '3c000000'
        ],
        'ct_wire' => [
            'offset' => '5c000000'
        ],
        'ct_wooden_spike' => [
            'offset' => '5e000000'
        ],
        'ct_pigsy_wire' => [
            'offset' => '61000000'
        ],
        'ct_pigsy_shard' => [
            'offset' => '60000000'
        ],
        'ct_pigsy_spike' => [
            'offset' => '62000000'
        ],
        'ct_hammer' => [
            'offset' => '99000000'
        ],
        'ct_doll_1' => [
            'offset' => '10000000'
        ],
        'ct_doll_2' => [
            'offset' => '65000000'
        ],
        'ct_doll_3' => [
            'offset' => '66000000'
        ],
        'ct_head' => [
            'offset' => '67000000'
        ],
        'ct_key' => [
            'offset' => '55000000'
        ],
        'ct_dvtape' => [
            'offset' => '6f000000'
        ],
        'ct_handycam' => [
            'offset' => '70000000'
        ],
        'ct_ammo_nails' => [
            'offset' => '68000000'
        ],
        'ct_ammo_shotgun' => [
            'offset' => '69000000'
        ],
        'ct_ammo_pistol' => [
            'offset' => '6a000000'
        ],
        'ct_ammo_mgun' => [
            'offset' => '6b000000'
        ],
        'ct_ammo_tranq' => [
            'offset' => '6c000000'
        ],
        'ct_ammo_sniper' => [
            'offset' => '6d000000'
        ],
        'ct_chainsaw_player' => [
            'offset' => '6e000000'
        ],
        'ct_syringe' => [
            'offset' => '71000000'
        ],
        'ct_sledgehammer' => [
            'offset' => '73000000'
        ],
        'ct_stunprod' => [
            'offset' => '74000000'
        ],
        'ct_pen' => [
            'offset' => '75000000'
        ],
        'ct_acid_bottle' => [
            'offset' => '76000000'
        ],
        'ct_1h_firearm' => [
            'offset' => '77000000'
        ],
        'ct_2h_firearm' => [
            'offset' => '78000000'
        ],
        'ct_cut_throat_razor' => [
            'offset' => '79000000'
        ],
        'ct_blowtorch' => [
            'offset' => '7a000000'
        ],
        'ct_mace' => [
            'offset' => '7b000000'
        ],
        'ct_hedge_trimmer' => [
            'offset' => '7c000000'
        ],
        'ct_metal_hook' => [
            'offset' => '7d000000'
        ],
        'ct_circular_saw' => [
            'offset' => '7e000000'
        ],
        'ct_cash_bundle' => [
            'offset' => '8c000000'
        ],
        'ct_matchbook' => [
            'offset' => '8e000000'
        ],
        'ct_noose' => [
            'offset' => '81000000'
        ],
        'ct_camera_weapon' => [
            'offset' => '82000000'
        ],
        'ct_porn' => [
            'offset' => '8d000000'
        ],
        'ct_flaregun' => [
            'offset' => '7f000000'
        ],
        'ct_ammo_flares' => [
            'offset' => '80000000'
        ],
        'ct_crossbow' => [
            'offset' => '83000000'
        ],
        'ct_ammo_crossbow' => [
            'offset' => '84000000'
        ],
        'ct_ammo_arrow' => [
            'offset' => '85000000'
        ],
        'ct_newspaper' => [
            'offset' => '86000000'
        ],
        'ct_milkbottle' => [
            'offset' => '87000000'
        ],
        'ct_dildo' => [
            'offset' => '88000000'
        ],
        'ct_katana' => [
            'offset' => '89000000'
        ],
        'ct_hacksaw' => [
            'offset' => '8a000000'
        ],
        'ct_golfball' => [
            'offset' => '8f000000'
        ],


        'arm_invulnerable' => [
            'offset' => '04000000'
        ],


        'col_shot' => [
            'offset' => '00800000'
        ],

        'hid_healthbar_player' => [
            'offset' => '03000000'
        ],

        'map_color_person' => [
            'offset' => '16000000'
        ],

        'mover_accel_fast' => [
            'offset' => '01000000'
        ],

        'aiscript_mediumpriority' => [
            'offset' => '02000000'
        ],

        'aiscript_idle_standanims' => [
            'offset' => '05000000'
        ],

        'aiscript_idle_wandersearch' => [
            'offset' => '00000000'
        ],

        'mtt_hood_medium' => [
            'offset' => '02000000'
        ],

        'aiscript_walkmovespeed' => [
            'offset' => '01000000'
        ],

        'col_player' => [
            'offset' => '00020000'
        ],

        'map_color_yellow' => [
            'offset' => '04000000'
        ],

        'map_color_red' => [
            'offset' => '02000000'
        ],

        'hid_radar' => [
            'offset' => '02000000'
        ],

        'map_color_location' => [
            'offset' => '14000000'
        ],
        'map_color_green' => [
            'offset' => '05000000'
        ],

        'map_color_hunter_idle' => [
            'offset' => '08000000'
        ],

        'col_hunter' => [
            'offset' => '10000000'
        ],

        'aiscript_veryhighpriority' => [
            'offset' => '00000000'
        ],

        'aiscript_lowpriority' => [
            'offset' => '03000000'
        ],

        'aiscript_idle_standstill' => [
            'offset' => '02000000'
        ],

        'combattypeid_melee' => [
            'offset' => '00000000'
        ],

        'mtt_training' => [
            'offset' => '00000000'
        ],

        'difficulty_normal' => [
            'offset' => '01000000'
        ],

        'map_color_blue' => [
            'offset' => '06000000'
        ],

        'combattypeid_open_melee' => [
            'offset' => '03000000'
        ],

        'combattypeid_cover' => [
            'offset' => '02000000'
        ],

        'combattypeid_open' => [
            'offset' => '01000000'
        ],

        'aiscript_graphlink_allow_nothing' => [
            'offset' => '00000000'
        ],

        'hid_all_player_items' => [
            'offset' => '2c010000'
        ],

        'aiscript_graphlink_allow_everything' => [
            'offset' => '03000000'
        ],

        'aiscript_idle_patrol' => [
            'offset' => '01000000'
        ],

        'aiscript_highpriority' => [
            'offset' => '01000000'
        ],

        'mover_stopped' => [
            'offset' => '00000000'
        ],

        'mover_forward' => [
            'offset' => '01000000'
        ],

        'mover_accel_slow' => [
            'offset' => '02000000'
        ],

        'aiscript_runmovespeed' => [
            'offset' => '00000000'
        ],

        'door_open' => [
            'offset' => '00000000'
        ],

        'door_opening' => [
            'offset' => '01000000'
        ],

        'door_closed' => [
            'offset' => '02000000'
        ],

        'door_closing' => [
            'offset' => '03000000'
        ],


        'useable_on' => [
            'offset' => '01000000'
        ],

        'arm_heavy' => [
            'offset' => '03000000'
        ],

        'map_color_cyan' => [
            'offset' => '07000000'
        ],

        'map_color_orange' => [
            'offset' => '03000000'
        ],

    ];

    public $functions = [
//        'this' => [
//            'name' => 'this',
//            'offset' => "49000000"
//        ],

        'sleep' => [
            'name' => 'Sleep',
            'offset' => "6a000000"
        ],

        'runscript' => [
            'name' => 'RunScript',
            'offset' => "e4000000"
        ],

        'callscript' => [
            'name' => 'callscript',
            'offset' => '0d030000'
        ],

        'playscriptaudiostreamaux' => [
            'name' => 'PlayScriptAudioStreamAux',
            'offset' => 'd7030000'
        ],
        'playdirectorspeechplaceholder' => [
            'name' => 'PlayDirectorSpeechPlaceholder',
            'offset' => '8f020000'
        ],
        'fakehuntersetdir' => [
            'name' => 'FakeHunterSetDir',
            'offset' => 'bd030000'
        ],
        'fakehuntergoto' => [
            'name' => 'FakeHunterGoto',
            'offset' => 'bf030000'
        ],
        'fakehunterdestroy' => [
            'name' => 'FakeHunterDestroy',
            'offset' => 'bb030000'
        ],

        'fakehunterwander' => [
            'name' => 'FakeHunterWander',
            'offset' => 'c1030000'
        ],

        'fakehuntersetpos' => [
            'name' => 'FakeHunterSetPos',
            'offset' => 'bc030000'
        ],

        'fakehuntersetcolour' => [
            'name' => 'FakeHunterSetColour',
            'offset' => 'be030000'
        ],

        'fakehuntercreate' => [
            'name' => 'FakeHunterCreate',
            'offset' => 'ba030000'
        ],

        'getentityview' => [
            'name' => 'GetEntityView',
            'offset' => '93010000',
            'return' => 'vec3d'
        ],

        'freezeentity' => [
            'name' => 'FreezeEntity',
            'offset' => '37010000'
        ],

        'blockpathsaroundplayer' => [
            'name' => 'BlockPathsAroundPlayer',
            'offset' => '9b030000'
        ],

        'aisethunterhomenodedirection' => [
            'name' => 'AISetHunterHomeNodeDirection',
            'offset' => '9d010000'
        ],
        'setdooroverrideangle' => [
            'name' => 'SetDoorOverrideAngle',
            'offset' => '98020000'
        ],
        'aisetentitycrouch' => [
            'name' => 'AISetEntityCrouch',
            'offset' => '09020000'
        ],
        'useablesetstate' => [
            'name' => 'UseableSetState',
            'offset' => 'cc010000'
        ],

        'additemtoinventory' => [
            'name' => 'AddItemToInventory',
            'offset' => 'bb000000'
        ],

        'enablejumpattacks' => [
            'name' => 'EnableJumpAttacks',
            'offset' => '80030000'
        ],

        'inflictdamage' => [
            'name' => 'InflictDamage',
            'offset' => '85000000'
        ],

        'instantdecayalldead' => [
            'name' => 'InstantDecayAllDead',
            'offset' => '83030000'
        ],

        'setspotlightmode' => [
            'name' => 'SetSpotlightMode',
            'offset' => 'a2030000'
        ],

        'heligetnodereached' => [
            'name' => 'HeliGetNodeReached',
            'offset' => 'b4030000',
            'return' => 'string'
        ],

        'getindexfrominventoryitemtype' => [
            'name' => 'GetIndexFromInventoryItemType',
            'offset' => 'c6000000'
        ],

        'enteredtrigger' => [
            'name' => 'EnteredTrigger',
            'offset' => 'a4000000'
        ],

        'aimodifygoalcrouch' => [
            'name' => 'AIModifyGoalCrouch',
            'offset' => '08020000'
        ],
        'aiguardmodifyshootoutsideradius' => [
            'name' => 'AIGuardModifyShootOutsideRadius',
            'offset' => 'cd010000'
        ],
        'sethuntergunfireminpause' => [
            'name' => 'SetHunterGunFireMinPause',
            'offset' => '22020000'
        ],
        'sethuntergunfiremaxpause' => [
            'name' => 'SetHunterGunFireMaxPause',
            'offset' => '23020000'
        ],
        'sethuntergunfireminburst' => [
            'name' => 'SetHunterGunFireMinBurst',
            'offset' => '24020000'
        ],
        'heliresumeidlepatrol' => [
            'name' => 'HeliResumeIdlePatrol',
            'offset' => 'b9030000'
        ],
        'helipauseidlepatrol' => [
            'name' => 'HeliPauseIdlePatrol',
            'offset' => 'b8030000'
        ],
        'helisetidlepatrolpath' => [
            'name' => 'HeliSetIdlePatrolPath',
            'offset' => 'b5030000'
        ],
        'isplayercrawling' => [
            'name' => 'IsPlayerCrawling',
            'offset' => '84030000'
        ],

        'helisetlightrandomwander' => [
            'name' => 'HeliSetLightRandomWander',
            'offset' => '39030000'
        ],
        'helilookatposition' => [
            'name' => 'HeliLookAtPosition',
            'offset' => '33030000'
        ],
        'helilookatentity' => [
            'name' => 'HeliLookAtEntity',
            'offset' => '34030000'
        ],

        'helisetfiringrate' => [
            'name' => 'HeliSetFiringRate',
            'offset' => '70030000'
        ],

        'helisetspolightmaxdeflection' => [
            'name' => 'HeliSetSpolightMaxDeflection',
            'offset' => 'a8030000'
        ],

        'heliopenfire' => [
            'name' => 'HeliOpenFire',
            'offset' => '36030000'
        ],

        'helisetlightspeed' => [
            'name' => 'HeliSetLightSpeed',
            'offset' => '38030000'
        ],

        'helisetenemy' => [
            'name' => 'HeliSetEnemy',
            'offset' => '35030000'
        ],

        'setnoisyentity' => [
            'name' => 'SetNoisyEntity',
            'offset' => 'af030000'
        ],

        'setambienttrackvolume' => [
            'name' => 'SetAmbientTrackVolume',
            'offset' => '2d030000'
        ],

        'aisetidlepatrolstopdirection' => [
            'name' => 'AISetIdlePatrolStopDirection',
            'offset' => 'a7010000'
        ],

        'aiisinsubpack' => [
            'name' => 'AIIsInSubPack',
            'offset' => '66010000'
        ],

        'aidoesleaderhavesubpack' => [
            'name' => 'AIDoesLeaderHaveSubpack',
            'offset' => 'eb020000'
        ],

        'heligotonode' => [
            'name' => 'HeliGotoNode',
            'offset' => 'b3030000'
        ],

        'setscriptaudiostreamocclusion' => [
            'name' => 'SetScriptAudiosTreamOcclusion',
            'offset' => '61030000'
        ],

        'allowreceivingofheadshots' => [
            'name' => 'AllowReceivingOfHeadshots',
            'offset' => '7f030000'
        ],
        'getnumberoftypesinsidetrigger' => [
            'name' => 'GetNumberOfTypesInsideTrigger',
            'offset' => 'db010000'
        ],
        'ishunterinshadow' => [
            'name' => 'IsHunterInShadow',
            'offset' => 'ca030000',
            'return' => 'integer'
        ],
        'frisbeespeechstop' => [
            'name' => 'FrisbeeSpeechStop',
            'offset' => '67030000'
        ],
        'getmoverstate' => [
            'name' => 'GetMoverState',
            'offset' => '39010000',
            'return' => 'integer'
        ],
        'radarcreateblip' => [
            'name' => 'RadarCreateBlip',
            'offset' => 'aa030000'
        ],
        'scripthogprocessorend' => [
            'name' => 'ScriptHogProcessorEnd',
            'offset' => '16020000'
        ],

        'aidefinegoalgotovector' => [
            'name' => 'AiDefineGoalGotoVector',
            'offset' => '70010000'
        ],

        'setspotlighttransitiontime' => [
            'name' => 'SetSpotlightTransitionTime',
            'offset' => 'a1030000'
        ],

        'setspotlighttarget' => [
            'name' => 'SetSpotLightTarget',
            'offset' => 'a0030000'
        ],

        'initspotlight' => [
            'name' => 'InitSpotlight',
            'offset' => '9f030000'
        ],


        'aidefinegoalgotonodestayonpath' => [
            'name' => 'AiDefineGoalGotoNodeStayOnPath',
            'offset' => 'f2010000'
        ],

        'isentitydying' => [
            'name' => 'IsEntityDying',
            'offset' => '52020000'
        ],

        'aiassociatefouractiveareaswithplayerarea' => [
            'name' => 'AIAssociateFourActiveAreasWithPlayerArea',
            'offset' => 'be010000'
        ],

        'aiassociatethreeactiveareaswithplayerarea' => [
            'name' => 'AIAssociateThreeActiveAreasWithPlayerArea',
            'offset' => 'bd010000'
        ],

        'aiassociatetwoactiveareaswithplayerarea' => [
            'name' => 'AiAssociateTwoActiveAreasWithPlayerArea',
            'offset' => 'bc010000'
        ],
        'stringcopy' => [
            'name' => 'stringcopy',
            'offset' => '6d000000'
        ],

        'helicreatehelipath' => [
            'name' => 'HeliCreateHeliPath',
            'offset' => 'b2030000'
        ],

        'helicreatehelinode' => [
            'name' => 'HeliCreateHeliNode',
            'offset' => 'b1030000'
        ],

        'randnum' => [
            'name' => 'RandNum',
            'offset' => '69000000',
            'return' => 'integer'
        ],

        'getdifficultylevel' => [
            'name' => 'GetDifficultyLevel',
            'offset' => '9f020000',
            'return' => 'integer'
        ],

        'aicutsceneentityenable' => [
            'name' => 'AICutSceneEntityEnable',
            'offset' => 'a9020000'
        ],

        'cutsceneend' => [
            'name' => 'CutSceneEnd',
            'offset' => '49010000'
        ],

        'clearlevelgoal' => [
            'name' => 'ClearLevelGoal',
            'offset' => '42020000'
        ],

        'cutscenestart' => [
            'name' => 'CutSceneStart',
            'offset' => '48010000'
        ],

        'cutsceneregisterskipscript' => [
            'name' => 'CutSceneRegisterSkipScript',
            'offset' => '20030000'
        ],

        'displaygametext' => [
            'name' => 'DisplayGameText',
            'offset' => '04010000'
        ],

        'frisbeespeechplay' => [
            'name' => 'FrisbeeSpeechPlay',
            'offset' => '66030000'
        ],

        'frisbeespeechisfinished' => [
            'name' => 'FrisbeeSpeechIsFinished',
            'offset' => '69030000',
            'return' => 'integer'
        ],

        'getentityname' => [
            'name' => 'GetEntityName',
            'offset' => '86000000',
            'return' => 'string'
        ],

        'createlinetrigger' => [
            'name' => 'CreateLineTrigger',
            'offset' => '27010000'
        ],

        'insidetriggertype' => [
            'name' => 'InsideTriggerType',
            'offset' => 'd6010000',
            'return' => 'integer'

        ],

        'getdoorstate' => [
            'name' => 'GetDoorState',
            'offset' => '96000000',
            'return' => 'integer'
        ],

        'pedsettranqknockout' => [
            'name' => 'PedSetTranqKnockout',
            'offset' => '64030000'
        ],

        'hascutscenebeenplayed' => [
            'name' => 'HasCutsceneBeenPlayed',
            'offset' => '03030000'
        ],

        'markcutsceneasplayed' => [
            'name' => 'MarkCutsceneAsPlayed',
            'offset' => '02030000'
        ],

        'whitenoisesetval' => [
            'name' => 'WhiteNoiseSetVal',
            'offset' => 'db020000'
        ],

        'getentity' => [
            'name' => 'GetEntity',
            'offset' => '77000000',
            'return' => 'entityptr'
        ],

        'hudtoggleflashflags' => [
            'name' => 'HUDToggleFlashFlags',
            'offset' => 'b2020000'
        ],
        'isfirstpersoncamera' => [
            'name' => 'isfirstpersoncamera',
            'offset' => 'd5030000'
        ],

        'iswhitenoisedisplaying' => [
            'name' => 'IsWhiteNoiseDisplaying',
            'offset' => 'e7020000'
        ],

        'killgametext' => [
            'name' => 'KillGameText',
            'offset' => '08010000'
        ],

        'killscript' => [
            'name' => 'KillScript',
            'offset' => 'e5000000'
        ],

        'setdoorstate' => [
            'name' => 'SetDoorState',
            'offset' => '97000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state
             * - DOOR_CLOSING  => 3
             */
            'params' => ['Entity', 'integer'],
            'desc' => ''
        ],

        'setswitchstate' => [
            'offset' => '95000000'
        ],

        'setcurrentlod' => [
            'offset' => '2d010000'
        ],

        'setshowhudincutscene' => [
            'offset' => '86030000'
        ],

        'setvector' => [
            'name' => 'SetVector',
            'offset' => '84010000'
        ],

        'setcameraposition' => [
            'offset' => '92010000'
        ],

        'setcameraview' => [
            'offset' => '8f010000'
        ],

        'setzoomlerp' => [
            'offset' => 'b5020000'
        ],

        'setlevelgoal' => [
            'name' => 'SetLevelGoal',
            'offset' => '41020000'
        ],

        'setslidedoorspeed' => [
            'offset' => 'ae010000'
        ],

        'togglehudflag' => [
            'offset' => '7f020000'
        ],

        'aiaddentity' => [
            'offset' => '4d010000'
        ],

        'aisethunteronradar' => [
            'name' => 'aisethunteronradar',
            'offset' => 'a8010000',
            /**
             * Parameters
             * 1: ref to me (me[30])
             * 2: boolean
             */
            'params' => [Token::T_STRING, 'Bollean'],
            'desc' => 'Set the Hunter visibility on the Players Radar'
        ],

        'setnextlevelbyname' => [
            'name' => 'SetNextLevelByName',
            'offset' => '4c030000',
            /**
             * Parameters
             * 1: string level name
             * - A02_The_Old_House
             */
            'params' => [Token::T_STRING],
            'desc' => ''
        ],

        'aisetentityasleader' => [
            'offset' => '4f010000'
        ],

        'aisetleaderinvisible' => [
            'offset' => '6d020000'
        ],

        'aiaddleaderenemy' => [
            'offset' => '54010000'
        ],

        'aientityalwaysenabled' => [
            'offset' => 'bf010000'
        ],

        'aiaddsubpackforleader' => [
            'offset' => '50010000'
        ],

        'aisetsubpackcombattype' => [
            'offset' => '82010000'
        ],

        'aidefinegoalhuntenemy' => [
            'offset' => '58010000'
        ],

        'aiaddgoalforsubpack' => [
            'offset' => '56010000'
        ],

        'aiaddplayer' => [
            'name' => 'aiaddplayer',
            'offset' => '5b010000'
        ],

        'hideentity' => [
            'name' => 'HideEntity',
            'offset' => '83000000'
        ],
        'setslidedoorajardistance' => [
            'name' => 'SetSlideDoorAjarDistance',
            'offset' => '9b010000'
        ],

        'setmaxnumberofrats' => [
            'name' => 'setmaxnumberofrats',
            'offset' => 'a8020000'
        ],

        'switchlitteron' => [
            'name' => 'switchlitteron',
            'offset' => 'a4020000'
        ],

        /**
         * Note: WriteDebug function is internal splitted!
         */
        'writedebug' => [
            'name' => 'WriteDebug',
            'offset' => '73000000'
        ],

        /*
         * I dont know , sounds incorrect
         */
        'writedebugemptystring' => [
            'name' => 'WriteDebugEmptyString',
            'offset' => '71000000'
        ],


        'getplatform' => [
            'name' => 'GetPlatform',
            'offset' => 'd4030000',
            'return' => 'string'
        ],
        'aisetsearchparams' => [
            'name' => 'AISetSearchParams',
            'offset' => '97010000'
        ],

        'aiaddareaforsubpack' => [
            'name' => 'AIAddAreaForSubpack',
            'offset' => '78010000'
        ],

        'aisubpackstayinterritory' => [
            'name' => 'AISubpackStayInTerritory',
            'offset' => 'd5010000'
        ],

        'aisetboundaryintercept' => [
            'name' => 'AISetBoundaryIntercept',
            'offset' => '53020000'
        ],

        'isplayercarryinggascan' => [
            'name' => 'IsPlayerCarryingGasCan',
            'offset' => 'f0020000'
        ],

        'writedebugstring' => [
            'name' => 'WriteDebugString',
            'offset' => '73000000'
        ],
        'writedebugstringarray' => [
            'name' => 'writedebugstringarray',
            'offset' => '73000000'
        ],

        'writedebugfloat' => [
            'name' => 'WriteDebugFloat',
            'offset' => '6f000000'
        ],

        'writedebugreal' => [
            'name' => 'WriteDebugReal',
            'offset' => '6f000000'
        ],

        'writedebuginteger' => [
            'name' => 'WriteDebugInteger',
            'offset' => '6e000000'
        ],

        'writedebugflush' => [
            'name' => 'WriteDebugFlush',
            'offset' => '74000000'
        ],

//
        'removecurrentinventoryitem' => [
            'name' => 'RemoveCurrentInventoryItem',
            'offset' => 'be000000'
        ],

        'getnameoftypeintriggerfromindex' => [
            'name' => 'GetNameOfTypeInTriggerFromIndex',
            'offset' => 'dc010000'
        ],
        'heligetdistancefromspolight' => [
            'name' => 'HeliGetDistanceFromSpolight',
            'offset' => 'a7030000'
        ],
        'setqtmbaseprobability' => [
            'name' => 'SetQTMBaseProbability',
            'offset' => 'ac030000'
        ],

        'graphmodifyconnections' => [
            'name' => 'GraphModifyConnections',
            'offset' => 'e9000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2:
             * - AISCRIPT_GRAPHLINK_ALLOW_NOTHING => 0
             * - AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING => 3
             */
            'params' => ['Entity', 'integer'],
            'desc' => ''
        ],

        'unfreezeentity' => [
            'name' => 'UnFreezeEntity',
            'offset' => '38010000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        'lockentity' => [
            'name' => 'LockEntity',
            'offset' => '98000000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        'entityplayanim' => [
            'name' => 'EntityPlayAnim',
            'offset' => 'a1010000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: animation id ?
             * - PAT_raindow
             * 3: active state?
             * - true
             */
            'params' => ['Entity', Token::T_STRING, 'integer'],
            'desc' => ''
        ],

        'setentityscriptsfromentity' => [
            'name' => 'SetEntityScriptsFromEntity',
            'offset' => 'd9010000',
            /**
             * Parameters
             * 1:
             * - SLockerC_(O)
             * 2:
             * - SLockerC_(O)01
             * - SLockerC_(O)02
             */
            'params' => [Token::T_STRING, Token::T_STRING],
            'desc' => ''
        ],

        'entityignorecollisions' => [
            'name' => 'EntityIgnoreCollisions',
            'offset' => 'a2020000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state
             * - true
             * - false
             */
            'params' => ['Entity', 'integer'],
            'desc' => ''
        ],

        'aientitycancelanim' => [
            'name' => 'AIEntityCancelAnim',
            'offset' => '17020000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: animation name ?
             * - BAT_INMATE_SMACK_HEAD_ANIM
             */
            'params' => [Token::T_STRING, Token::T_STRING],
            'desc' => ''
        ],

        'aisetentityidleoverride' => [
            'name' => 'AISetEntityIdleOverRide',
            'offset' => 'b5010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state 1 boolean
             * 3: state 2 boolean
             */
            'params' => [Token::T_STRING, 'integer', 'integer'],
            'desc' => ''
        ],

        'setentityinvulnerable' => [
            'name' => 'SetEntityInvulnerable',
            'offset' => '5e010000',
            /**
             * Parameters
             * 1: result of getEntity
             * 2: state boolean
             */
            'params' => ['Entity', 'integer'],
            'desc' => ''
        ],

        'aimakeentityblind' => [
            'name' => 'AIMakeEntityBlind',
            'offset' => '71010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state boolean
             */
            'params' => [Token::T_STRING, 'integer'],
            'desc' => ''
        ],

        'aimakeentitydeaf' => [
            'name' => 'AIMakeEntityDeaf',
            'offset' => '72010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state boolean
             */
            'params' => [Token::T_STRING, 'integer'],
            'desc' => ''
        ],

        'aiaddhuntertoleadersubpack' => [
            'name' => 'AIAddHunterToLeaderSubpack',
            'offset' => '52010000',
            /**
             * Parameters
             * 1: Entity name
             * - leader(leader)
             * 2: unknown string
             * - subManWoman
             * 3: Entity name
             * - SobbingWoman(hunter)
             */
            'params' => [Token::T_STRING, Token::T_STRING, Token::T_STRING],
            'desc' => ''
        ],

        'playerdropbody' => [
            'name' => 'PlayerDropBody',
            'offset' => 'b4020000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        'playerfullbodyanimdone' => [
            'name' => 'PlayerFullBodyAnimDone',
            'offset' => '96020000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        'enableuserinput' => [
            'name' => 'EnableUserInput',
            'offset' => 'f5000000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        'showentity' => [
            'name' => 'ShowEntity',
            'offset' => '82000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        'cutscenecamerainit' => [
            'name' => 'CutSceneCameraInit',
            'offset' => '5f030000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        'cutscenecamerasetpos' => [
            'name' => 'CutSceneCameraSetPos',
            'offset' => '5a030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             * 3: float
             * 4: float
             */
            'params' => ['Float', 'Float', 'Float', 'Float'],
            'desc' => ''
        ],

        'cutscenecamerasettarget' => [
            'name' => 'CutSceneCameraSetTarget',
            'offset' => '5b030000'
        ],

        'cutscenecamerasetfov' => [
            'name' => 'CutSceneCameraSetFOV',
            'offset' => '5c030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             */
            'params' => ['Float', 'Float'],
            'desc' => ''
        ],

        'cutscenecamerasetroll' => [
            'name' => 'CutSceneCameraSetRoll',
            'offset' => '5d030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             */
            'params' => ['Float', 'Float'],
            'desc' => ''
        ],

        'cutscenecamerasethandycam' => [
            'name' => 'CutSceneCameraSetHandyCam',
            'offset' => '6d030000',
            /**
             * Parameters
             * 1: state boolean
             */
            'params' => ['integer'],
            'desc' => ''
        ],

        'aidefinegoalgotonodeidle' => [
            'name' => 'AIDefineGoalGotoNodeIdle',
            'offset' => 'b1010000',
            /**
             * Parameters
             * 1: string
             * - goalAmbush
             * 2: string
             * - ref to me (me[30])
             * 3: state
             * - AISCRIPT_HIGHPRIORITY => 1
             * 4: string
             * - AMBUSHNODE
             * 5: state
             * - AISCRIPT_RUNMOVESPEED => 0
             * 6: state boolean
             */
            'params' => [Token::T_STRING, Token::T_STRING, 'integer', Token::T_STRING, 'integer', 'integer'],
            'desc' => ''
        ],

        'aidefinegoalgotonode' => [
            'name' => 'AIDefineGoalGotoNode',
            'offset' => '6f010000',
            /**
             * Parameters
             * 1: string
             * - goalHideTwo
             * 2: stringArray
             * - ref to me (me[30])
             * 3: state
             * - AISCRIPT_HIGHPRIORITY => 1
             * 4: string
             * - HIDERTWO
             * 5: state
             * - AISCRIPT_RUNMOVESPEED => 0
             * 6: state boolean
             */
            'params' => [Token::T_STRING, Token::T_STRING, 'integer', Token::T_STRING, 'integer', 'integer'],
            'desc' => ''
        ],

        'setpedlockonable' => [
            'name' => 'SetPedLockonable',
            'offset' => '97020000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state boolean
             */
            'params' => ['Entity', 'integer'],
            'desc' => ''
        ],

        'moveentity' => [
            'name' => 'MoveEntity',
            'offset' => '7d000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: ref to vec3d (3x float)
             * 3: integer
             */
            'params' => ['Entity', 'vec3d', 'integer'],
            'desc' => ''
        ],

        'setpedorientation' => [
            'name' => 'SetPedOrientation',
            'offset' => 'b0020000',
            'return' => 'vec3d'
        ],

        'setmoverstate' => [
            'name' => 'SetMoverState',
            'offset' => '3a010000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: integer
             * - MOVER_FORWARD => 1
             */
            'params' => ['Entity', 'integer'],
            'desc' => ''
        ],

        'aisetidlehomenode' => [
            'name' => 'AISetIdleHomeNode',
            'offset' => '83010000',
            /**
             * Parameters
             * 1: string ref to me (me[30])
             * 2: string
             * - AMBUSHNODE
             */
            'params' => [Token::T_STRING, Token::T_STRING],
            'desc' => ''
        ],

        'radarpositionclearentity' => [
            'name' => 'RadarPositionClearEntity',
            'offset' => 'e1020000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        'createboxtrigger' => [
            'name' => 'CreateBoxTrigger',
            'offset' => '28010000',
            /**
             * Parameters
             * 1: vec3D
             * 2: vec3D
             * 3: String
             * - triggerOutOfWindow
             */
            'params' => ['vec3d', 'vec3d', Token::T_STRING],
            'desc' => ''
        ],

        'removethisscript' => [
            'name' => 'RemoveThisScript',
            'offset' => 'e8000000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        'sethuntermeleetraits' => [
            'name' => 'SetHunterMeleeTraits',
            'offset' => '77020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: int
             * - MTT_HOOD_MEDIUM => 2
             */
            'params' => ['This', 'integer'],
            'desc' => ''
        ],

        'aisethunteridleactionminmaxradius' => [
            'name' => 'AISetHunterIdleActionMinMaxRadius',
            'offset' => 'a4010000',
            /**
             * Parameters
             * 1: string
             * 2: state int
             * - AISCRIPT_IDLE_WANDERSEARCH => 0
             * 2: state int
             * - AISCRIPT_HIGHPRIORITY => 1
             * 3: int
             * 4: int
             * 5: float
             */
            'params' => [Token::T_STRING, 'integer', 'integer', 'integer', 'integer', 'Float'],
            'desc' => ''
        ],


        'setpeddonotdecay' => [
            'name' => 'SetPedDoNotDecay',
            'offset' => '6b020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: boolean
             */
            'params' => ['This', 'integer'],
            'desc' => ''
        ],

        'enableuseable' => [
            'name' => 'EnableUseable',
            'offset' => 'e5020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: boolean
             */
            'params' => ['This', 'integer'],
            'desc' => ''
        ],

        'setmoveridleposition' => [
            'name' => 'SetMoverIdlePosition',
            'offset' => '3c010000',
            /**
             * Parameters
             * 1: ref to this
             * 2: vec3D
             */
            'params' => ['This', 'vec3d'],
            'desc' => ''
        ],

        'movemovertoidleposition' => [
            'name' => 'MoveMoverToIdlePosition',
            'offset' => '3d010000',
            /**
             * Parameters
             * 1: ref to this
             * 2: vec3D
             */
            'params' => ['This', 'vec3d'],
            'desc' => ''
        ],

        'getplayer' => [
            'name' => 'GetPlayer',
            'offset' => '8a000000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => '',
            'return' => 'entityptr'
        ],
//

        'setmoverspeed' => [
            'name' => 'SetMoverSpeed',
            'offset' => '40010000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        'playaudiooneshotfromentity' => [
            'name' => 'PlayAudioOneShotFromEntity',
            'offset' => '5c020000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],


        'playscriptaudiostreamfromentityauto' => [
            'name' => 'PlayScriptAudioStreamFromEntityAuto',
            'offset' => '6b030000',
            /**
             * Parameters
             * 1: String
             * - NEWLIFT
             * 2: integer
             * 3: result of GetPlayer
             * 4: integer
             */
            'params' => [Token::T_STRING, 'integer', 'GetPlayer', 'integer'],
            'desc' => ''
        ],

        'createspheretrigger' => [
            'name' => 'CreateSphereTrigger',
            'offset' => 'a3000000',
            /**
             * Parameters
             * 1: Vec3D
             * 2: Float
             * 3: string
             * - triggerVisionCheck
             */
            'params' => ['vec3d', 'Float', Token::T_STRING],
            'desc' => ''
        ],

        'destroyentity' => [
            'name' => 'DestroyEntity',
            'offset' => 'a0020000',
            'desc' => ''
        ],

        'getanimationlength' => [
            'name' => 'GetAnimationLength',
            'offset' => '49030000',
            /**
             * Parameters
             * 1: string
             */
            'params' => [Token::T_STRING],
            'return' => 'integer',
            'desc' => ''
        ],

        'unlockentity' => [
            'name' => 'UnLockEntity',
            'offset' => '99000000',
            'desc' => ''
        ],

        'spawnentitywithdirection' => [
            'name' => 'SpawnEntityWithDirection',
            'offset' => '7c000000',
            /**
             * Parameters
             * 1: string
             * - Ins_BodA
             * 2: Vec3d
             * 3: string
             * - Runner(hunter)
             * 4: Vec3d
             */
            'params' => [Token::T_STRING, 'vec3d', Token::T_STRING, 'vec3d'],
            'desc' => ''
        ],

        'getentityposition' => [
            'name' => 'GetEntityPosition',
            'offset' => '78000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'return' => 'vec3d',
            'desc' => ''
        ],

        'setcolourramp' => [
            'name' => 'SetColourRamp',
            'offset' => 'ab030000'
        ],

        'getambientaudiotrack' => [
            'name' => 'GetAmbientAudioTrack',
            'offset' => '77030000',
            'return' => 'integer',
        ],

        'sethunterhideHealth' => [
            'name' => 'SetHunterHideHealth',
            'offset' => '2f010000'
        ],

        'isentityalive' => [
            'name' => 'IsEntityAlive',
            'offset' => 'aa010000',
            'return' => 'integer'
        ],

        'setmaxscoreforlevel' => [
            'name' => 'SetMaxScoreForLevel',
            'offset' => '59030000',
        ],

        'fakehunterdestroyall' => [
            'name' => 'FakeHunterDestroyAll',
            'offset' => 'c3030000',
        ],

        'scripthogprocessorstart' => [
            'name' => 'ScriptHogProcessorStart',
            'offset' => '15020000',
        ],

        'enableaction' => [
            'name' => 'EnableAction',
            'offset' => '62030000',
        ],

        'killthisscript' => [
            'name' => 'KillThisScript',
            'offset' => 'e7000000',
        ],

        'playerplayfullbodyanim' => [
            'name' => 'PlayerPlayFullBodyAnim',
            'offset' => '94020000',
            'return' => 'integer',
        ],

        'disableuserinput' => [
            'name' => 'DisableUserInput',
            'offset' => 'f6000000',
            'return' => 'integer',
        ],

        'setdamage' => [
            'name' => 'SetDamage',
            'offset' => '2f010000',
        ],

        'aitriggersoundknownlocationnoradar' => [
            'name' => 'AITriggerSoundKnownLocationNoRadar',
            'offset' => 'b8020000',
            /**
             * Parameters
             * 1: String
             * - LURE_HIGH
             * 2: Player
             * - GetPlayer
             */
            'params' => [Token::T_STRING, 'Player'],
            'return' => 'integer',
            'desc' => ''
        ],

        'spawnmovingentity' => [
            'name' => 'SpawnMovingEntity',
            'offset' => '7a000000',
            'return' => 'entityptr',
        ],

        'isplayerinsafezone' => [
            'name' => 'IsPlayerInSafeZone',
            'offset' => '89020000',
            'return' => 'integer',
        ],

        'isplayerrunning' => [
            'name' => 'IsPlayerRunning',
            'offset' => 'ee020000',
            'return' => 'integer',
        ],

        'isplayersprinting' => [
            'name' => 'IsPlayerSprinting',
            'offset' => 'ef020000',
            'return' => 'integer',
        ],

        'getpedorientation' => [
            'name' => 'GetPedOrientation',
            'offset' => '8d030000',

            'return' => 'float'
        ],

        'isgametextdisplaying' => [
            'name' => 'IsGameTextDisplaying',
            'offset' => '07010000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'integer',
            'desc' => ''
        ],


        'getlastitempickedup' => [
            'name' => 'GetLastItemPickedUp',
            'offset' => 'c9010000',
            /**
             * Parameters
             * 1: player
             */
            'params' => ['Player'],
            'return' => 'Item',
            'desc' => ''
        ],

        'attachtoentity' => [
            'name' => 'AttachToEntity',
            'offset' => '93000000',
        ],

        'isnameditemininventory' => [
            'name' => 'IsNamedItemInInventory',
            'offset' => '30010000',
            'return' => 'integer',
        ],

        'helisetlight' => [
            'name' => 'HeliSetLight',
            'offset' => '31030000',
        ],

        'helisetmovespeed' => [
            'name' => 'HeliSetMoveSpeed',
            'offset' => '3a030000'
        ],

        'insidetrigger' => [
            'name' => 'InsideTrigger',
            'offset' => 'a5000000',
            'return' => 'integer',
        ],

        'isplayerpositionknown' => [
            'name' => 'IsPlayerPositionKnown',
            'offset' => '6e030000',
            'return' => 'integer',
        ],

        'isplayerwalking' => [
            'name' => 'IsPlayerWalking',
            'offset' => 'ed020000',
            'return' => 'integer'
        ],

        'isexecutioninprogress' => [
            'name' => 'IsExecutionInProgress',
            'offset' => '51020000',
            'return' => 'integer',
        ],

        'isscriptaudiostreamcompleted' => [
            'name' => 'IsScriptAudioStreamCompleted',
            'offset' => 'cf020000',
            'return' => 'integer'

        ],

        'cutscenecamerastart' => [
            'name' => 'CutSceneCameraStart',
            'offset' => '5e030000',
        ],


        'playscriptaudiostreamauto' => [
            'name' => 'PlayScriptAudioStreamAuto',
            'offset' => '6a030000',
        ],

        'aientityplayanim' => [
            'name' => 'AiEntityPlayAnim',
            'offset' => 'b3010000',
        ],

        'setstreamlipsyncspeaker' => [
            'name' => 'SetStreamLipsyncSpeaker',
            'offset' => 'cf030000',
        ],

        'aientityplayanimlooped' => [
            'name' => 'AIEntityPlayAnimLooped',
            'offset' => 'b4010000',
        ],

        'endscriptaudiostream' => [
            'name' => 'EndScriptAudioStream',
            'offset' => 'ce020000',
        ],

        'ishunterknockeddown' => [
            'name' => 'IsHunterKnockedDown',
            'offset' => 'cb030000',
            'return' => 'integer',
        ],

        'aisetentitystayonpath' => [
            'name' => 'AISetEntityStayOnPath',
            'offset' => '4e020000',
        ],

        'setpedhurtotherpeds' => [
            'name' => 'SetPedHurtOtherPeds',
            'offset' => '1e030000',
        ],

        'getdamage' => [
            'name' => 'GetDamage',
            'offset' => '84000000',
            'return' => 'integer',
        ],

        'aisethunteridlepatrol' => [
            'name' => 'AISetHunterIdlePatrol',
            'offset' => 'a3010000',
        ],

        'deactivatesavepoint' => [
            'name' => 'DeactivateSavePoint',
            'offset' => '12030000',
        ],

        'calcdistancetoentity' => [
            'name' => 'CalcDistanceToEntity',
            'offset' => '1a030000',

            'params' => ['EntityPtr'],
            'return' => 'float',
            'desc' => ''
        ],

        'getplayerposition' => [
            'name' => 'GetPlayerPosition',
            'offset' => '8b000000',
            'return' => 'vec3d',
        ],

        'aitriggersound' => [
            'name' => 'AITriggerSound',
            'offset' => '5d010000',
        ],

        'aiplaycommunication' => [
            'name' => 'AIPlayCommunication',
            'offset' => 'fe010000',
        ],

        'setambientaudiotrack' => [
            'name' => 'SetAmbientAudioTrack',
            'offset' => '75030000',
        ],
        'iscutsceneinprogress' => [
            'name' => 'IsCutSceneInProgress',
            'offset' => 'f5020000',
            'return' => 'integer',
        ],
        'setlevelcompleted' => [
            'name' => 'SetLevelCompleted',
            'offset' => '04020000',
        ],

        'thislevelbeencompletedalready' => [
            'name' => 'ThisLevelBeenCompletedAlready',
            'offset' => '04030000',
            'return' => 'integer',
        ],

        'registernonexecutablehunterinlevel' => [
            'name' => 'RegisterNonExecutableHunterInLevel',
            'offset' => 'b1020000',
        ],

        'sethuntermute' => [
            'name' => 'SetHunterMute',
            'offset' => '76030000',
        ],

        'playaudioloopedfromentity' => [
            'name' => 'PlayAudioLoopedFromEntity',
            'offset' => '5e020000',
        ],

        'triggersavepoint' => [
            'name' => 'TriggerSavePoint',
            'offset' => '47030000',
        ],

        'clearalllevelgoals' => [
            'name' => 'ClearAllLevelGoals',
            'offset' => '00030000',
        ],

        'setplayerjumpflag' => [
            'name' => 'SetPlayerJumpFlag',
            'offset' => '24030000',
        ],

        'aisethunteridleactionminmax' => [
            'name' => 'AISetHunterIdleActionMinMax',
            'offset' => '80010000',
        ],

        'killentity' => [
            'name' => 'KillEntity',
            'offset' => '80000000',
        ],

        'sethunterexecutable' => [
            'name' => 'SetHunterExecutable',
            'offset' => '82020000',
        ],

        'radarpositionsetentity' => [
            'name' => 'RadarPositionSetEntity',
            'offset' => 'e0020000',
        ],
        'setplayerheading' => [
            'name' => 'SetPlayerHeading',
            'offset' => '80020000',
        ],
        'sethunterrunspeed' => [
            'name' => 'SetHunterRunSpeed',
            'offset' => 'f1010000',
        ],
        'triggeraddentityclass' => [
            'name' => 'TriggerAddEntityClass',
            'offset' => '10020000',
        ],
        'killentitywithoutanim' => [
            'name' => 'KillEntityWithoutAnim',
            'offset' => '23030000',
        ],
        'airemovegoalfromsubpack' => [
            'name' => 'AIRemoveGoalFromSubpack',
            'offset' => '57010000',
        ],

        'getcameraposition' => [
            'name' => 'GetCameraPosition',
            'offset' => '8e010000',
            'return' => 'vec3d',
        ],

        'sethunterhidehealth' => [
            'name' => 'SetHunterHideHealth',
            'offset' => 'ee010000',

            'params' => [],
            'return' => 'vec3d',
            'desc' => ''
        ],
        'aisetidlepatrolstop' => [
            'name' => 'AISetIdlePatrolStop',
            'offset' => 'a6010000',

            'params' => ['StringArray', Token::T_STRING, 'integer', 'integer'],
            'return' => 'vec3d',
            'desc' => ''
        ],

        'switchlightoff' => [
            'name' => 'SwitchLightOff',
            'offset' => 'da000000'
        ],

        'playscriptaudiostreamfromposauto' => [
            'name' => 'PlayScriptAudioStreamFromPosAuto',
            'offset' => '6c030000'
        ],

        'airemovehunterfromleadersubpack' => [
            'name' => 'AIRemoveHunterFromLeaderSubpack',
            'offset' => '53010000'
        ],

        'setplayercontrollable' => [
            'name' => 'SetPlayerControllable',
            'offset' => '91020000'
        ],

        'setplayergotonode' => [
            'name' => 'SetPlayerGoToNode',
            'offset' => '93020000'
        ],


        'aientitygohomeifidle' => [
            'name' => 'AIEntityGoHomeIfIdle',
            'offset' => '18020000'
        ],


        'aiignoreentityifdead' => [
            'name' => 'AIIgnoreEntityIfDead',
            'offset' => '4f020000'
        ],

        'removeentity' => [
            'name' => 'RemoveEntity',
            'offset' => '81000000'
        ],

        'playscriptaudiostreamfromposautolooped' => [
            'name' => 'PlayScriptAudioStreamFromPosAutoLooped',
            'offset' => '73030000'
        ],


        'isfrisbeespeechcompleted' => [
            'name' => 'IsFrisbeeSpeechCompleted',
            'offset' => 'b0030000'
        ],


        'spawnentitywithvelocity' => [
            'name' => 'SpawnEntityWithVelocity',
            'offset' => 'a1020000'
        ],

        'applyforcetophysicsobject' => [
            'name' => 'ApplyForceToPhysicsObject',
            'offset' => '98030000'
        ],
        'addammotoinventoryweapon' => [
            'name' => 'addammotoinventoryweapon',
            'offset' => '29010000'
        ],

        'isplayerwallsquashed' => [
            'name' => 'IsPlayerWallSquashed',
            'offset' => 'e4020000'
        ],

        'switchlighton' => [
            'name' => 'SwitchLightOn',
            'offset' => 'db000000'
        ],


        'aiisgoalnameinsubpack' => [
            'name' => 'AIIsGoalNameInSubpack',
            'offset' => 'a5020000'
        ],


        'aisetentityallowsurprise' => [
            'name' => 'AISetEntityAllowSurprise',
            'offset' => '6e020000'
        ],

        'triggerremoveentityclass' => [
            'name' => 'TriggerRemoveEntityClass',
            'offset' => '11020000'
        ],


        'aisetidletalkprobability' => [
            'name' => 'AISetIdleTalkProbability',
            'offset' => 'cc030000'
        ],

        'aiisidle' => [
            'name' => 'AIIsIdle',
            'offset' => '6a010000'
        ],

        'playerignorethisentity' => [
            'name' => 'PlayerIgnoreThisEntity',
            'offset' => '8b030000'
        ],

        'ailookatentity' => [
            'name' => 'AILookAtEntity',
            'offset' => 'fd010000'
        ],


        'aicancelhunteridleaction' => [
            'name' => 'AICancelHunterIdleAction',
            'offset' => '81010000'
        ],


        'getdropposforplayerpickups' => [
            'name' => 'GetDropPosForPlayerPickups',
            'offset' => '96030000'
        ],


        'aisethunteridleaction' => [
            'name' => 'AISetHunterIdleAction',
            'offset' => '7f010000'
        ],

        'lockped' => [
            'name' => 'LockPed',
            'offset' => '9a020000'
        ],


        'hunteruseswitch' => [
            'name' => 'HunterUseSwitch',
            'offset' => 'ae020000'
        ],


        'playscriptaudiostreamfromentityautolooped' => [
            'name' => 'PlayScriptAudioStreamFromEntityAutoLooped',
            'offset' => '72030000'
        ],


        'aidefinegoalhidenamedhunter' => [
            'name' => 'AIDefineGoalHideNamedHunter',
            'offset' => '4b020000'
        ],


        'setentityfade' => [
            'name' => 'SetEntityFade',
            'offset' => '82030000'
        ],

        'removescript' => [
            'name' => 'RemoveScript',
            'offset' => 'e6000000'
        ],
        'sethearingactivationradius' => [
            'name' => 'sethearingactivationradius',
            'offset' => '97030000'
        ],

        'isplayercarryingbody' => [
            'name' => 'IsPlayerCarryingBody',
            'offset' => 'b3020000'
        ],


        'settimer' => [
            'name' => 'SetTimer',
            'offset' => 'd0020000',
        ],

        'starttimer' => [
            'name' => 'StartTimer',
            'offset' => 'd1020000'
        ],

        'stoptimer' => [
            'name' => 'StopTimer',
            'offset' => 'd2020000'
        ],
//
//        'incrementtimer' => [
//            'name' => 'IncrementTimer',
//            'offset' => 'd3020000'
//        ],


        'showtimer' => [
            'name' => 'ShowTimer',
            'offset' => 'd4020000'
        ],

        'hidetimer' => [
            'name' => 'HideTimer',
            'offset' => 'd5020000'
        ],

        'incrementcounter' => [
            'name' => 'IncrementCounter',
            'offset' => 'fb020000'
        ],

        'decreasecounter' => [
            'name' => 'DecreaseCounter',
            'offset' => 'fc020000'
        ],

        'showcounter' => [
            'name' => 'ShowCounter',
            'offset' => 'fd020000'
        ],

        'stringcat' => [
            'name' => 'StringCat',
            'offset' => '6c000000'
        ],

        'integertostring' => [
            'name' => 'IntegerToString',
            'offset' => '5b020000'
        ],

        'aientityignoredeadbodies' => [
            'name' => 'AIEntityIgnoreDeadBodies',
            'offset' => 'af020000'
        ],

        'createinventoryitem' => [
            'name' => 'CreateInventoryItem',
            'offset' => 'ba000000'
        ],

        'sethunterdropammo' => [
            'name' => 'SetHunterDropAmmo',
            'offset' => 'd9020000'
        ],

        'setqtmlength' => [
            'name' => 'SetQTMLength',
            'offset' => 'ad030000'
        ],

        'setqtmpresses' => [
            'name' => 'SetQTMPresses',
            'offset' => 'ae030000'
        ],


        'setnodeshadow' => [
            'name' => 'SetNodeShadow',
            'offset' => 'a5030000'
        ],

        'showtriggers' => [
            'name' => 'ShowTriggers',
            'offset' => '20010000'
        ],


        'createcrawltrigger' => [
            'name' => 'CreateCrawlTrigger',
            'offset' => 'd8030000'
        ],

        'aiclearallactiveareaassociations' => [
            'name' => 'AIClearAllActiveAreaAssociations',
            'offset' => 'ba010000'
        ],

        'aiassociateoneactiveareawithplayerarea' => [
            'name' => 'AIAssociateOneActiveAreaWithPlayerArea',
            'offset' => 'bb010000'
        ],


        'getcurrentinventoryitemtype' => [
            'name' => 'GetCurrentInventoryItemType',
            'offset' => '2a010000',
            'return' => 'integer'
        ],

        'aitriggersoundnoradar' => [
            'name' => 'AITriggerSoundNoRadar',
            'offset' => 'b7020000'
        ],


        'aigethunterlastnodename' => [
            'name' => 'AIGetHunterLastNodeName',
            'offset' => '77010000',
            'return' => Token::T_STRING
        ],

        'aidefinegoalguardlookatentity' => [
            'name' => 'AIDefineGoalGuardLookAtEntity',
            'offset' => 'fc010000'
        ],

        'huntersetgunaccuracyfar' => [
            'name' => 'HunterSetGunAccuracyFar',
            'offset' => 'd1010000'
        ],


        'huntersetgunaccuracymid' => [
            'name' => 'HunterSetGunAccuracyMid',
            'offset' => 'd0010000'
        ],

        'huntersetgunaccuracynear' => [
            'name' => 'HunterSetGunAccuracyNear',
            'offset' => 'cf010000'
        ],


        'setmoveraccel' => [
            'name' => 'SetMoverAccel',
            'offset' => '42010000'
        ],


        'sethunteraimtarget' => [
            'name' => 'SetHunterAimTarget',
            'offset' => '49020000'
        ],

        'sethunterhitaccuracy' => [
            'name' => 'SetHunterHitAccuracy',
            'offset' => 'ab010000'
        ],


        'setjittereffect' => [
            'name' => 'SetJitterEffect',
            'offset' => 'c4030000'
        ],

        'aireturnsubpackentityname' => [
            'name' => 'AIReturnSubpackEntityName',
            'offset' => 'ec010000'
        ],

        'ainumberinsubpack' => [
            'name' => 'AINumberInSubpack',
            'offset' => '67010000'
        ],


        'seteffectposition' => [
            'name' => 'SetEffectPosition',
            'offset' => 'ab000000'
        ],

        'createeffect' => [
            'name' => 'CreateEffect',
            'offset' => 'a9000000'
        ],

        'seteffectrgbastart' => [
            'name' => 'SetEffectRGBAStart',
            'offset' => '61010000'
        ],

        'seteffectrgbaend' => [
            'name' => 'SetEffectRGBAEnd',
            'offset' => '62010000'
        ],


        'seteffectpausecycle' => [
            'name' => 'SetEffectPauseCycle',
            'offset' => 'b9000000'
        ],

        'aidefinegoalguarddirection' => [
            'name' => 'aidefinegoalguarddirection',
            'offset' => 'b0010000'
        ],

        'seteffectpauselength' => [
            'name' => 'SetEffectPauseLength',
            'offset' => 'b8000000'
        ],

        'seteffectradius' => [
            'name' => 'SetEffectRadius',
            'offset' => 'b6000000'
        ],

        'substr' => [
            'name' => 'SubStr',
            'offset' => '17030000'
        ],

        'forceweathertype' => [
            'name' => 'forceweathertype',
            'offset' => 'a6020000'
        ],

        'setdooropenanglein' => [
            'name' => 'SetDoorOpenAngleIn',
            'offset' => 'd3010000'
        ],

        'setdooropenangleout' => [
            'name' => 'SetDoorOpenAngleOut',
            'offset' => 'd4010000'
        ],

        'endaudiolooped' => [
            'name' => 'EndAudioLooped',
            'offset' => '60020000'
        ],

        'playscriptaudiostreamfromentityaux' => [
            'name' => 'PlayScriptAudioStreamFromEntityAux',
            'offset' => 'd0030000'
        ],


        'airemoveallgoalsfromsubpack' => [
            'name' => 'AIRemoveAllGoalsFromSubpack',
            'offset' => '9a010000'
        ],


        'sethunterruntime' => [
            'name' => 'SetHunterRunTime',
            'offset' => 'ef010000'
        ],


        'enablegraphconnection' => [
            'name' => 'EnableGraphConnection',
            'offset' => '87030000'
        ],

        'setqtmsoundprobabilitymodifier' => [
            'name' => 'SetQTMSoundProbabilityModifier',
            'offset' => 'c7030000'
        ],

        'setqtmstaminaprobabilitymodifier' => [
            'name' => 'SetQTMStaminaProbabilityModifier',
            'offset' => 'c6030000'
        ],

        'setqtmdeadbodyprobabilitymodifier' => [
            'name' => 'SetQTMDeadBodyProbabilityModifier',
            'offset' => 'c5030000'
        ],


        'aisethunteridledirection' => [
            'name' => 'AISetHunterIdleDirection',
            'offset' => '9c010000'
        ],


        'aienableclimbinginidle' => [
            'name' => 'AiEnableClimbingInIdle',
            'offset' => 'ce030000'
        ],


        'aideletegoaldefinition' => [
            'name' => 'AIDeleteGoalDefinition',
            'offset' => 'df010000'
        ],


        'playaudiooneshotfrompos' => [
            'name' => 'PlayAudioOneShotFromPos',
            'offset' => '5d020000'
        ],


        'activateenvexec' => [
            'name' => 'ActivateEnvExec',
            'offset' => '1c030000'
        ],

        'aitriggersoundlocationknown' => [
            'name' => 'AITriggerSoundLocationKnown',
            'offset' => '71020000'
        ],

        'aidefinegoalguard' => [
            'name' => 'AIDefineGoalGuard',
            'offset' => '59010000'
        ],

        'aidefinegoalorbitentity' => [
            'name' => 'AIDefineGoalOrbitEntity',
            'offset' => 'fa010000'
        ],

        'aimodifygoalaim' => [
            'name' => 'AIModifyGoalAim',
            'offset' => '3f020000'
        ],

        'aistayinhuntenemy' => [
            'name' => 'AIStayInHuntEnemy',
            'offset' => '66020000'
        ],

        'killsubtitletext' => [
            'name' => 'KillSubtitleText',
            'offset' => 'f6020000'
        ],

        'aidefinegoalshootvector' => [
            'name' => 'AIDefineGoalShootVector',
            'offset' => '6a020000'
        ],

        'setnumberofkillablehuntersinlevel' => [
            'name' => 'SetNumberOfKillableHuntersInLevel',
            'offset' => 'e8020000'
        ],
        'returnammoofinventoryweapon' => [
            'name' => 'ReturnAmmoOfInventoryWeapon',
            'offset' => 'df020000'
        ],
        'airemovesubpackfromleader' => [
            'name' => 'AIRemoveSubPackFromLeader',
            'offset' => '51010000'
        ],

        'hidefakehunter' => [
            'name' => 'hidefakehunter',
            'offset' => 'hidefakehunter'
        ],


        'showfakehunter' => [
            'name' => 'showfakehunter',
            'offset' => 'showfakehunter'
        ],

        'subtractvectors' => [
            'name' => 'subtractvectors',
            'offset' => '86010000'
        ],

        'removeitemfrominventoryatslot' => [
            'name' => 'RemoveItemFromInventoryAtSlot',
            'offset' => 'bd000000'
        ],

        'enteredtriggertype' => [
            'name' => 'EnteredTriggerType',
            'offset' => 'd2010000'
        ],


        'setlevelfailed' => [
            'name' => 'SetLevelFailed',
            'offset' => '8e020000'
        ],


        'heligotoposition' => [
            'name' => 'HeliGotoPosition',
            'offset' => '32030000'
        ],



        'isaudiocompleted' => [
            'name' => 'IsAudioCompleted',
            'offset' => '61020000',
            'return' => 'integer'
        ],



        'temporarysetplayertofists' => [
            'name' => 'TemporarySetPlayerToFists',
            'offset' => '21000000',
        ],


        'restoreplayerweapon' => [
            'name' => 'RestorePlayerWeapon',
            'offset' => 'RestorePlayerWeapon'
        ],


        'round' => [
            'name' => 'Round',
            'offset' => 'Round'
        ],



        'newparticleeffect' => [
            'name' => 'NewParticleEffect',
            'offset' => 'a8000000'
        ],

        'getentitymatrix' => [
            'name' => 'GetEntityMatrix',
            'offset' => '0f010000'
        ],

        'attacheffecttomatrix' => [
            'name' => 'AttachEffectToMatrix',
            'offset' => '10010000'
        ],

        'clearhunteraimtarget' => [
            'name' => 'ClearHunterAimTarget',
            'offset' => '4a020000'
        ],

        'isgoaldefined' => [
            'name' => 'IsGoalDefined',
            'offset' => 'd3030000'
        ],



        'playscriptaudiostream' => [
            'name' => 'PlayScriptAudioStream',
            'offset' => 'cb020000'
        ],




        'isscriptaudiostreampreloaded' => [
            'name' => 'IsScriptAudioStreamPreLoaded',
            'offset' => 'ca020000'
        ],

        'preloadscriptaudiostream' => [
            'name' => 'PreLoadScriptAudioStream',
            'offset' => 'c9020000'
        ],


        'helisetorientation' => [
            'name' => 'HeliSetOrientation',
            'offset' => 'a9030000'
        ],

        'calcvisibility' => [
            'name' => 'CalcVisibility',
            'offset' => '53030000'
        ],


        'aidefinegoalgotoentity' => [
            'name' => 'AIDefineGoalGotoEntity',
            'offset' => 'd7010000'
        ],

        'aiisenemyinsight' => [
            'name' => 'AIIsEnemyInSight',
            'offset' => 'AIIsEnemyInSight'
        ],

        'huntershootatentityauto' => [
            'name' => 'HunterShootAtEntityAuto',
            'offset' => 'HunterShootAtEntityAuto'
        ],

        'huntershootatentitystop' => [
            'name' => 'HunterShootAtEntityStop',
            'offset' => 'HunterShootAtEntityStop'
        ],

        'usablegetcurrentanimtimeratio' => [
            'name' => 'UsableGetCurrentAnimTimeRatio',
            'offset' => 'c0010000'
        ],


        'playscriptaudiostreamfromentity' => [
            'name' => 'PlayScriptAudioStreamFromEntity',
            'offset' => '21030000'
        ],


        'aicutsceneallentitiesenable' => [
            'name' => 'AICutSceneAllEntitiesEnable',
            'offset' => 'aa020000'
        ],


        'sethunterimpossibletopickup' => [
            'name' => 'SetHunterImpossibleToPickup',
            'offset' => '9d030000'
        ],


        'entityplaycutsceneanimation' => [
            'name' => 'EntityPlayCutSceneAnimation',
            'offset' => 'd8020000'
        ],


        'cancelhuntercutsceneanim' => [
            'name' => 'CancelHunterCutsceneAnim',
            'offset' => '74030000'
        ],


        'setgametextdisplaytime' => [
            'name' => 'SetGameTextDisplayTime',
            'offset' => '0d010000'
        ],


        'isentitypartofai' => [
            'name' => 'IsEntityPartOfAI',
            'offset' => 'd1030000',
            'return' => 'integer'
        ],


        'aigetsubpackname' => [
            'name' => 'AIGetSubpackName',
            'offset' => '15030000'
        ],


        'gethunterareaname' => [
            'name' => 'GetHunterAreaName',
            'offset' => '1f010000',
            'return' => 'string'
        ],

        'getplayerareaname' => [
            'name' => 'GetPlayerAreaName',
            'offset' => '1d010000',
            'return' => 'string'
        ],

        'doesscriptexist' => [
            'name' => 'DoesScriptExist',
            'offset' => 'd2030000',
            'return' => 'integer'
        ],

        'setpeddecayinstantly' => [
            'name' => 'SetPedDecayInstantly',
            'offset' => '7b020000'
        ],


        'disablelightflare' => [
            'name' => 'DisableLightFlare',
            'offset' => '26030000'
        ],


        'enablelightflare' => [
            'name' => 'EnableLightFlare',
            'offset' => '25030000'
        ],





    ];
}
