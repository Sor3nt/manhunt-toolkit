<?php
namespace App\Service\Compiler\FunctionMap;

class Manhunt
{


    /**
     * some functions need explicit float parameters
     * when someone give a 10 instead of 10.0 we need to
     * tell te engine to convert the value
     *
     * Sample Input: SetVector(pos, 1, 23.45, 67.89);
     * the function require 3 floats but the first value is a int.
     * convert it to a float with 0x4d 0x10 0x01
     */
    public static $functionForceFloar = [
    ];

    public static $functionEventDefinition = [
        '__default__' => '54000000'
    ];

    public static $constants = [

        'voice_hooded3voice2' => [
            'offset' => '05000000'
        ],

        'aispeech_h3v2_uni_long_pain' => [
            'offset' => '02000000'
        ],

        'aispeech_h1v1_uni_long_pain' => [
            'offset' => 'aispeech_h1v1_uni_long_pain'
        ],

        'aispeech_h2v1_der_laughter' => [
            'offset' => '27000000'
        ],

        'ct_brick_half' => [
            'offset' => '38000000'
        ],

        'ct_w_baseball_bat' => [
            'offset' => '23000000'
        ],

        'mtt_defensive_hard' => [
            'offset' => 'mtt_defensive_hard'
        ],

        'aiscript_idle_moveanims' => [
            'offset' => '04000000'
        ],

        'aiscript_idle_speech' => [
            'offset' => '03000000'
        ],

        'aispeech_h2v1_uni_long_pain' => [
            'offset' => '01000000'
        ],

        'aispeech_h1v1_der_urinating' => [
            'offset' => '25000000'
        ],
        'voice_hooded1voice1' => [
            'offset' => 'voice_hooded1voice1'
        ],
        'ct_small_bat' => [
            'offset' => '21000000'
        ],
        'aispeech_d1v1_der_exit' => [
            'offset' => '34000000'
        ],
        'aispeech_d1v1_der_crowbar' => [
            'offset' => '32000000'
        ],

        'scripted_track_pdoor' => [
            'offset' => 'scripted_track_pdoor'
        ],

        'aispeech_d1v1_der_level_start' => [
            'offset' => '2f000000'
        ],

        'map_color_blue' => [
            'offset' => '06000000'
        ],


        'sfx_script_slot_zip' => [
            'offset' => 'ea360000'
        ],

        'sfx_script_slot_piss' => [
            'offset' => 'e9360000'
        ],


        'voice_hooded2voice1' => [
            'offset' => '02000000'
        ],


        'scripted_track_direct' => [
            'offset' => 'scripted_track_direct'
        ],

        'voice_piggsy1voice1' => [
            'offset' => 'voice_piggsy1voice1'
        ],

        'searchreqid_runtoinvestigate' => [
            'offset' => 'searchreqid_runtoinvestigate'
        ],
        'searchreqid_negativechase' => [
            'offset' => 'searchreqid_negativechase'
        ],
        'weather_clear' => [
            'offset' => 'weather_clear'
        ],

        'aispeech_d1v1_trf_hoods_carpark' => [
            'offset' => '2a000000'
        ],

        'aispeech_d1v1_trf_hoods_mall_entrance' => [
            'offset' => '2c000000'
        ],

        'aispeech_d1v1_brn_first_execution' => [
            'offset' => '28000000'
        ],

        'aiscript_veryhighpriority' => [
            'offset' => 'aiscript_veryhighpriority'
        ],

        'aispeech_d1v1_trf_hoods_gladiator_court' => [
            'offset' => '29000000'
        ],

        'hud_man' => [
            'offset' => '02000000'
        ],

        'ct_shard' => [
            'offset' => '13000000'
        ],

        'hud_all_off' => [
            'offset' => 'hud_all_off'
        ],

        'mtt_training' => [
            'offset' => 'mtt_training'
        ],

        'hud_health' => [
            'offset' => '04000000'
        ],
        'col_hunter' => [
            'offset' => '10000000'
        ],
        'hud_map' => [
            'offset' => '01000000'
        ],

        'hud_inventory' => [
            'offset' => '10000000'
        ],

        'ct_bag' => [
            'offset' => '3a000000'
        ],

        'hud_all_on' => [
            'offset' => 'ff000000'
        ],

        'difficulty_easy' => [
            'offset' => 'difficulty_easy'
        ],
        'aispeech_d1v1_trf_hoods_level_start' => [
            'offset' => '27000000'
        ],
        'aiscript_idle_standstill' => [
            'offset' => '02000000'
        ],
        'aiscript_runmovespeed' => [
            'offset' => 'aiscript_runmovespeed'
        ],
        'hud_stamina' => [
            'offset' => '08000000'
        ],
        'CT_TRIPWIRE' => [
            'offset' => '01000000'
        ],
        'CT_GASOLINE' => [
            'offset' => '02000000'
        ],
        'CT_WATER' => [
            'offset' => '03000000'
        ],
        'CT_LIGHTER' => [
            'offset' => '04000000'
        ],
        'CT_CASH' => [
            'offset' => '05000000'
        ],
        'CT_TORCH' => [
            'offset' => '06000000'
        ],
        'CT_N_NIGHTVISION' => [
            'offset' => '07000000'
        ],
        'CT_PAINKILLERS' => [
            'offset' => '08000000'
        ],
        'CT_G_FIRST_AID' => [
            'offset' => '09000000'
        ],
        'CT_Y_FIRST_AID' => [
            'offset' => '0A000000'
        ],
        'CT_SPEED_BOOST' => [
            'offset' => '0B000000'
        ],
        'CT_STRENGHT_BOOST' => [
            'offset' => '0C000000'
        ],
        'CT_SHOOTING_BOOST' => [
            'offset' => '0D000000'
        ],
        'CT_REFLEXES_BOOST' => [
            'offset' => '0E000000'
        ],
        'CT_HEALTH_BOOST' => [
            'offset' => '0F000000'
        ],
        'CT_FISTS' => [
            'offset' => '12000000'
        ],
        'CT_KNIFE' => [
            'offset' => '13000000'
        ],
        'CT_SHARD' => [
            'offset' => '13000000'
        ],
        'CT_BROKEN_BOTTLE' => [
            'offset' => '14000000'
        ],
        'CT_JURYBLADES' => [
            'offset' => '15000000'
        ],
        'CT_BOTTLE' => [
            'offset' => '16000000'
        ],
        'CT_PIPE' => [
            'offset' => '17000000'
        ],
        'CT_CLEAVER' => [
            'offset' => '18000000'
        ],
        'CT_WOODEN_BAR' => [
            'offset' => '19000000'
        ],
        'CT_CROWBAR' => [
            'offset' => '1A000000'
        ],
        'CT_AXE' => [
            'offset' => '1E000000'
        ],
        'CT_ICEPICK' => [
            'offset' => '1F000000'
        ],
        'CT_MACHETE' => [
            'offset' => '20000000'
        ],
        'CT_SMALL_BAT' => [
            'offset' => '21000000'
        ],
        'CT_BASEBALL_BAT' => [
            'offset' => '22000000'
        ],
        'CT_W_BASEBALL_BAT' => [
            'offset' => '23000000'
        ],
        'CT_FIRE_AXE' => [
            'offset' => '24000000'
        ],
        'CT_BASEBALL_BAT_BLADES' => [
            'offset' => '26000000'
        ],
        'CT_6SHOOTER' => [
            'offset' => '27000000'
        ],
        'CT_GLOCK' => [
            'offset' => '28000000'
        ],
        'CT_GLOCK_SILENCED' => [
            'offset' => '29000000'
        ],
        'CT_GLOCK_TORCH' => [
            'offset' => '2A000000'
        ],
        'CT_UZI' => [
            'offset' => '2B000000'
        ],
        'CT_SHOTGUN' => [
            'offset' => '2C000000'
        ],
        'CT_SHOTGUN_TORCH' => [
            'offset' => '2D000000'
        ],
        'CT_COLT_COMMANDO' => [
            'offset' => '2F000000'
        ],
        'CT_DESERT_EAGLE' => [
            'offset' => '2E000000'
        ],
        'CT_SNIPER_RIFLE' => [
            'offset' => '30000000'
        ],
        'CT_SNIPER_RIFLE_SILENCED' => [
            'offset' => '30000000'
        ],
        'CT_TRANQ_RIFLE' => [
            'offset' => '31000000'
        ],
        'CT_SAWNOFF' => [
            'offset' => '32000000'
        ],
        'CT_GRENADE' => [
            'offset' => '33000000'
        ],
        'CT_MOLOTOV' => [
            'offset' => '34000000'
        ],
        'CT_EXPMOLOTOV' => [
            'offset' => '35000000'
        ],
        'CT_TEAR_GAS' => [
            'offset' => '36000000'
        ],
        'CT_FLASH' => [
            'offset' => '37000000'
        ],
        'CT_BRICK_HALF' => [
            'offset' => '38000000'
        ],
        'CT_FIREWORK' => [
            'offset' => '39000000'
        ],
        'CT_CAN' => [
            'offset' => '5B000000'
        ],
        'CT_RAG' => [
            'offset' => '3B000000'
        ],
        'CT_CHLORINE' => [
            'offset' => '3B000000'
        ],
        'CT_METHS' => [
            'offset' => '3B000000'
        ],
        'CT_HCC' => [
            'offset' => '3E000000'
        ],
        'CT_D_BEER_GUY' => [
            'offset' => '3F000000'
        ],
        'CT_D_MERC_LEAD' => [
            'offset' => '40000000'
        ],
        'CT_D_SMILEY' => [
            'offset' => '41000000'
        ],
        'CT_D_HUNTLORD' => [
            'offset' => '42000000'
        ],
        'CT_CANE' => [
            'offset' => '1D000000'
        ],
        'CT_NIGHTSTICK' => [
            'offset' => '1C000000'
        ],
        'CT_K_DUST' => [
            'offset' => '11000000'
        ],
        'CT_E_L_SIGHT' => [
            'offset' => '43000000'
        ],
        'CT_S_SILENCER' => [
            'offset' => '44000000'
        ],
        'CT_RADIO' => [
            'offset' => '45000000'
        ],
        'CT_BAR_KEY' => [
            'offset' => '46000000'
        ],
        'CT_SYARD_COMB' => [
            'offset' => '47000000'
        ],
        'CT_CAMERA' => [
            'offset' => '48000000'
        ],
        'CT_BODY_P1' => [
            'offset' => '49000000'
        ],
        'CT_BODY_P2' => [
            'offset' => '4A000000'
        ],
        'CT_PREC_KEY' => [
            'offset' => '4B000000'
        ],
        'CT_PREC_DOCS' => [
            'offset' => '4C000000'
        ],
        //  for hunters!
        'CT_CHAINSAW' => [
            'offset' => '58000000'
        ],
        'CT_CHAINSAW_PLAYER' => [
            'offset' => '6C000000'
        ],
        'CT_BAG' => [
            'offset' => '3A000000'
        ],
        'CT_WIRE' => [
            'offset' => '5A000000'
        ],
        'CT_WOODEN_SPIKE' => [
            'offset' => '5C000000'
        ],
        'CT_PIGSY_WIRE' => [
            'offset' => '5F000000'
        ],
        'CT_PIGSY_SHARD' => [
            'offset' => '5E000000'
        ],
        'CT_PIGSY_SPIKE' => [
            'offset' => '60000000'
        ],
        'CT_HAMMER' => [
            'offset' => '61000000'
        ],
        'CT_KEY' => [
            'offset' => '53000000'
        ],
        'CT_NAILGUN' => [
            'offset' => '59000000'
        ],
        'CT_HANDYCAM' => [
            'offset' => '6E000000'
        ],
        // Ammo
        'CT_AMMO_NAILS' => [
            'offset' => '66000000'
        ],
        'CT_AMMO_SHOTGUN' => [
            'offset' => '67000000'
        ],
        'CT_AMMO_PISTOL' => [
            'offset' => '68000000'
        ],
        'CT_AMMO_MGUN' => [
            'offset' => '69000000'
        ],
        'CT_AMMO_TRANQ' => [
            'offset' => '6A000000'
        ],

        'CT_NO_ITEM' => [
            'offset' => '6F000000'
        ],

        'aiscript_mediumpriority' => [
            'offset' => '02000000'
        ],

        'aiscript_highpriority' => [
            'offset' => '01000000'
        ],

        'aiscript_walkmovespeed' => [
            'offset' => '01000000'
        ],

        'aiscript_lowpriority' => [
            'offset' => '03000000'
        ],

        'combattypeid_melee' => [
            'offset' => '00000000'
        ],

        'combattypeid_cover' => [
            'offset' => '02000000'
        ],

        'combattypeid_open' => [
            'offset' => '01000000'
        ],
        'combattypeid_open_melee' => [
            'offset' => '03000000'
        ],
        'col_basic' => [
            'offset' => '01000000'
        ],
        'door_closed' => [
            'offset' => '02000000'
        ],
        'aiscript_graphlink_allow_everything' => [
            'offset' => '03000000'
        ],
        'aiscript_graphlink_allow_nothing' => [
            'offset' => '00000000'
        ],
        'voice_smiley3voice1' => [
            'offset' => '1d000000'
        ],
        'aiscript_idle_patrol' => [
            'offset' => '01000000'
        ],
        'aispeech_s3v1_asy_close_door' => [
            'offset' => '11000000'
        ],
        'aispeech_s2v1_asy_close_door' => [
            'offset' => '10000000'
        ],
        'voice_smiley2voice1' => [
            'offset' => '1b000000'
        ],
        'voice_smiley1voice1' => [
            'offset' => '19000000'
        ],
        'aispeech_s1v1_asy_close_door' => [
            'offset' => '0f000000'
        ],
        'aiscript_idle_wandersearch' => [
            'offset' => '00000000'
        ],

    ];

