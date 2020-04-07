<?php

namespace App\Service\CompilerV2;

use App\Service\Helper;

class Manhunt2 extends ManhuntDefault
{

    public $constants = [

        'col_responder' => [
            'offset' => '00400000'
        ],

        'searchreqid_walktoinvestigate' => [
            'offset' => '02000000'
        ],
        'searchreqid_runtoinvestigate' => [
            'offset' => '01000000'
        ],
        'searchreqid_negativechase' => [
            'offset' => '00000000'
        ],
        'pad_square' => [
            'offset' => '08000000'
        ],
        'pad_r1' => [
            'offset' => '80000000'
        ],

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
            'offset' => 'db010000',
            'return' => 'integer'
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
        ],

        'setnextlevelbyname' => [
            'name' => 'SetNextLevelByName',
            'offset' => '4c030000',
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

        'writedebugsinglechar' => [
            'name' => 'WriteDebugSingleChar',
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
            'offset' => 'dc010000',
            'return' => 'string'
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
        ],

        'unfreezeentity' => [
            'name' => 'UnFreezeEntity',
            'offset' => '38010000',
        ],

        'lockentity' => [
            'name' => 'LockEntity',
            'offset' => '98000000',
        ],

        'entityplayanim' => [
            'name' => 'EntityPlayAnim',
            'offset' => 'a1010000',
        ],

        'setentityscriptsfromentity' => [
            'name' => 'SetEntityScriptsFromEntity',
            'offset' => 'd9010000',
        ],

        'entityignorecollisions' => [
            'name' => 'EntityIgnoreCollisions',
            'offset' => 'a2020000',
        ],

        'aientitycancelanim' => [
            'name' => 'AIEntityCancelAnim',
            'offset' => '17020000',
        ],

        'aisetentityidleoverride' => [
            'name' => 'AISetEntityIdleOverRide',
            'offset' => 'b5010000',
        ],

        'setentityinvulnerable' => [
            'name' => 'SetEntityInvulnerable',
            'offset' => '5e010000',
        ],

        'aimakeentityblind' => [
            'name' => 'AIMakeEntityBlind',
            'offset' => '71010000',
        ],

        'aimakeentitydeaf' => [
            'name' => 'AIMakeEntityDeaf',
            'offset' => '72010000',
        ],

        'aiaddhuntertoleadersubpack' => [
            'name' => 'AIAddHunterToLeaderSubpack',
            'offset' => '52010000',
        ],

        'playerdropbody' => [
            'name' => 'PlayerDropBody',
            'offset' => 'b4020000',
        ],

        'playerfullbodyanimdone' => [
            'name' => 'PlayerFullBodyAnimDone',
            'offset' => '96020000',
        ],

        'enableuserinput' => [
            'name' => 'EnableUserInput',
            'offset' => 'f5000000',
        ],

        'showentity' => [
            'name' => 'ShowEntity',
            'offset' => '82000000',
        ],

        'cutscenecamerainit' => [
            'name' => 'CutSceneCameraInit',
            'offset' => '5f030000',
        ],

        'cutscenecamerasetpos' => [
            'name' => 'CutSceneCameraSetPos',
            'offset' => '5a030000',
        ],

        'cutscenecamerasettarget' => [
            'name' => 'CutSceneCameraSetTarget',
            'offset' => '5b030000'
        ],

        'cutscenecamerasetfov' => [
            'name' => 'CutSceneCameraSetFOV',
            'offset' => '5c030000',
        ],

        'cutscenecamerasetroll' => [
            'name' => 'CutSceneCameraSetRoll',
            'offset' => '5d030000',
        ],

        'cutscenecamerasethandycam' => [
            'name' => 'CutSceneCameraSetHandyCam',
            'offset' => '6d030000',
        ],

        'aidefinegoalgotonodeidle' => [
            'name' => 'AIDefineGoalGotoNodeIdle',
            'offset' => 'b1010000',
        ],

        'aidefinegoalgotonode' => [
            'name' => 'AIDefineGoalGotoNode',
            'offset' => '6f010000',
        ],

        'setpedlockonable' => [
            'name' => 'SetPedLockonable',
            'offset' => '97020000',
        ],

        'moveentity' => [
            'name' => 'MoveEntity',
            'offset' => '7d000000',
        ],

        'setpedorientation' => [
            'name' => 'SetPedOrientation',
            'offset' => 'b0020000',
            'return' => 'vec3d'
        ],

        'setmoverstate' => [
            'name' => 'SetMoverState',
            'offset' => '3a010000',
        ],

        'aisetidlehomenode' => [
            'name' => 'AISetIdleHomeNode',
            'offset' => '83010000',
            'return' => 'integer'
        ],

        'radarpositionclearentity' => [
            'name' => 'RadarPositionClearEntity',
            'offset' => 'e1020000',
        ],

        'createboxtrigger' => [
            'name' => 'CreateBoxTrigger',
            'offset' => '28010000',
        ],

        'removethisscript' => [
            'name' => 'RemoveThisScript',
            'offset' => 'e8000000',
        ],

        'sethuntermeleetraits' => [
            'name' => 'SetHunterMeleeTraits',
            'offset' => '77020000',
        ],

        'aisethunteridleactionminmaxradius' => [
            'name' => 'AISetHunterIdleActionMinMaxRadius',
            'offset' => 'a4010000',
        ],


        'setpeddonotdecay' => [
            'name' => 'SetPedDoNotDecay',
            'offset' => '6b020000',
        ],

        'enableuseable' => [
            'name' => 'EnableUseable',
            'offset' => 'e5020000',
        ],

        'setmoveridleposition' => [
            'name' => 'SetMoverIdlePosition',
            'offset' => '3c010000',
        ],

        'movemovertoidleposition' => [
            'name' => 'MoveMoverToIdlePosition',
            'offset' => '3d010000',
        ],

        'getplayer' => [
            'name' => 'GetPlayer',
            'offset' => '8a000000',
            'return' => 'entityptr'
        ],

        'setmoverspeed' => [
            'name' => 'SetMoverSpeed',
            'offset' => '40010000',
        ],

        'playaudiooneshotfromentity' => [
            'name' => 'PlayAudioOneShotFromEntity',
            'offset' => '5c020000',
            'return' => 'integer'
        ],


        'playscriptaudiostreamfromentityauto' => [
            'name' => 'PlayScriptAudioStreamFromEntityAuto',
            'offset' => '6b030000',
        ],

        'createspheretrigger' => [
            'name' => 'CreateSphereTrigger',
            'offset' => 'a3000000',
        ],

        'destroyentity' => [
            'name' => 'DestroyEntity',
            'offset' => 'a0020000',
        ],

        'getanimationlength' => [
            'name' => 'GetAnimationLength',
            'offset' => '49030000',
            'return' => 'integer'
        ],

        'unlockentity' => [
            'name' => 'UnLockEntity',
            'offset' => '99000000',
        ],

        'spawnentitywithdirection' => [
            'name' => 'SpawnEntityWithDirection',
            'offset' => '7c000000',
            'return' => 'integer'
        ],

        'getentityposition' => [
            'name' => 'GetEntityPosition',
            'offset' => '78000000',
            'return' => 'vec3d',
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
            'return' => 'integer',
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
            'return' => 'integer',
        ],


        'getlastitempickedup' => [
            'name' => 'GetLastItemPickedUp',
            'offset' => 'c9010000',
            'return' => 'integer',
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
            'return' => 'float',
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
            'return' => 'integer'
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
            'return' => 'vec3d',
        ],
        'aisetidlepatrolstop' => [
            'name' => 'AISetIdlePatrolStop',
            'offset' => 'a6010000',
            'return' => 'vec3d',
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
            'return' => 'string'
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
            'offset' => 'ec010000',
            'return' => 'integer'
        ],

        'ainumberinsubpack' => [
            'name' => 'AINumberInSubpack',
            'offset' => '67010000',
            'return' => 'integer'
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
            'offset' => '5d020000',
            'return' => 'integer'
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
            'offset' => 'be020000',
        ],


        'restoreplayerweapon' => [
            'name' => 'RestorePlayerWeapon',
            'offset' => 'bf020000'
        ],


        'round' => [
            'name' => 'Round',
            'offset' => '59000000',
            'return' => 'integer'
        ],


        'newparticleeffect' => [
            'name' => 'NewParticleEffect',
            'offset' => 'a8000000',
            'return' => 'integer'
        ],

        'getentitymatrix' => [
            'name' => 'GetEntityMatrix',
            'offset' => '0f010000',
            'return' => 'integer'
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
            'offset' => '53030000',
            'return' => 'integer'
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
            'offset' => 'c0010000',
            'return' => 'float'
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


        'hasplayerarrivedatnode' => [
            'name' => 'HasPlayerArrivedAtNode',
            'offset' => '92020000'
        ],

        'ispadbuttonpressed' => [
            'name' => 'IsPadButtonPressed',
            'offset' => 'fa000000'
        ],

        'cleardooroverrideangle' => [
            'name' => 'ClearDoorOverrideAngle',
            'offset' => '99020000'
        ],

        'cos' => [
            'name' => 'Cos',
            'offset' => '65000000',
            'return' => 'float'
        ],

        'sin' => [
            'name' => 'Sin',
            'offset' => '64000000',
            'return' => 'float'
        ],

        'sethunterwalkspeed' => [
            'name' => 'SetHunterWalkSpeed',
            'offset' => 'f0010000'
        ],


        'spawnstaticentity' => [
            'name' => 'SpawnStaticEntity',
            'offset' => '7b000000'
        ],


        'aisetsubpacksearchparams' => [
            'name' => 'AISetSubPackSearchParams',
            'offset' => '98010000'
        ],

        'isplayersneaking' => [
            'name' => 'IsPlayerSneaking',
            'offset' => 'ec020000'
        ],

        'haspadbuttonbeenpressed' => [
            'name' => 'haspadbuttonbeenpressed',
            'offset' => 'fb000000'
        ],

        'handcamsetall' => [
            'name' => 'HandCamSetAll',
            'offset' => 'e5010000'
        ],

        'killallflares' => [
            'name' => 'KillAllFlares',
            'offset' => '6f030000'
        ],


        'mutilatehunter' => [
            'name' => 'MutilateHunter',
            'offset' => 'c0020000'
        ],

        'rotateentityleft' => [
            'name' => 'RotateEntityLeft',
            'offset' => '7e000000'
        ],

        'setmoveridleposmargin' => [
            'name' => 'SetMoverIdlePosMargin',
            'offset' => 'de010000'
        ],

    ];

    public $gameSccSrc = "
{ !! THIS IS A GLOBAL FILE FOR THE ENTIRE GAME - EDIT WITH CAUTION !! }
scriptmain gameScript;
entity manhunt : et_game;
var	{ Global game_var's }
	gsAcPlatformModifier, gsAcDifficultyModifier : real;
	{ Accuracy setting }
	gsHeliAccuracy, gsHeliRateOfFire : real;
	gsAcRangeNear_UZI, gsAcRangeMid_UZI, gsAcRangeFar_UZI, gsAcRadiusNear_UZI, gsAcRadiusMid_UZI, gsAcRadiusFar_UZI : real;
	gsAcRangeNear_COLT, gsAcRangeMid_COLT, gsAcRangeFar_COLT, gsAcRadiusNear_COLT, gsAcRadiusMid_COLT, gsAcRadiusFar_COLT : real;
	gsAcRangeNear_GLOCK, gsAcRangeMid_GLOCK, gsAcRangeFar_GLOCK, gsAcRadiusNear_GLOCK, gsAcRadiusMid_GLOCK, gsAcRadiusFar_GLOCK : real;
	gsAcRangeNear_DEAGLE, gsAcRangeMid_DEAGLE, gsAcRangeFar_DEAGLE, gsAcRadiusNear_DEAGLE, gsAcRadiusMid_DEAGLE, gsAcRadiusFar_DEAGLE : real;
	gsAcRangeNear_SNIPER, gsAcRangeMid_SNIPER, gsAcRangeFar_SNIPER, gsAcRadiusNear_SNIPER, gsAcRadiusMid_SNIPER, gsAcRadiusFar_SNIPER : real;
	gsAcRangeNear_SAWNOFF, gsAcRangeMid_SAWNOFF, gsAcRangeFar_SAWNOFF, gsAcRadiusNear_SAWNOFF, gsAcRadiusMid_SAWNOFF, gsAcRadiusFar_SAWNOFF : real;
	gsAcRangeNear_SHOTGUN, gsAcRangeMid_SHOTGUN, gsAcRangeFar_SHOTGUN, gsAcRadiusNear_SHOTGUN, gsAcRadiusMid_SHOTGUN, gsAcRadiusFar_SHOTGUN : real;
	gsAcRangeNear_REVOLVER, gsAcRangeMid_REVOLVER, gsAcRangeFar_REVOLVER, gsAcRadiusNear_REVOLVER, gsAcRadiusMid_REVOLVER, gsAcRadiusFar_REVOLVER : real;
	gsAcRangeNear_CROSSBOW, gsAcRangeMid_CROSSBOW, gsAcRangeFar_CROSSBOW, gsAcRadiusNear_CROSSBOW, gsAcRadiusMid_CROSSBOW, gsAcRadiusFar_CROSSBOW : real;
	gsAcRangeNear_FLAREGUN, gsAcRangeMid_FLAREGUN, gsAcRangeFar_FLAREGUN, gsAcRadiusNear_FLAREGUN, gsAcRadiusMid_FLAREGUN, gsAcRadiusFar_FLAREGUN : real;
	gsAcRangeNear_TRANQGUN, gsAcRangeMid_TRANQGUN, gsAcRangeFar_TRANQGUN, gsAcRadiusNear_TRANQGUN, gsAcRadiusMid_TRANQGUN, gsAcRadiusFar_TRANQGUN : real;

{ GameScript }
script OnCreate;
	begin
		WriteDebug('================= GameScript: OnCreate =================');
		{ Set default variables }
		gsAcPlatformModifier	:= 0;
		gsAcDifficultyModifier	:= 0;
		gsHeliAccuracy			:= 0.2;
		gsHeliRateOfFire		:= 5;
		{ =========================== GUN ACCURACY =========================== }
		{ Modify global accuracy for platform - use a positive number to reduce accuracy }
		if(GetPlatform = 'PS2') then gsAcPlatformModifier := 0.0;
		if(GetPlatform = 'PSP') then gsAcPlatformModifier := 0.2;
		{if(GetPlatform = 'WII') then gsAcPlatformModifier := 0.0;}
		{ Modify global accuracy for difficulty level - use a negative number to increase accuracy }
		{ NOTE: not working currently - we need an OnLevelLoad event to reload the data }
		if(GetDifficultyLevel = DIFFICULTY_EASY) then gsAcDifficultyModifier :=  0.0;
		if(GetDifficultyLevel = DIFFICULTY_HARD) then gsAcDifficultyModifier := -0.01;
		{ ============== WHITE: REVOLVER }
		gsAcRangeNear_REVOLVER	:=  5.0;
		gsAcRangeMid_REVOLVER	:= 15.0;
		gsAcRangeFar_REVOLVER	:= 40.0;
		gsAcRadiusNear_REVOLVER	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_REVOLVER	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_REVOLVER	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: GLOCK }
		gsAcRangeNear_GLOCK		:=  5.0;
		gsAcRangeMid_GLOCK		:= 15.0;
		gsAcRangeFar_GLOCK		:= 40.0;
		gsAcRadiusNear_GLOCK	:=  0.4 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_GLOCK		:=  0.8 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_GLOCK		:=  1.4 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: DEAGLE }
		gsAcRangeNear_DEAGLE	:=  5.0;
		gsAcRangeMid_DEAGLE		:= 15.0;
		gsAcRangeFar_DEAGLE		:= 40.0;
		gsAcRadiusNear_DEAGLE	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_DEAGLE	:=  0.9 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_DEAGLE	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: TRANQGUN }
		gsAcRangeNear_TRANQGUN	:=  5.0;
		gsAcRangeMid_TRANQGUN	:= 15.0;
		gsAcRangeFar_TRANQGUN	:= 40.0;
		gsAcRadiusNear_TRANQGUN	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_TRANQGUN	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_TRANQGUN	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: FLAREGUN }
		gsAcRangeNear_FLAREGUN	:=  5.0;
		gsAcRangeMid_FLAREGUN	:= 15.0;
		gsAcRangeFar_FLAREGUN	:= 40.0;
		gsAcRadiusNear_FLAREGUN	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_FLAREGUN	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_FLAREGUN	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: UZI }
		gsAcRangeNear_UZI		:=  5.0;
		gsAcRangeMid_UZI		:= 15.0;
		gsAcRangeFar_UZI		:= 40.0;
		gsAcRadiusNear_UZI		:=  0.6 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_UZI		:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_UZI		:=  2.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: SAWNOFF }
		gsAcRangeNear_SAWNOFF	:=  5.0;
		gsAcRangeMid_SAWNOFF	:= 15.0;
		gsAcRangeFar_SAWNOFF	:= 40.0;
		gsAcRadiusNear_SAWNOFF	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_SAWNOFF	:=  1.8 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_SAWNOFF	:=  3.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: SHOTGUN }
		gsAcRangeNear_SHOTGUN	:=  5.0;
		gsAcRangeMid_SHOTGUN	:= 15.0;
		gsAcRangeFar_SHOTGUN	:= 40.0;
		gsAcRadiusNear_SHOTGUN	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_SHOTGUN	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_SHOTGUN	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: COLT }
		gsAcRangeNear_COLT		:=  5.0;
		gsAcRangeMid_COLT		:= 15.0;
		gsAcRangeFar_COLT		:= 40.0;
		gsAcRadiusNear_COLT		:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_COLT		:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_COLT		:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: SNIPER }
		gsAcRangeNear_SNIPER	:=  5.0;
		gsAcRangeMid_SNIPER		:= 15.0;
		gsAcRangeFar_SNIPER		:= 40.0;
		gsAcRadiusNear_SNIPER	:=  0.7 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_SNIPER	:=  1.2 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_SNIPER	:=  2.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: CROSSBOW }
		gsAcRangeNear_CROSSBOW	:=  5.0;
		gsAcRangeMid_CROSSBOW	:= 15.0;
		gsAcRangeFar_CROSSBOW	:= 40.0;
		gsAcRadiusNear_CROSSBOW	:=  0.8 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_CROSSBOW	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_CROSSBOW	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
	end;