    public static $functions = [



        'settimer' => [
            'name' => 'SetTimer',
            'offset' => 'ce020000',
            /**
             * Parameters
             * 1: Minutes
             * 2: Seconds>
             */
            'params' => ['integer', 'integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        'getplayerlevelrestarts' => [
            'name' => 'getplayerlevelrestarts',
            'offset' => '89020000',
            'return' => 'integer'

        ],


        'aientitygohomeifidle' => [
            'name' => 'aientitygohomeifidle',
            'offset' => '15020000',
        ],
        'aisethunteridleactionminmaxradius' => [
            'name' => 'aisethunteridleactionminmaxradius',
            'offset' => 'a3010000',
        ],

        'graphmodifyconnections' => [
            'name' => 'graphmodifyconnections',
            'offset' => 'e8000000',
        ],

        'lockentity' => [
            'name' => 'lockentity',
            'offset' => '97000000',
        ],

        'ainumberinsubpack' => [
            'name' => 'ainumberinsubpack',
            'offset' => '66010000',
            'return' => 'integer'
        ],



        'aisethunterhomenodedirection' => [
            'name' => 'aisethunterhomenodedirection',
            'offset' => '9c010000',
        ],
        'airemovegoalfromsubpack' => [
            'name' => 'airemovegoalfromsubpack',
            'offset' => '56010000',
        ],
        'setentityinvulnerable' => [
            'name' => 'setentityinvulnerable',
            'offset' => '5d010000',
        ],
        'setpeddonotdecay' => [
            'name' => 'setpeddonotdecay',
            'offset' => '68020000',
        ],
        'playhunterspeech' => [
            'name' => 'playhunterspeech',
            'offset' => '99020000',
        ],

        'destroyentity' => [
            'name' => 'destroyentity',
            'offset' => '9d020000',
        ],

        'aisetidlepatrolstop' => [
            'name' => 'aisetidlepatrolstop',
            'offset' => 'a5010000',
        ],

        'aisethunteridlepatrol' => [
            'name' => 'aisethunteridlepatrol',
            'offset' => 'a2010000',
        ],


        'sethunterhidehealth' => [
            'name' => 'sethunterhidehealth',
            'offset' => 'ed010000',
        ],



        'sethunterhitaccuracy' => [
            'name' => 'sethunterhitaccuracy',
            'offset' => 'aa010000',
        ],


        'aisetidlehomenode' => [
            'name' => 'aisetidlehomenode',
            'offset' => '82010000',
        ],

        'aisetentityvoiceid' => [
            'name' => 'aisetentityvoiceid',
            'offset' => '6f020000',
        ],


        'sethunterheadskin' => [
            'name' => 'sethunterheadskin',
            'offset' => '45020000',
        ],

        'sethunterskin' => [
            'name' => 'sethunterskin',
            'offset' => '42020000',
        ],

        'entityignorecollisions' => [
            'name' => 'entityignorecollisions',
            'offset' => '9f020000',
        ],

        'cutsceneend' => [
            'name' => 'cutsceneend',
            'offset' => '48010000',
        ],

        'loadscriptaudioslot' => [
            'name' => 'loadscriptaudioslot',
            'offset' => 'be020000',
        ],


        'removeentity' => [
            'name' => 'removeentity',
            'offset' => '80000000',
        ],

        'setdooroverrideangle' => [
            'name' => 'setdooroverrideangle',
            'offset' => '95020000',
        ],

        'setcameraview' => [
            'name' => 'setcameraview',
            'offset' => '8e010000',
        ],

        'setcameraposition' => [
            'name' => 'setcameraposition',
            'offset' => '91010000',
        ],
        'cutscenestart' => [
            'name' => 'cutscenestart',
            'offset' => '47010000',
        ],

        'isexecutioninprogress' => [
            'name' => 'isexecutioninprogress',
            'offset' => '4e020000',
        ],

        'aiisgoalnameinsubpack' => [
            'name' => 'aiisgoalnameinsubpack',
            'offset' => 'a2020000',
            'return' => 'Boolean'
        ],

        'setdooropenangleout' => [
            'name' => 'setdooropenangleout',
            'offset' => 'd3010000',
        ],
        'killeffect' => [
            'name' => 'killeffect',
            'offset' => 'a9000000',
        ],
        'removescript' => [
            'name' => 'removescript',
            'offset' => 'e5000000',
        ],

        'aientitycancelanim' => [
            'name' => 'aientitycancelanim',
            'offset' => '14020000',
        ],

        'endscriptaudioslotlooped' => [
            'name' => 'endscriptaudioslotlooped',
            'offset' => 'c4020000',
        ],

        'unlockentity' => [
            'name' => 'unlockentity',
            'offset' => '98000000',
        ],


        'setdoorstate' => [
            'name' => 'setdoorstate',
            'offset' => '96000000',
        ],


        'killentity' => [
            'name' => 'killentity',
            'offset' => '7f000000',
        ],


        'unfreezeentity' => [
            'name' => 'unfreezeentity',
            'offset' => '37010000',
        ],

        'runscript' => [
            'name' => 'runscript',
            'offset' => 'e3000000',
        ],

        'createboxtrigger' => [
            'name' => 'createboxtrigger',
            'offset' => '27010000',
        ],

        'triggeraddentityclass' => [
            'name' => 'triggeraddentityclass',
            'offset' => '0d020000',
        ],

        'createspheretrigger' => [
            'name' => 'createspheretrigger',
            'offset' => 'a2000000',
        ],

        'aiassociatefouractiveareaswithplayerarea' => [
            'name' => 'aiassociatefouractiveareaswithplayerarea',
            'offset' => 'bd010000',
        ],

        'aiassociatethreeactiveareaswithplayerarea' => [
            'name' => 'aiassociatethreeactiveareaswithplayerarea',
            'offset' => 'bc010000',
        ],

        'aiclearallactiveareaassociations' => [
            'name' => 'aiclearallactiveareaassociations',
            'offset' => 'b9010000',
        ],

        'aisubpackstayinterritory' => [
            'name' => 'aisubpackstayinterritory',
            'offset' => 'd4010000',
        ],

        'aiaddgoalforsubpack' => [
            'name' => 'aiaddgoalforsubpack',
            'offset' => '55010000',
        ],

        'aiaddareaforsubpack' => [
            'name' => 'aiaddareaforsubpack',
            'offset' => '77010000',
        ],

        'aisetleaderinvisible' => [
            'name' => 'aisetleaderinvisible',
            'offset' => '6a020000',
        ],


        'aidefinegoalgotonode' => [
            'name' => 'aidefinegoalgotonode',
            'offset' => '6e010000',
        ],


        'aidefinegoalguarddirection' => [
            'name' => 'aidefinegoalguarddirection',
            'offset' => 'af010000',

        ],

        'aiguardmodifyshootoutsideradius' => [
            'name' => 'aiguardmodifyshootoutsideradius',
            'offset' => 'cc010000',
        ],

        'aidefinegoalhuntenemy' => [
            'name' => 'aidefinegoalhuntenemy',
            'offset' => '57010000',
        ],

        'getplayerareaname' => [
            'name' => 'getplayerareaname',
            'offset' => '1c010000',
            'return' => 'string'
        ],

        'spawnmovingentity' => [
            'name' => 'spawnmovingentity',
            'offset' => '79000000'
        ],

        'isentityalive' => [
            'name' => 'isentityalive',
            'offset' => 'a9010000',
            'return' => 'Boolean'
        ],

        'setentityscriptsfromentity' => [
            'name' => 'setentityscriptsfromentity',
            'offset' => 'd8010000'
        ],
        'writedebugflush' => [
            'name' => 'writedebugflush',
            'offset' => '73000000'
        ],
        'writedebugstring' => [
            'name' => 'writedebugstring',
            'offset' => '72000000'
        ],
        'writedebuginteger' => [
            'name' => 'writedebuginteger',
            'offset' => '6d000000'
        ],
        'switchlightoff' => [
            'name' => 'switchlightoff',
            'offset' => 'd9000000'
        ],
        'writedebuglevelvarinteger' => [
            'name' => 'writedebuglevelvarinteger',
            'offset' => '6d000000'
        ],
        'setnumberofkillablehuntersinlevel' => [
            'name' => 'SetNumberOfKillableHuntersInLevel',
            'offset' => 'e6020000'
        ],
        'aicutsceneentityenable' => [
            'name' => 'aicutsceneentityenable',
            'offset' => 'a6020000'
        ],
        'isscriptaudioslotloaded' => [
            'name' => 'isscriptaudioslotloaded',
            'offset' => 'bf020000'
        ],
        'aisetentityidleoverride' => [
            'name' => 'aisetentityidleoverride',
            'offset' => 'b4010000'
        ],
        'playscriptaudioslotoneshotfromentity' => [
            'name' => 'playscriptaudioslotoneshotfromentity',
            'offset' => 'c0020000'
        ],
        'aitriggersoundlocationknown' => [
            'name' => 'aitriggersoundlocationknown',
            'offset' => '6e020000'
        ],
        'createkillablemhfx' => [
            'name' => 'createkillablemhfx',
            'offset' => 'f1020000'
        ],
        'createmhfx' => [
            'name' => 'createmhfx',
            'offset' => '8d020000'
        ],
        'getentityposition' => [
            'name' => 'getentityposition',
            'offset' => '77000000',
            'return' => 'vec3d'
        ],
        'setdooropenanglein' => [
            'name' => 'setdooropenanglein',
            'offset' => 'd2010000'
        ],
        'createandfireweapon' => [
            'name' => 'createandfireweapon',
            'offset' => '9b020000'
        ],
        'killmhfx' => [
            'name' => 'killmhfx',
            'offset' => 'f2020000'
        ],
        'switchlighton' => [
            'name' => 'switchlighton',
            'offset' => 'da000000'
        ],
        'setlightflicker' => [
            'name' => 'setlightflicker',
            'offset' => 'de000000'
        ],
        'entityplayanim' => [
            'name' => 'entityplayanim',
            'offset' => 'a0010000'
        ],
        'rotateentityleft' => [
            'name' => 'rotateentityleft',
            'offset' => '4d000000'
        ],
        'setmaxnumberofrats' => [
            'name' => 'setmaxnumberofrats',
            'offset' => 'a5020000'
        ],

        'starttimer' => [
            'name' => 'StartTimer',
            'offset' => 'cf020000'
        ],

        'stoptimer' => [
            'name' => 'StopTimer',
            'offset' => 'd0020000'
        ],

        'showtimer' => [
            'name' => 'ShowTimer',
            'offset' => 'd2020000'
        ],

        'hidetimer' => [
            'name' => 'HideTimer',
            'offset' => 'd3020000'
        ],

        'setlevelfailed' => [
            'name' => 'SetLevelFailed',
            'offset' => '8b020000'
        ],

        'setdirectorspeechtime' => [
            'name' => 'setdirectorspeechtime',
            'offset' => 'dc020000'
        ],

        'hudtoggleflashflags' => [
            'name' => 'HUDToggleFlashFlags',
            'offset' => 'af020000',
            /**
             * Parameters
             * 1:  Hud elem
             * 2:  on/off
             */
            'params' => ['integer', 'integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        'displaygametext' => [
            'name' => 'DisplayGameText',
            'offset' => '03010000',
            'params' => ['string']

        ],


        'handcamsetvideoeffecttimecode' => [
            'name' => 'HandCamSetVideoEffectTimeCode',
            'offset' => '5f020000',
            'params' => ['integer'],
            'desc' => ''
        ],
        'handcamsetvideoeffectrecorddot' => [
            'name' => 'HandCamSetVideoEffectRecordDot',
            'offset' => '60020000',
            'params' => ['integer'],
            'desc' => ''
        ],
        'handcamsetvideoeffectfuzz' => [
            'name' => 'HandCamSetVideoEffectFuzz',
            'offset' => '61020000',
            'params' => ['integer'],
            'desc' => ''
        ],
        'handcamsetvideoeffectscrollbar' => [
            'name' => 'HandCamSetVideoEffectScrollBar',
            'offset' => '62020000',
            'params' => ['integer'],
            'desc' => ''
        ],
        'aiaddplayer' => [
            'name' => 'AIAddPlayer',
            'offset' => '5a010000',
            'params' => ['string'],
            'desc' => ''
        ],
        'setlevelgoal' => [
            'name' => 'SetLevelGoal',
            'offset' => '3e020000',
            'params' => ['string'],
            'desc' => ''
        ],

        'createinventoryitem' => [
            'name' => 'CreateInventoryItem',
            'offset' => 'b9000000',
        ],

        'aisethunteridleaction' => [
            'name' => 'aisethunteridleaction',
            'offset' => '7e010000',
        ],

        'ispadbuttonpressed' => [
            'name' => 'IsPadButtonPressed',
            'offset' => 'f9000000',
            'params' => ['integer']
        ],

        'aiaddentity' => [
            'name' => 'AIAddEntity',
            'offset' => '4c010000'
        ],
        'aisetentityasleader' => [
            'name' => 'AISetEntityAsLeader',
            'offset' => '4e010000'
        ],
        'aiaddleaderenemy' => [
            'name' => 'AIAddLeaderEnemy',
            'offset' => '53010000'
        ],
        'aientityalwaysenabled' => [
            'name' => 'AIEntityAlwaysEnabled',
            'offset' => 'be010000'
        ],
	    'aiaddsubpackforleader' => [
            'name' => 'AIAddSubpackForLeader',
            'offset' => '4f010000'
        ],
	    'aiaddhuntertoleadersubpack' => [
            'name' => 'AIAddHunterToLeaderSubPack',
            'offset' => '51010000'
        ],
        'aisethunteronradar' => [
            'name' => 'AISetHunterOnRadar',
            'offset' => 'a7010000'
        ],
        'aisetsubpackcombattype' => [
            'name' => 'AISetSubpackCombatType',
            'offset' => '81010000'
        ],
        'getentity' => [
            'name' => 'GetEntity',
            'offset' => '76000000',
            'return' => 'Entity'
        ],

        'getplayerposition' => [
            'name' => 'GetPlayerPosition',
            'offset' => '8a000000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],


        'setvector' => [
            'name' => 'SetVector',
            'offset' => '83010000'
        ],


        'aiassociateoneactiveareawithplayerarea' => [
            'name' => 'aiassociateoneactiveareawithplayerarea',
            'offset' => 'ba010000'
        ],


        'aidefinegoalgotonodeidle' => [
            'name' => 'aidefinegoalgotonodeidle',
            'offset' => 'b0010000'
        ],

        'aisethunteridleactionminmax' => [
            'name' => 'aisethunteridleactionminmax',
            'offset' => '7f010000'
        ],

        'setpedskintextureid' => [
            'name' => 'setpedskintextureid',
            'offset' => '9e010000'
        ],

        'aimakeentityblind' => [
            'name' => 'aimakeentityblind',
            'offset' => '70010000'
        ],

        'aimakeentitydeaf' => [
            'name' => 'aimakeentitydeaf',
            'offset' => '71010000'
        ],

        'sethunterruntime' => [
            'name' => 'sethunterruntime',
            'offset' => 'ee010000'
        ],

        'aicancelhunteridleaction' => [
            'name' => 'aicancelhunteridleaction',
            'offset' => '80010000'
        ],

        'assert' => [
            'name' => 'assert',
            'offset' => '6b000000'
        ],

        'setgametextteletype' => [
            'name' => 'setgametextteletype',
            'offset' => '08010000'
        ],

        'setgametextboxposition' => [
            'name' => 'setgametextboxposition',
            'offset' => '0b010000'
        ],

        'aisetsubpacksearchparams' => [
            'name' => 'aisetsubpacksearchparams',
            'offset' => 'aisetsubpacksearchparams'
        ],


        'setgametextdisplaytime' => [
            'name' => 'setgametextdisplaytime',
            'offset' => '0c010000'
        ],

        'loadfrontendaudiostream' => [
            'name' => 'loadfrontendaudiostream',
            'offset' => 'loadfrontendaudiostream'
        ],

        'showentity' => [
            'name' => 'showentity',
            'offset' => 'showentity'
        ],

        'setpedorientation' => [
            'name' => 'setpedorientation',
            'offset' => 'setpedorientation'
        ],



        'setgametextboxsize' => [
            'name' => 'setgametextboxsize',
            'offset' => '0a010000'
        ],
        'createlinetrigger' => [
            'name' => 'createlinetrigger',
            'offset' => '26010000'
        ],

        'playdirectorspeech' => [
            'name' => 'playdirectorspeech',
            'offset' => '79020000'
        ],

        'handcamsetactive' => [
            'name' => 'handcamsetactive',
            'offset' => 'ea010000'
        ],


        'playsplinefiledefault' => [
            'name' => 'playsplinefiledefault',
            'offset' => 'c9010000'
        ],

        'issplineplaying' => [
            'name' => 'issplineplaying',
            'offset' => 'cd010000'
        ],

        'whitenoisesetval' => [
            'name' => 'whitenoisesetval',
            'offset' => 'd9020000'
        ],


        'handcamsetall' => [
            'name' => 'handcamsetall',
            'offset' => 'e4010000'
        ],

        'setzoomlerp' => [
            'name' => 'setzoomlerp',
            'offset' => 'b2020000'
        ],

        'setplayerheading' => [
            'name' => 'setplayerheading',
            'offset' => '7d020000'
        ],

        'drawhud' => [
            'name' => 'drawhud',
            'offset' => 'd4020000'
        ],

        'handcamsetalleffects' => [
            'name' => 'handcamsetalleffects',
            'offset' => '77020000'
        ],


        'thislevelbeencompletedalready' => [
            'name' => 'thislevelbeencompletedalready',
            'offset' => '02030000',
            'return' => 'Boolean'
        ],


        'togglehudflag' => [
            'name' => 'togglehudflag',
            'offset' => '7c020000'
        ],

        'airemovehunterfromleadersubpack' => [
            'name' => 'airemovehunterfromleadersubpack',
            'offset' => '52010000'
        ],

        'getplayer' => [
            'name' => 'getplayer',
            'offset' => '89000000'
        ],

        'insidetrigger' => [
            'name' => 'insidetrigger',
            'offset' => 'a4000000'
        ],

        'enteredtrigger' => [
            'name' => 'enteredtrigger',
            'offset' => 'a3000000'
        ],

        'handcamsetothereffects' => [
            'name' => 'handcamsetothereffects',
            'offset' => '76020000'
        ],

        'aisetsubpackfollowthrough' => [
            'name' => 'aisetsubpackfollowthrough',
            'offset' => 'e2010000'
        ],

        'cameraforcelookatentity' => [
            'name' => 'cameraforcelookatentity',
            'offset' => '23020000'
        ],

        'camerastoplookatentity' => [
            'name' => 'camerastoplookatentity',
            'offset' => '24020000'
        ],

        'newparticleeffect' => [
            'name' => 'newparticleeffect',
            'offset' => 'a7000000'
        ],

        'getentitymatrix' => [
            'name' => 'getentitymatrix',
            'offset' => '0e010000'
        ],

        'attacheffecttomatrix' => [
            'name' => 'attacheffecttomatrix',
            'offset' => '0f010000'
        ],

        'seteffectposition' => [
            'name' => 'seteffectposition',
            'offset' => 'aa000000'
        ],

        'seteffectdirection' => [
            'name' => 'seteffectdirection',
            'offset' => 'ab000000'
        ],

        'createeffect' => [
            'name' => 'createeffect',
            'offset' => 'a8000000'
        ],

        'playscriptaudioslotloopedfromentity' => [
            'name' => 'playscriptaudioslotloopedfromentity',
            'offset' => 'c2020000'
        ],

        'aiisidle' => [
            'name' => 'aiisidle',
            'offset' => '69010000',
            'return' => 'Boolean'
        ],

        'markcutsceneasplayed' => [
            'name' => 'markcutsceneasplayed',
            'offset' => '00030000'
        ],
        'clearlevelgoal' => [
            'name' => 'clearlevelgoal',
            'offset' => '3f020000'
        ],
        'removethisscript' => [
            'name' => 'removethisscript',
            'offset' => 'e7000000'
        ],

        'sethuntermeleetraits' => [
            'name' => 'sethuntermeleetraits',
            'offset' => '74020000'
        ],

        'aisetentityallowsurprise' => [
            'name' => 'aisetentityallowsurprise',
            'offset' => '6b020000'
        ],

        'aientityplayanim' => [
            'name' => 'aientityplayanim',
            'offset' => 'b2010000'
        ],

        'sethudflag' => [
            'name' => 'sethudflag',
            'offset' => '7b020000'
        ],

        'setplayerstatusflash' => [
            'name' => 'setplayerstatusflash',
            'offset' => 'e8020000'
        ],

        'lefttrigger' => [
            'name' => 'lefttrigger',
            'offset' => 'a5000000'
        ],

        'killsubtitletext' => [
            'name' => 'killsubtitletext',
            'offset' => 'f4020000'
        ],

        'setlevelcompleted' => [
            'name' => 'setlevelcompleted',
            'offset' => '01020000'
        ],

        'setdamage' => [
            'name' => 'setdamage',
            'offset' => '2e010000'
        ],

        'forceweathertype' => [
            'name' => 'forceweathertype',
            'offset' => 'forceweathertype'
        ],

        'aidefinegoalgotoentity' => [
            'name' => 'aidefinegoalgotoentity',
            'offset' => 'aidefinegoalgotoentity'
        ],

        'initareas' => [
            'name' => 'initareas',
            'offset' => 'initareas'
        ],

        'switchlitteron' => [
            'name' => 'switchlitteron',
            'offset' => 'a1020000'
        ],

        'setpedlockonable' => [
            'name' => 'setpedlockonable',
            'offset' => '94020000'
        ],

        'aiassociatetwoactiveareaswithplayerarea' => [
            'name' => 'aiassociatetwoactiveareaswithplayerarea',
            'offset' => 'bb010000'
        ],


        'setgametextttypedisplaytime' => [
            'name' => 'setgametextttypedisplaytime',
            'offset' => 'setgametextttypedisplaytime'
        ],

        'aidefinegoalhideunnamedhunters' => [
            'name' => 'aidefinegoalhideunnamedhunters',
            'offset' => 'aidefinegoalhideunnamedhunters'
        ],

        'aidefinegoalgotoentityidle' => [
            'name' => 'aidefinegoalgotoentityidle',
            'offset' => 'aidefinegoalgotoentityidle'
        ],

        'aidefinegoalgotovector' => [
            'name' => 'aidefinegoalgotovector',
            'offset' => 'aidefinegoalgotovector'
        ],

        'aidefinegoalguard' => [
            'name' => 'aidefinegoalguard',
            'offset' => 'aidefinegoalguard'
        ],
       'getdamage' => [
            'name' => 'getdamage',
            'offset' => 'getdamage'
        ],

        'hideentity' => [
            'name' => 'hideentity',
            'offset' => 'hideentity'
        ],

        'aitriggersoundnoradar' => [
            'name' => 'aitriggersoundnoradar',
            'offset' => 'b4020000'
        ],

        'aientityspecificspeechanim' => [
            'name' => 'aientityspecificspeechanim',
            'offset' => '98020000'
        ],

        'aidefinegoalguardlookatentity' => [
            'name' => 'aidefinegoalguardlookatentity',
            'offset' => 'aidefinegoalguardlookatentity'
        ],

        'aisetsearchparams' => [
            'name' => 'aisetsearchparams',
            'offset' => 'aisetsearchparams'
        ],

        'aidefinegoalmeleeattackvector' => [
            'name' => 'aidefinegoalmeleeattackvector',
            'offset' => 'aidefinegoalmeleeattackvector'
        ],

        'radarpositionsetentity' => [
            'name' => 'radarpositionsetentity',
            'offset' => 'de020000'
        ],

        'getlastitempickedup' => [
            'name' => 'getlastitempickedup',
            'offset' => 'c8010000'
        ],

        'isnameditemininventory' => [
            'name' => 'isnameditemininventory',
            'offset' => '2f010000',
            'return' => 'boolean'
        ],

        'hascutscenebeenplayed' => [
            'name' => 'hascutscenebeenplayed',
            'offset' => '01030000',
            'return' => 'boolean'
        ],

        'isgametextdisplaying' => [
            'name' => 'isgametextdisplaying',
            'offset' => '06010000',
            'return' => 'boolean'
        ],

        'iscutsceneinprogress' => [
            'name' => 'iscutsceneinprogress',
            'offset' => 'f3020000',
            'return' => 'boolean'
        ],

        'isplayersneaking' => [
            'name' => 'isplayersneaking',
            'offset' => 'ea020000',
            'return' => 'boolean'
        ],

        'isplayerwalking' => [
            'name' => 'isplayerwalking',
            'offset' => 'eb020000',
            'return' => 'boolean'
        ],

        'getdifficultylevel' => [
            'name' => 'getdifficultylevel',
            'offset' => '9c020000',
            'return' => 'integer'
        ],

        'randnum' => [
            'name' => 'randnum',
            'offset' => '69000000',
            'return' => 'integer'
        ],


        'moveentity' => [
            'name' => 'MoveEntity',
            'offset' => '7c000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: ref to vec3d (3x float)
             * 3: integer
             */
            'params' => ['Entity', 'vec3d', 'integer'],
            'desc' => ''
        ],
    ];

//

}