end.
        ";

    public static $hashNames = [
        'A20_WALL_V1',
        'ADJACENTDOOR',
        'AFFECTS MAP',
        'AFFECTS OBJECTS',
        'AH_ARCHCORN',
        'AH_SLABTOPS',
        'AI TYPE',
        'AI_CHECK_CRAWLSPACE',
        'AI_NO_ANIM',
        'AI_PISS_HERE',
        'AI_SMOKE_HERE',
        'AI_VENDING_MACHINE',
        'ALARM',
        'ANIMATION_BLOCK',
        'ASS1',
        'ASYLUMSHAD1',
        'ASYLUM_BIGHOUSE001',
        'ASYLUM_CEILING_CONCRETE02',
        'ASYLUM_CELLMARKS',
        'ASYLUM_CELLMARKS2',
        'ASYLUM_DOOR',
        'ASYLUM_FLOOR_GREEN',
        'ASYLUM_FLOOR_GREEN002',
        'ASYLUM_GRAFF',
        'ASYLUM_GRAFF002',
        'ASYLUM_GRAFF003',
        'ASYLUM_GUMMIZELLE',
        'ASYLUM_LOGO',
        'ASYLUM_MONITOR_ANIM',
        'ASYLUM_PEER_ANIM',
        'ASYLUM_SIGNS001',
        'ASYLUM_SIGNS002',
        'ASYLUM_SIGNS003',
        'ASYLUM_SIGNS004',
        'ASYLUM_SIGNS005',
        'ASYLUM_SIGNS006',
        'ASYLUM_SIGNS007',
        'ASYLUM_SPEAK_ANIM',
        'ASYLUM_WALL005DBIG',
        'ASYLUM_WALL_CONCRETE001',
        'ASYLUM_WALL_CONCRETE002',
        'ASYLUM_WALL_WHITE001',
        'ASYLUM_WALL_WHITE002',
        'ASYLUM_WINDOWTILE',
        'ATTENUATION RADIUS',
        'BACK_LEFT',
        'BACK_RIGHT',
        'BAG_MELEE_COLLISION',
        'BALL',
        'BANK NAME',
        'BASS',
        'BB2',
        'BB3',
        'BB_WRITING',
        'BLACK',
        'BLACK64',
        'BLACKBOARD',
        'BLACK_CLOTH',
        'BLACK_STONE',
        'BLACK_TILES02',
        'BLACK_WOOD',
        'BLOCKS',
        'BLOCK_WITH_ARM',
        'BLOCK_WITH_ARM_X',
        'BLOODFLOOR',
        'BODYBLOW',
        'BODYBLOW_X',
        'BODY_PART',
        'BOTTLE',
        'BOW_ABATTOIR_CONC2',
        'BOW_BARB',
        'BOW_BB_COURT',
        'BOW_BIGPAVE',
        'BOW_BIGROAD',
        'BOW_BROWNBRICK',
        'BOW_CONCRETE_DRIP',
        'BOW_CRACKPAVE',
        'BOW_FENCE_METAL',
        'BOW_MEATVAN_VENT',
        'BOW_MESHFENCING',
        'BOW_ROAD_CNTR',
        'BOW_ROAD_CNTR_DRY',
        'BOW_SIDEWALK',
        'BOW_SKANKLEDGE',
        'BOW_SMEAR_CEMENT',
        'BOW_TV_FRONT_SKIRTING_LWR',
        'BOW_WALL_WHITEBRIX',
        'BOW_WAREHOUSEWALL',
        'BOXWOOD',
        'BRAIN_SPLAT',
        'BRICK',
        'BRICK_2',
        'BRICK_3',
        'BRICK_THROWER_COLLISION',
        'BURNFLA',
        'BUSH',
        'BUSH_SOLID',
        'BUZZERACCEPT',
        'BUZZERDENY',
        'CAMERA_NOISE_LEFT',
        'CAMERA_NOISE_RIGHT',
        'CAMPUS_LIBR_CONCRETE',
        'CAMPUS_LIBR_ROOF_DARK',
        'CAN',
        'CAN_THROWER_COLLISION',
        'CAR',
        'CARPET',
        'CEM_GEN_CRYPT_M001',
        'CEM_GRASS002',
        'CERAMIC',
        'CHAIN',
        'CHAIN_FALL',
        'CHEAT_LEFT',
        'CHEAT_RIGHT',
        'CHU_PIPES',
        'CIRCULARSAW_MELEE_COLLISION',
        'CJ_BAR_BOTTLE1',
        'CJ_BAR_TAP1',
        'CJ_BAR_TAP2',
        'CJ_BASKETB_BOARD',
        'CJ_BBNETCOLOR',
        'CJ_CHILLER',
        'CJ_CHROME2',
        'CJ_CHROMEPIPE',
        'CJ_CONSOLE1',
        'CJ_CONSOLE3',
        'CJ_CRANE_ARM',
        'CJ_DARKWOOD',
        'CJ_DESK',
        'CJ_DIRTYBRICK',
        'CJ_DOORSURROUND',
        'CJ_GALVANISED',
        'CJ_GENERATOR3',
        'CJ_GREENWOOD',
        'CJ_KEYBOARD',
        'CJ_METAL2',
        'CJ_METALDOOR1',
        'CJ_METALDOOR3_SM',
        'CJ_OLD_PISH',
        'CJ_PANEL2',
        'CJ_SHADOW',
        'CJ_SHEET2',
        'CJ_SLATEDWOOD',
        'CJ_TRUCKBACK',
        'CJ_TRUCKSIDE',
        'CLIP_FABRIC',
        'CLIP_FOLIAGE',
        'CLIP_GLAS',
        'CLIP_METAL',
        'CLIP_METALFENCE',
        'CLIP_METALGRATE',
        'CLIP_PEDBARRIER',
        'CLIP_PEDBARRIER_WOOD',
        'CLIP_STONE',
        'CLIP_WATER',
        'CLIP_WHEELY_BIN',
        'CLIP_WOOD',
        'CLOSEST %',
        'CLOSE_HEAVY_METAL',
        'CLOSE_IRONBAR_DOOR',
        'CLOSE_METAL',
        'CLOSE_WIRE',
        'CLOSE_WOOD',
        'CLOTHING',
        'COFFEEMACHINE_FRONT',
        'COFFEE_MACHINE_C',
        'COFFEE_MACHINE_GLASS_C',
        'COFFEE_MACHINE_SPEC',
        'COLLEGE_LOCKER_RUSTY',
        'COLOUR: BLUE',
        'COLOUR: GREEN',
        'COLOUR: RED',
        'CONE ANGLE',
        'CORN',
        'CRACKEDGLASS',
        'CRAWLING',
        'CRAWLINGCHILD',
        'CRAWLING_METAL',
        'CREATES CHARACTER SHADOWS',
        'CROWBAR_MELEE_COLLISION',
        'CUSHIONS',
        'CUTTING_MELEE_COLLISION',
        'CUTTING_MELEE_COLLISION_X',
        'DARK_WOOD',
        'DEADHOOKERBLOOD',
        'DEADHOOKERPARTS02',
        'DELIVERYVAN8BIT128',
        'DETECTION HEIGHT IN METRES',
        'DETECTION RADIUS IN METRES',
        'DE_ASYLMCIELING01',
        'DE_ASYLMCIELING02',
        'DE_FLOOR',
        'DE_LIFT_DOOR01',
        'DE_METAL_GRILL02',
        'DE_PBARS1',
        'DE_PBARSHADOWS1',
        'DE_PBARS_METAL01',
        'DE_TILES01',
        'DE_UPT_FLAT_TILES',
        'DILDO_MELEE_COLLISION',
        'DIRECTOR_NOISE',
        'DIRTDECAL1',
        'DIRTSCUFF',
        'DIVE',
        'DOOR001',
        'DOOR_FRAME_DIRT',
        'DRAWWOOD',
        'DRILL1',
        'DRILL2',
        'DRILL3',
        'DRINKS1',
        'DRIPPY',
        'DRIPS1',
        'DRIPS2',
        'DRIPTAIL1',
        'DRIPTAILOOP',
        'DROP_AMMO',
        'DT_GRASSTRANS1_TEXTURE',
        'DUCKY',
        'DUMPSTER',
        'EASY_BRUSHED_METAL',
        'EFFECT DURATION',
        'ELEVATORSTOP',
        'ENTITY LIGHT',
        'EST_BRKRM_CARPET',
        'EST_BRKRM_PAPER',
        'EST_BRKRM_PAPER_BOTTOM',
        'EST_DNGRM_MARBLE',
        'EST_DNGRM_WIN',
        'EST_LOCKUP_UPPERWALL',
        'EST_METALVENTEDDOOR',
        'EXECUTION OBJECT',
        'EXECUTION TYPE',
        'EXIT_SIGN_RED',
        'FAB_FRIC',
        'FADE CONTINOUSLY',
        'FADE IN TIME IN MS',
        'FADE OUT TIME',
        'FAN1',
        'FAN2',
        'FANBLADERING1',
        'FANLOOP1',
        'FANLOOP2',
        'FANPROX',
        'FARM_BRICKS002B',
        'FENCE',
        'FIREAXE_MELEE_COLLISION',
        'FIRE_BAZOOKA',
        'FIRE_EMPTY',
        'FIRE_POPGUN',
        'FISTS_MELEE_COLLISION',
        'FLESH_SQUELCH',
        'FLESH_SQUELCH_X',
        'FLICKER/STROBE OFF TIME IN MS',
        'FLICKER/STROBE ON TIME IN MS',
        'FLICKERING',
        'FLIES',
        'FLOOR_TILES_FASTFOOD001',
        'FLOOR_TILES_FASTFOOD002',
        'FLOOR_TILES_SM001',
        'FLOOR_TILES_SM002',
        'FLOOR_TOL_APPA_TILE',
        'FORCE TO ZONE',
        'FRAMEWORK_WOOD002',
        'FREAKS_PISS_ANIM',
        'FREAKS_VENDING_ANIM',
        'FREAKS_VOMIT_ANIM',
        'GB_BASEMENT_FLOOR',
        'GB_CONC_FILTHB',
        'GB_GATEMETAL01_M',
        'GB_GAUNT_WALL04_S',
        'GB_PAVEMENT',
        'GB_PHARM_WALL01_S',
        'GB_PRESUB_TROOSTTILEMIDDLE',
        'GB_RAMIREZ_FLATWALLC',
        'GB_RAMIREZ_WINDOW01',
        'GB_SIDEWALK',
        'GB_TRAINYARD_WALL',
        'GB_WEAS_WALL03_S',
        'GB__PETSHOP_FLOORB',
        'GENCAR_BITS',
        'GENERIC_TALK_ANIM',
        'GEN_GRAFFITI',
        'GEN_LOCK',
        'GEN_WHEEL',
        'GEN_ZOOMETAL',
        'GLASSBREAK2',
        'GLASSHARD',
        'GLASS_BREAK',
        'GORE',
        'GRAB',
        'GRASS',
        'GRASSBOOT',
        'GRASS_DETAIL',
        'GRAVELSCUFF',
        'HACKSAW_MELEE_COLLISION',
        'HALFOPENSHUTTER',
        'HANDLES64',
        'HANG',
        'HAS LENS FLARE',
        'HAS LENSFLARE',
        'HAS SEARCHLIGHT CONE',
        'HEAD_POP',
        'HEAD_SPLAT',
        'HEAD_SPLAT_X',
        'HEART',
        'HEART1',
        'HEART2',
        'HEAVY_FLESH_HIT',
        'HEAVY_FLESH_HIT_X',
        'HIT_GROUND',
        'HIT_GROUND_HARD',
        'HIT_HINGE_LIMIT_HEAVY_METAL',
        'HIT_HINGE_LIMIT_IRONBAR_DOOR',
        'HIT_HINGE_LIMIT_METAL',
        'HIT_HINGE_LIMIT_WIRE',
        'HIT_HINGE_LIMIT_WOOD',
        'HOSPITAL_TROLLEY',
        'HP%_',
        'HUNTERLOOK X',
        'HUNTERLOOK Z',
        'HUNTERSTART X',
        'HUNTERSTART Y',
        'HUNTERSTART Z',
        'IGNORE',
        'IND_ESCAPE_FLOOR',
        'IND_ESCAPE_FLOOR_02',
        'IND_METALPANEL',
        'IND_WAREHOUSE_WALL_B',
        'INMATE1',
        'INMATE2',
        'INMATE3',
        'INTENSITY',
        'IRON_BAR_DOOR',
        'IS REAL LIGHT',
        'IS STREAMED',
        'JAILDOORM',
        'JAILDOORS',
        'KICKABLE',
        'LAMP',
        'LATON',
        'LBLOOD2',
        'LBLOOD2_X',
        'LBLOOD4',
        'LBLOOD4_X',
        'LBLOOD7',
        'LBLOOD7_X',
        'LBLOOD8',
        'LBLOOD8_X',
        'LBLOODA',
        'LBLOODF',
        'LBLOODJ',
        'LBLOODJ_X',
        'LBLOODK',
        'LBLOODK_X',
        'LBLOODP',
        'LBLOODP_X',
        'LEGION_I_KNOW_ANIM',
        'LEGION_KICK_PROP_ANIM',
        'LEGION_TALK_PROP_ANIM',
        'LENS FLARE',
        'LENSFLARE INTENSITY',
        'LENSFLARE SIZE',
        'LIGHT EFFECT TYPE',
        'LIGHT FOG',
        'LIGHT TYPE',
        'LIGHT_BREAK',
        'LINE OF SIGHT',
        'LIVEROOM1',
        'LIVEROOM2',
        'LOCKABLE',
        'LOCKBREAK',
        'LOCKED',
        'LOCKEDDOOR',
        'LOCKEDDOOR_CHAIN',
        'LOCKMETAL',
        'LOD',
        'LODNEAR',
        'LOD_DATA1',
        'LOD_DATA2',
        'LOD_DATA3',
        'LOD_DATA4',
        'LOWAMBIENT',
        'MACE_MELEE_COLLISION',
        'MATERIAL',
        'MAX DISTANCE',
        'MAXOPENANGLEIN',
        'MAXOPENANGLEOUT',
        'MEDFAC_BRUSHED_METAL',
        'MEDFAC_DUCTING',
        'MEDFAC_ENVMAP',
        'MEDFAC_OP_THEATRE_CEILING_V3',
        'MEDFAC_VENT',
        'MEDICINE_CON_CEILING',
        'MEDIC_BED_CORPSE',
        'METAL',
        'METAL0015',
        'METALHARD',
        'METALRAILING',
        'METAL_CAGE_SHUTTER',
        'METAL_CONTAINER',
        'METAL_GATE_ASYLUM',
        'METAL_GATE_ASYLUM002',
        'METAL_LARGE_SHUTTER',
        'METAL_NOISY',
        'METAL_PIPE_WC001',
        'METAL_POLE',
        'METAL_POLE_JOINT',
        'METAL_SMALL_SHUTTER',
        'METAL_STUDDEDSTRIP01',
        'METAL_THICK_METAL_PLATE',
        'METAL_WC_DRAIN',
        'METAL_WC_DRAIN2',
        'MIN DISTANCE',
        'MOTIONLITE',
        'MOTOR',
        'MOVE_MENU',
        'MP_BRICK_128',
        'MP_BURN_CEILING',
        'MP_CARTER_CEILING',
        'MP_CARTER_SMOOTHWALL',
        'MP_CARTER_WALLBOT',
        'MP_CARTER_WHITEWALL',
        'MP_CELLWALL_128',
        'MP_CELLWALL_SCROLL',
        'MP_CLEANTILES_256',
        'MP_COP_FLOOR1',
        'MP_COP_SEP',
        'MP_COP_SKIRTING',
        'MP_COP_VINYL',
        'MP_GUARDTOWERTHIN_128',
        'MP_GUN_METAL',
        'MP_SHOWERROOM',
        'MP_STINKINGWALLNOBAR_128',
        'MP_STINKINGWALLWHITE_128',
        'MP_STINKINGWALL_128',
        'MUD',
        'NAME IN SAMPLEBANK',
        'NEON1',
        'NEON2',
        'NEON3',
        'NIGHTSTICK_MELEE_COLLISION',
        'NOISY',
        'NONE',
        'NOT CLIMBABLE',
        'OBJECT ANIMATION',
        'OCCLUSION IGNORANCE',
        'OLD_PLANKS',
        'OPANDTRWINDOW',
        'OPAQUEGLASS',
        'OPEN_HEAVY_METAL',
        'OPEN_IRONBAR_DOOR',
        'OPEN_METAL',
        'OPEN_WIRE',
        'OPEN_WOOD',
        'OVER21',
        'PADLOCK',
        'PAPER',
        'PED',
        'PEDBARRIER',
        'PEDBARRIER_CARPET',
        'PEDBARRIER_METAL',
        'PEDBARRIER_STONE',
        'PEDBARRIER_WIREMESH',
        'PEDBARRIER_WOOD',
        'PEN_MELEE_COLLISION',
        'PHONE',
        'PHYSICS',
        'PICKUP_HEALTH',
        'PICKUP_WEAPON',
        'PICTURESMASH',
        'PIGSY_GRATING',
        'PISS',
        'PLASTER',
        'PLASTIC',
        'PLASTICBAG',
        'PLASTIC_SURFACE_BLACK',
        'PLASTIC_SURFACE_ORANGE',
        'PLASTIC_WRAP',
        'PLAYERLOOK X',
        'PLAYERLOOK Z',
        'PLAYERSTART X',
        'PLAYERSTART Y',
        'PLAYERSTART Z',
        'PLAYER_BREATHE_IN_1',
        'PLAYER_BREATHE_IN_2',
        'PLAYER_BREATHE_IN_3',
        'PLAYER_BREATHE_IN_4',
        'PLAYER_BREATHE_IN_5',
        'PLAYER_BREATHE_OUT_1',
        'PLAYER_BREATHE_OUT_2',
        'PLAYER_BREATHE_OUT_3',
        'PLAYER_BREATHE_OUT_4',
        'PLAYER_BREATHE_OUT_5',
        'PLAZA_FLOOR_BIG001',
        'PLIERS_MELEE_COLLISION',
        'POSTBOX',
        'PP_METAL013',
        'PP_METAL015',
        'PP_WALL0014',
        'PP_WALL007B',
        'PP_WINDOW007',
        'PP_WINDOW011A',
        'PP_WINDOW011B',
        'PP_WINDOW012SMALL',
        'PRONE',
        'PUDDLE',
        'PUKE',
        'PUKEFLOOR',
        'PUNCH_PED_BODY',
        'PUNCH_PED_BODY_X',
        'PUNCH_PED_FACE',
        'PUNCH_PED_FACE_X',
        'RADIUS',
        'RATEDE_MELEE_COLLISION',
        'REACT_TO_LIGHT',
        'REAKS_VENDING_ANIM',
        'RUBBERFISTSM',
        'RUBBLE',
        'SAFETYGLASS01',
        'SCENE LIGHT',
        'SELECT_LEFT',
        'SELECT_RIGHT',
        'SHADOWCIRCLE',
        'SHADOWS',
        'SHARD_DESTRUCT',
        'SHARD_MELEE_COLLISION',
        'SHARP_MELEE_COLLISION',
        'SHARP_MELEE_COLLISION_X',
        'SHEET1',
        'SHOPPING_TROLLEY',
        'SINKDRIP1',
        'SINKDRIPDETAIL',
        'SIZE',
        'SKY_LIGHTB',
        'SLAM_HEAVY_METAL',
        'SLAM_IRONBAR_DOOR',
        'SLAM_METAL',
        'SLAM_WIRE',
        'SLAM_WOOD',
        'SLEDGEHAMMER_MELEE_COLLISION',
        'SLIDE_CLOSE',
        'SLIDE_HORIZONTAL',
        'SLIDE_OPEN',
        'SLIDE_VERTICAL',
        'SLOT1',
        'SLOT2',
        'SLOT3',
        'SL_ASYLMCIELING02',
        'SL_ASYMFLOOR01',
        'SL_CONCRETEFLR',
        'SL_CONCWALL01',
        'SL_IND_GRATE',
        'SL_PBARS1',
        'SMALLDRIP',
        'SMALLDRIPECHO',
        'SMALLDRIPTAIL',
        'SMASHABLE',
        'SMASHLOCK',
        'SMHALL_WALL001',
        'SM_BAR_SEATS_RED',
        'SM_BAR_SEATS_RED_BOT',
        'SM_BLACK_TILES001',
        'SM_BRICKDIRT001',
        'SM_CARPET01',
        'SM_CHROMEPILLAR',
        'SM_CONCRETE001',
        'SM_CONCRETE002',
        'SM_COVING01',
        'SM_DIRTFLOOR001',
        'SM_DIRTWINDOW002',
        'SM_DOOR_FRAME_DIRT',
        'SM_DUNGEON_WALL001',
        'SM_DUNGEON_WALL001B',
        'SM_DUNGEON_WALL002',
        'SM_DUNGEON_WALL003',
        'SM_DUNGEON_WALL004',
        'SM_DUNGEON_WALL005A',
        'SM_DUNGEON_WALL005B',
        'SM_DUNGEON_WALL005C',
        'SM_DUNGEON_WALL006A',
        'SM_FLOORR',
        'SM_FLOOR_BLACK',
        'SM_FLOOR_ORANGETILES',
        'SM_GREEN_WALL001',
        'SM_GREY_WALL001',
        'SM_LIGHTFLOOR001',
        'SM_LIGHTFLOOR001B',
        'SM_METAL_CONSTR001',
        'SM_NEONLIGHT001',
        'SM_NEONLIGHT002',
        'SM_PAINTEDBRICK_RED03',
        'SM_PAINTEDBRICK_RED04',
        'SM_PAINTED_RED02',
        'SM_POSTER01',
        'SM_POSTER02',
        'SM_POSTER03',
        'SM_POSTER04',
        'SM_RAILINGS001',
        'SM_RAILINGS001SHADOW',
        'SM_REDCOUCH001',
        'SM_REDCROSS',
        'SM_RED_CURTAINS001',
        'SM_STAIRCASE001',
        'SM_STAIRCASE002',
        'SM_STEPS_DIRT',
        'SM_STONEORNAMENT',
        'SM_STONEPILLAR001',
        'SM_STONEPILLAR002',
        'SM_STONEPILLAR002B',
        'SM_STONEWALL001',
        'SM_STONEWALL001_BORDER',
        'SM_STONEWALL002',
        'SM_STONEWALL005',
        'SM_STONEWALL006',
        'SM_TESTFLOOR001',
        'SM_TESTWALL001',
        'SM_TESTWALL002',
        'SM_WC_CEILINGTILES',
        'SM_WINDOWFRAME01',
        'SM_WOOD001',
        'SM_WOOD002',
        'SNOOKER',
        'SOFT',
        'SOR_FLOOR',
        'SOR_FLOOR_DL2',
        'SPARKS',
        'SPOOKY1',
        'SPOOKY2',
        'SPOOKY3',
        'STAB_FLESH',
        'STAB_FLESH_X',
        'STAB_REMOVE',
        'STAIRCASE_STONE_ASYLUM',
        'START',
        'STATIC',
        'STONE',
        'STONELIGHT',
        'STONESCUFF',
        'STREAM ID',
        'STREETLIGHT',
        'STUNPROD_MELEE_COLLISION',
        'SURGERY_WALL_ASYLUM001',
        'SWING',
        'SWINGLOW2',
        'SWINGLOW2_X',
        'SWING_HI',
        'SWING_HI_X',
        'SWING_LO',
        'SWING_LO_X',
        'SWING_X',
        'SWITCH OFF AFTER DURATION',
        'SWITCH ON BY DEFAULT',
        'SWITCH_BUTTON_ON',
        'SWITCH_CLICK_OFF',
        'SWITCH_CLICK_ON',
        'SWITCH_LEVER_DOWN',
        'SWITCH_LEVER_UP',
        'SWITCH_WEAPON',
        'SYRINGE_DESTRUCT',
        'SYRINGE_MELEE_COLLISION',
        'TARMAC',
        'TELEWIRESLONG',
        'TEXTBOX',
        'THUNDER',
        'TILE',
        'TILETOP',
        'TL_SURGERY_WALL_ASYLUM001_LOWER',
        'TOGGLE',
        'TOILET',
        'TRADESSIGN',
        'TRANSFORMER2',
        'TRANSPARENT',
        'TRASHCAN',
        'TRIGGER PROBABILITY',
        'TRIGGER TIMEOUT',
        'TV',
        'TV1',
        'TVSTAT',
        'TYPE',
        'TYRE',
        'UNLOCK1',
        'UPT_PRECINCT_TILEDWALL',
        'URINAL',
        'URINALFLUSH',
        'USE DEFAULT AI',
        'VENT03',
        'VOLUME',
        'WALL_MARBLESTONE_GREY',
        'WALL_WIND001',
        'WALL_WIND002',
        'WARDMETAL',
        'WARDWOOD',
        'WATCHDOG_CHECK_CAM_ANIM',
        'WATCHDOG_SMOKE_ANIM',
        'WATCHDOG_WINDOW_ANIM',
        'WATER',
        'WATERDETAIL1',
        'WATERDETAIL2',
        'WATERDRIP',
        'WATERLOOP1',
        'WC_DOOR_METAL001',
        'WEAPON',
        'WEAPON2',
        'WEIGHTON',
        'WET_MUD',
        'WHEELY_BIN',
        'WHITEWALLBRICK',
        'WHITEWALLBRICK02',
        'WHITE_NOISE',
        'WHITE_NOISE_LP',
        'WINDOW001',
        'WINDOW_BIG_ASYLUM001',
        'WINDOW_TILE001',
        'WIREMESH',
        'WOOD',
        'WOODHARD',
        'WOODSCUFF',
        'WOOD_BAT_MELEE_COLLISION',
        'WOOD_BIG_DOOR',
        'WOOD_BOARD',
        'WOOD_DETAIL01',
        'WOOD_DETAIL02',
        'WOOD_DETAIL03',
        'WOOD_DOOR',
        'WOOD_PLANK',
        'WOOD_THIN',
        'WOOD_TRUNK',
        'WOOSH',
        'WOOSHHIGH',
        'WOOSHHIGH_X',
        'WOOSHLOW',
        'WOOSHLOW_X',
        'WOOSH_X',
        'WS_ALLEY2_128_DIRT',
        'X_BLOCK_WITH_ARM_X',
        'X_BODYBLOW_X',
        'X_CUTTING_MELEE_COLLISION_X',
        'X_FLESH_SQUELCH_X',
        'X_HEAVY_FLESH_HIT_X',
        'X_PUNCH_PED_BODY_X',
        'X_PUNCH_PED_FACE_X',
        'X_SHARP_MELEE_COLLISION_X',
        'X_STAB_FLESH_X',
        'X_STAB_REMOVE',
        'X_SWINGLOW2_X',
        'X_SWING_HI_X',
        'X_SWING_LO_X',
        'X_SWING_X',
        'X_WOOSHHIGH_X',
        'X_WOOSHLOW_X',
        'X_WOOSH_X',
        'ZOOMIN',
        'ZOOMOUT'
    ];
    

    private static $hashes = [];

    /**
     * From Manhunt2.exe 005890A0
     * Found and converted to C by MAJESTIC_R5
     * Ported to PHP by Sor3nt
     *
     * Convert the given string into a hash value
     *
     * @param $str
     * @return string
     */
    public static function calcHash($str){
        $str = strtoupper($str);
        $hash = 0;

        for($c = 0; $c < strlen($str); $c++){
            $hash = ($hash * 33 + ((((ord($str[$c]) - 97)) & 0xff  <= 25) ? ord($str[$c]) - 32 : ord($str[$c]))) << 32 >> 32;
        }

        return Helper::fromIntToHex($hash);
    }

    public static function generateHash(){

        foreach (self::$hashNames as $hashName) {
            self::$hashes[self::calcHash($hashName)] = $hashName;
        }
    }

    public static function getNameByHash($hash){
        if (count(self::$hashes) == 0) self::generateHash();

        return isset(self::$hashes[$hash]) ? self::$hashes[$hash] : false;
    }

    public static function getHashByName($name){
        if (in_array($name, self::$hashNames) !== false){

            $hash = self::calcHash($name);
//            $hash = Helper::toBigEndian(self::$availableParams[$name]);

            return $hash;
        }

//        $hash = Helper::toBigEndian($name);
        return $name;
//        return self::calcHash($name);
    }

    public function __construct()
    {
        $this->functionEventDefinition = array_merge($this->functionEventDefinition, [
            '__default__' => '68000000'
        ]);

        $this->functionForceFloat = array_merge($this->functionForceFloat, [
            'aidefinegoalbebuddy' => [false, false, false, true, false],
        ]);
    }

}
