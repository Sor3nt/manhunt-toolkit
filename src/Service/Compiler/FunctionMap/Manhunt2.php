<?php
namespace App\Service\Compiler\FunctionMap;

class Manhunt2
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
    public static $functionForceFloar = [];

    public static $functionNoReturn = [
        'getentityposition',
        'getcameraposition',
        'getentityname',
        'aigethunterlastnodename'
    ];

    public static $functionEventDefinition = [
        '__default__' => '68000000'
    ];

    public static $constants = [

        'ARM_INVULNERABLE' => [
            'offset' => "04000000"
        ],

        'CT_PLIERS' => [
            'offset' => "22000000"
        ],

        'CT_UZI' => [
            'offset' => "2c000000"
        ],

        'CT_SHOTGUN' => [
            'offset' => "2e000000"
        ],

        'COL_SHOT' => [
            'offset' => "00800000"
        ],

        'HID_HEALTHBAR_PLAYER' => [
            'offset' => "03000000"
        ],

        'MAP_COLOR_PERSON' => [
            'offset' => "16000000"
        ],
        'CT_SYRINGE' => [
            'offset' => "71000000"
        ],

        'CT_GLOCK' => [
            'offset' => "29000000"
        ],

        'CT_HEAD' => [
            'offset' => "67000000"
        ],

        'CT_G_FIRST_AID' => [
            "offset" => "09000000"
        ],

        'MOVER_ACCEL_FAST' => [
            'offset' => "02000000"
        ],

        'AISCRIPT_MEDIUMPRIORITY' => [
            'offset' => "02000000"
        ],

        'AISCRIPT_IDLE_STANDANIMS' => [
            'offset' => "05000000"
        ],

        'AISCRIPT_IDLE_WANDERSEARCH' => [
            'offset' => "00000000"
        ],

        'MTT_HOOD_MEDIUM' => [
            'offset' => "02000000"
        ],

        'AISCRIPT_WALKMOVESPEED' => [
            'offset' => "01000000"
        ],

        'COL_PLAYER' => [
            'offset' => "00020000"
        ],

        'MAP_COLOR_YELLOW' => [
            'offset' => "04000000"
        ],

        'MAP_COLOR_RED' => [
            'offset' => "02000000"
        ],

        'HID_RADAR' => [
            'offset' => "02000000"
        ],

        'MAP_COLOR_LOCATION' => [
            'offset' => "14000000"
        ],

        'MAP_COLOR_HUNTER_IDLE' => [
            'offset' => "08000000"
        ],

        'COL_HUNTER' => [
            'offset' => "10000000"
        ],

        'AISCRIPT_VERYHIGHPRIORITY' => [
            'offset' => "00000000"
        ],

        'AISCRIPT_LOWPRIORITY' => [
            'offset' => "03000000"
        ],

        'AISCRIPT_IDLE_STANDSTILL' => [
            'offset' => "02000000"
        ],

        'COMBATTYPEID_MELEE' => [
            'offset' => "00000000"
        ],

        'MTT_TRAINING' => [
            'offset' => "00000000"
        ],

        'DIFFICULTY_NORMAL' => [
            'offset' => "01000000"
        ],

        'MAP_COLOR_BLUE' => [
            'offset' => "06000000"
        ],

        'COMBATTYPEID_OPEN_MELEE' => [
            'offset' => "03000000"
        ],

        'COMBATTYPEID_COVER' => [
            'offset' => "02000000"
        ],

        'COMBATTYPEID_OPEN' => [
            'offset' => "01000000"
        ],

        'AISCRIPT_GRAPHLINK_ALLOW_NOTHING' => [
            'offset' => "00000000"
        ],

        'HID_ALL_PLAYER_ITEMS' => [
            'offset' => "2c010000"
        ],

        'AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING' => [
            "offset" => "03000000"
        ],

        'AISCRIPT_IDLE_PATROL' => [
            "offset" => "01000000"
        ],

        'AISCRIPT_HIGHPRIORITY' => [
            "offset" => "01000000"
        ],

        'MOVER_STOPPED' => [
            "offset" => "00000000"
        ],

        'MOVER_FORWARD' => [
            "offset" => "01000000"
        ],

        'MOVER_ACCEL_SLOW' => [
            "offset" => "02000000"
        ],

        'AISCRIPT_RUNMOVESPEED' => [
            "offset" => "00000000"
        ],

        'DOOR_OPEN' => [
            "offset" => "00000000"
        ],

        'DOOR_OPENING' => [
            "offset" => "01000000"
        ],

        'DOOR_CLOSED' => [
            "offset" => "02000000"
        ],

        'DOOR_CLOSING' => [
            "offset" => "03000000"
        ],

        'CT_CROSSBOW' => [
            "offset" => "83000000"
        ],

        'USEABLE_ON' => [
            "offset" => "01000000"
        ],

    ];

    public static $functions = [

        "useablesetstate" => [
            'name' => 'UseableSetState',
            'offset' => "cc010000"
        ],

        "additemtoinventory" => [
            'name' => 'AddItemToInventory',
            'offset' => "bb000000"
        ],

        "enablejumpattacks" => [
            'name' => 'EnableJumpAttacks',
            'offset' => "80030000"
        ],

        "inflictdamage" => [
            'name' => 'InflictDamage',
            'offset' => "85000000"
        ],

        "instantdecayalldead" => [
            'name' => 'InstantDecayAllDead',
            'offset' => "83030000"
        ],

        "setspotlightmode" => [
            'name' => 'SetSpotlightMode',
            'offset' => "a2030000"
        ],

        "heligetnodereached" => [
            'name' => 'HeliGetNodeReached',
            'offset' => "b4030000"
        ],

        "getindexfrominventoryitemtype" => [
            'name' => 'GetIndexFromInventoryItemType',
            'offset' => "c6000000"
        ],

        "enteredtrigger" => [
            'name' => 'EnteredTrigger',
            'offset' => "a4000000"
        ],

        "aimodifygoalcrouch" => [
            'name' => 'AIModifyGoalCrouch',
            'offset' => "08020000"
        ],
        "aiguardmodifyshootoutsideradius" => [
            'name' => 'AIGuardModifyShootOutsideRadius',
            'offset' => "cd010000"
        ],
        "sethuntergunfireminpause" => [
            'name' => 'SetHunterGunFireMinPause',
            'offset' => "22020000"
        ],
        "sethuntergunfiremaxpause" => [
            'name' => 'SetHunterGunFireMaxPause',
            'offset' => "23020000"
        ],
        "sethuntergunfireminburst" => [
            'name' => 'SetHunterGunFireMinBurst',
            'offset' => "24020000"
        ],
        "heliresumeidlepatrol" => [
            'name' => 'HeliResumeIdlePatrol',
            'offset' => "b9030000"
        ],
        "helipauseidlepatrol" => [
            'name' => 'HeliPauseIdlePatrol',
            'offset' => "b8030000"
        ],
        "helisetidlepatrolpath" => [
            'name' => 'HeliSetIdlePatrolPath',
            'offset' => "b5030000"
        ],
        "isplayercrawling" => [
            'name' => 'IsPlayerCrawling',
            'offset' => "84030000"
        ],

        "helisetlightrandomwander" => [
            'name' => 'HeliSetLightRandomWander',
            'offset' => "39030000"
        ],
        "helilookatposition" => [
            'name' => 'HeliLookAtPosition',
            'offset' => "33030000"
        ],
        "helilookatentity" => [
            'name' => 'HeliLookAtEntity',
            'offset' => "34030000"
        ],

        "helisetfiringrate" => [
            'name' => 'HeliSetFiringRate',
            'offset' => "70030000"
        ],

        "helisetspolightmaxdeflection" => [
            'name' => 'HeliSetSpolightMaxDeflection',
            'offset' => "a8030000"
        ],

        "heliopenfire" => [
            'name' => 'HeliOpenFire',
            'offset' => "36030000"
        ],

        "helisetlightspeed" => [
            'name' => 'HeliSetLightSpeed',
            'offset' => "38030000"
        ],

        "helisetenemy" => [
            'name' => 'HeliSetEnemy',
            'offset' => "35030000"
        ],

        "setnoisyentity" => [
            'name' => 'SetNoisyEntity',
            'offset' => "af030000"
        ],

        "setambienttrackvolume" => [
            'name' => 'SetAmbientTrackVolume',
            'offset' => "2d030000"
        ],

        "aisetidlepatrolstopdirection" => [
            'name' => 'AISetIdlePatrolStopDirection',
            'offset' => "a7010000"
        ],

        "aiisinsubpack" => [
            'name' => 'AIIsInSubPack',
            'offset' => "66010000"
        ],

        "aidoesleaderhavesubpack" => [
            'name' => 'AIDoesLeaderHaveSubpack',
            'offset' => "eb020000"
        ],

        "heligotonode" => [
            'name' => 'HeliGotoNode',
            'offset' => "b3030000"
        ],

        "setscriptaudiostreamocclusion" => [
            'name' => 'SetScriptAudiosTreamOcclusion',
            'offset' => "61030000"
        ],

        "allowreceivingofheadshots" => [
            'name' => 'AllowReceivingOfHeadshots',
            'offset' => "7f030000"
        ],
        "ishunterinshadow" => [
            'name' => 'IsHunterInShadow',
            'offset' => "ca030000"
        ],
        "getmoverstate" => [
            'name' => 'GetMoverState',
            'offset' => "39010000"
        ],
        "radarcreateblip" => [
            'name' => 'RadarCreateBlip',
            'offset' => "aa030000"
        ],
        "scripthogprocessorend" => [
            'name' => 'ScriptHogProcessorEnd',
            'offset' => "16020000"
        ],

        "aidefinegoalgotovector" => [
            'name' => 'AiDefineGoalGotoVector',
            'offset' => "70010000"
        ],

        "setspotlighttransitiontime" => [
            'name' => 'SetSpotlightTransitionTime',
            'offset' => "a1030000"
        ],

        "setspotlighttarget" => [
            'name' => 'SetSpotLightTarget',
            'offset' => "a0030000"
        ],

        "initspotlight" => [
            'name' => 'InitSpotlight',
            'offset' => "9f030000"
        ],


        "aidefinegoalgotonodestayonpath" => [
            'name' => 'AiDefineGoalGotoNodeStayOnPath',
            'offset' => "f2010000"
        ],

        "isentitydying" => [
            'name' => 'IsEntityDying',
            'offset' => "52020000"
        ],

        "aiassociatefouractiveareaswithplayerarea" => [
            'name' => 'AIAssociateFourActiveAreasWithPlayerArea',
            'offset' => "be010000"
        ],

        "aiassociatethreeactiveareaswithplayerarea" => [
            'name' => 'AIAssociateThreeActiveAreasWithPlayerArea',
            'offset' => "bd010000"
        ],

        "aiassociatetwoactiveareaswithplayerarea" => [
            'name' => 'AiAssociateTwoActiveAreasWithPlayerArea',
            'offset' => "bc010000"
        ],
        "stringcopy" => [
            'name' => 'stringcopy',
            'offset' => "6d000000"
        ],

        "helicreatehelipath" => [
            'name' => 'HeliCreateHeliPath',
            'offset' => "b2030000"
        ],

        "helicreatehelinode" => [
            'name' => 'HeliCreateHeliNode',
            'offset' => "b1030000"
        ],

        "randnum" => [
            'name' => 'RandNum',
            'offset' => "69000000"
        ],

        "getdifficultylevel" => [
            'name' => 'GetDifficultyLevel',
            'offset' => "9f020000"
        ],

        "aicutsceneentityenable" => [
            'name' => 'AICutSceneEntityEnable',
            'offset' => "a9020000"
        ],

        "cutsceneend" => [
            'name' => 'CutSceneEnd',
            'offset' => "49010000"
        ],

        "clearlevelgoal" => [
            'name' => 'ClearLevelGoal',
            'offset' => "42020000"
        ],

        "cutscenestart" => [
            'name' => 'CutSceneStart',
            'offset' => "48010000"
        ],

        "cutsceneregisterskipscript" => [
            'name' => 'CutSceneRegisterSkipScript',
            'offset' => "20030000"
        ],

        "displaygametext" => [
            'name' => 'DisplayGameText',
            'offset' => "04010000"
        ],

        "frisbeespeechplay" => [
            'name' => 'FrisbeeSpeechPlay',
            'offset' => "66030000"
        ],

        "frisbeespeechisfinished" => [
            'name' => 'FrisbeeSpeechIsFinished',
            'offset' => "69030000"
        ],

        'getentityname' => [
            'name' => 'GetEntityName',
            'offset' => "86000000",
            'return' => 'String'
        ],

        "getdoorstate" => [
            'name' => 'GetDoorState',
            'offset' => "96000000"
        ],

        "getentity" => [
            'name' => 'GetEntity',
            'offset' => "77000000"
        ],

        "hudtoggleflashflags" => [
            'name' => 'HUDToggleFlashFlags',
            'offset' => "b2020000"
        ],

        "iswhitenoisedisplaying" => [
            'name' => 'IsWhiteNoiseDisplaying',
            'offset' => "e7020000"
        ],

        "killgametext" => [
            'name' => 'KillGameText',
            'offset' => "08010000"
        ],

        "killscript" => [
            'name' => 'KillScript',
            'offset' => "e5000000"
        ],

        "setdoorstate" => [
            'name' => 'SetDoorState',
            'offset' => '97000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state
             * - DOOR_CLOSING  => 3
             */
            'params' => ['Entity', 'Integer'],
            'desc' => ''
        ],

        "setswitchstate" => [
            'offset' => "95000000"
        ],

        "setcurrentlod" => [
            'offset' => "2d010000"
        ],

        "setshowhudincutscene" => [
            'offset' => "86030000"
        ],

        "setvector" => [
            'name' => 'SetVector',
            'offset' => "84010000"
        ],

        "setcameraposition" => [
            'offset' => "92010000"
        ],

        "setcameraview" => [
            'offset' => "8f010000"
        ],

        "setzoomlerp" => [
            'offset' => "b5020000"
        ],

        "setlevelgoal" => [
            'name' => 'SetLevelGoal',
            'offset' => "41020000"
        ],

        "setslidedoorspeed" => [
            'offset' => "ae010000"
        ],

        "togglehudflag" => [
            'offset' => "7f020000"
        ],

        "aiaddentity" => [
            'offset' => "4d010000"
        ],

        "aisethunteronradar" => [
            'name' => 'aisethunteronradar',
            'offset' => 'a8010000',
            /**
             * Parameters
             * 1: ref to me (me[30])
             * 2: boolean
             */
            'params' => ['String', 'Bollean'],
            'desc' => 'Set the Hunter visibility on the Players Radar'
        ],

        "setnextlevelbyname" => [
            'name' => 'SetNextLevelByName',
            'offset' => '4c030000',
            /**
             * Parameters
             * 1: string level name
             * - A02_The_Old_House
             */
            'params' => ['String'],
            'desc' => ''
        ],

        "aisetentityasleader" => [
            'offset' => "4f010000"
        ],

        "aisetleaderinvisible" => [
            'offset' => "6d020000"
        ],

        "aiaddleaderenemy" => [
            'offset' => "54010000"
        ],

        "aientityalwaysenabled" => [
            'offset' => "bf010000"
        ],

        "aiaddsubpackforleader" => [
            'offset' => "50010000"
        ],

        "aisetsubpackcombattype" => [
            'offset' => "82010000"
        ],

        "aidefinegoalhuntenemy" => [
            'offset' => "58010000"
        ],

        "aiaddgoalforsubpack" => [
            'offset' => "56010000"
        ],

        "aiaddplayer" => [
            'offset' => "5b010000"
        ],

        "hideentity" => [
            'name' => 'HideEntity',
            'offset' => "83000000"
        ],
        "setslidedoorajardistance" => [
            'name' => 'SetSlideDoorAjarDistance',
            'offset' => "9b010000"
        ],

        "setmaxnumberofrats" => [
            'offset' => "a8020000"
        ],

        "switchlitteron" => [
            'offset' => "a4020000"
        ],

        /**
         * Note: WriteDebug function is internal splitted!
         */
        "writedebug" => [
            'name' => 'WriteDebug',
            'offset' => "73000000"
        ],

        "writedebugstring" => [
            'name' => 'WriteDebugString',
            'offset' => "73000000"
        ],

        "writedebugreal" => [
            'name' => 'WriteDebugReal',
            'offset' => "6f000000"
        ],

        "writedebuginteger" => [
            'name' => 'WriteDebugInteger',
            'offset' => "6e000000"
        ],

        "writedebugflush" => [
            'name' => 'WriteDebugFlush',
            'offset' => "74000000"
        ],


        "setqtmbaseprobability" => [
            'name' => 'SetQTMBaseProbability',
            'offset' => "ac030000"
        ],

        "graphmodifyconnections" => [
            'name' => 'GraphModifyConnections',
            'offset' => 'e9000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2:
             * - AISCRIPT_GRAPHLINK_ALLOW_NOTHING => 0
             * - AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING => 3
             */
            'params' => ['Entity', 'Integer'],
            'desc' => ''
        ],

        "unfreezeentity" => [
            'name' => 'UnFreezeEntity',
            'offset' => '38010000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        "lockentity" => [
            'name' => 'LockEntity',
            'offset' => '98000000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        "entityplayanim" => [
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
            'params' => ['Entity', 'String', 'Integer'],
            'desc' => ''
        ],

        "setentityscriptsfromentity" => [
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
            'params' => ['String', 'String'],
            'desc' => ''
        ],

        "entityignorecollisions" => [
            'name' => 'EntityIgnoreCollisions',
            'offset' => 'a2020000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state
             * - true
             * - false
             */
            'params' => ['Entity', 'Integer'],
            'desc' => ''
        ],

        "aientitycancelanim" => [
            'name' => 'AIEntityCancelAnim',
            'offset' => '17020000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: animation name ?
             * - BAT_INMATE_SMACK_HEAD_ANIM
             */
            'params' => ['String', 'String'],
            'desc' => ''
        ],

        "aisetentityidleoverride" => [
            'name' => 'AISetEntityIdleOverRide',
            'offset' => 'b5010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state 1 boolean
             * 3: state 2 boolean
             */
            'params' => ['String', 'Boolean', 'Boolean'],
            'desc' => ''
        ],

        "setentityinvulnerable" => [
            'name' => 'SetEntityInvulnerable',
            'offset' => '5e010000',
            /**
             * Parameters
             * 1: result of getEntity
             * 2: state boolean
             */
            'params' => ['Entity', 'Boolean'],
            'desc' => ''
        ],

        "aimakeentityblind" => [
            'name' => 'AIMakeEntityBlind',
            'offset' => '71010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state boolean
             */
            'params' => ['String', 'Boolean'],
            'desc' => ''
        ],

        "aimakeentitydeaf" => [
            'name' => 'AIMakeEntityDeaf',
            'offset' => '72010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state boolean
             */
            'params' => ['String', 'Boolean'],
            'desc' => ''
        ],

        "aiaddhuntertoleadersubpack" => [
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
            'params' => ['String', 'String', 'String'],
            'desc' => ''
        ],

        "playerdropbody" => [
            'name' => 'PlayerDropBody',
            'offset' => 'b4020000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        "playerfullbodyanimdone" => [
            'name' => 'PlayerFullBodyAnimDone',
            'offset' => '96020000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        "enableuserinput" => [
            'name' => 'EnableUserInput',
            'offset' => 'f5000000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        "showentity" => [
            'name' => 'ShowEntity',
            'offset' => '82000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        "cutscenecamerainit" => [
            'name' => 'CutSceneCameraInit',
            'offset' => '5f030000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        "cutscenecamerasetpos" => [
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

        "cutscenecamerasettarget" => [
            'name' => 'CutSceneCameraSetTarget',
            'offset' => '5b030000',
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

        "cutscenecamerasetfov" => [
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

        "cutscenecamerasetroll" => [
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

        "cutscenecamerasethandycam" => [
            'name' => 'CutSceneCameraSetHandyCam',
            'offset' => '6d030000',
            /**
             * Parameters
             * 1: state boolean
             */
            'params' => ['Boolean'],
            'desc' => ''
        ],

        "aidefinegoalgotonodeidle" => [
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
            'params' => ['String', 'String', 'Integer', 'String', 'Integer', 'Boolean'],
            'desc' => ''
        ],

        "aidefinegoalgotonode" => [
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
            'params' => ['String', 'String', 'Integer', 'String', 'Integer', 'Boolean'],
            'desc' => ''
        ],

        "setpedlockonable" => [
            'name' => 'SetPedLockonable',
            'offset' => '97020000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state boolean
             */
            'params' => ['Entity', 'Boolean'],
            'desc' => ''
        ],

        "moveentity" => [
            'name' => 'MoveEntity',
            'offset' => '7d000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: ref to vec3d (3x float)
             * 3: integer
             */
            'params' => ['Entity', 'Vec3D', 'Integer'],
            'desc' => ''
        ],

        "setpedorientation" => [
            'name' => 'SetPedOrientation',
            'offset' => 'b0020000',
            /**
             * Parameters
             * 1: GetEntityPosition
             * 2: integer
             * - 10
             */
            'params' => ['EntityPosition', 'Integer'],
            'desc' => ''
        ],

        "setmoverstate" => [
            'name' => 'SetMoverState',
            'offset' => '3a010000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: integer
             * - MOVER_FORWARD => 1
             */
            'params' => ['Entity', 'Integer'],
            'desc' => ''
        ],

        "aisetidlehomenode" => [
            'name' => 'AISetIdleHomeNode',
            'offset' => '83010000',
            /**
             * Parameters
             * 1: string ref to me (me[30])
             * 2: string
             * - AMBUSHNODE
             */
            'params' => ['String', 'String'],
            'desc' => ''
        ],

        "radarpositionclearentity" => [
            'name' => 'RadarPositionClearEntity',
            'offset' => 'e1020000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        "createboxtrigger" => [
            'name' => 'CreateBoxTrigger',
            'offset' => '28010000',
            /**
             * Parameters
             * 1: vec3D
             * 2: vec3D
             * 3: String
             * - triggerOutOfWindow
             */
            'params' => ['Vec3D', 'Vec3D', 'String'],
            'desc' => ''
        ],

        "removethisscript" => [
            'name' => 'RemoveThisScript',
            'offset' => 'e8000000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        "sethuntermeleetraits" => [
            'name' => 'SetHunterMeleeTraits',
            'offset' => '77020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: int
             * - MTT_HOOD_MEDIUM => 2
             */
            'params' => ['This', 'Integer'],
            'desc' => ''
        ],

        "aisethunteridleactionminmaxradius" => [
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
            'params' => ['String', 'Integer', 'Integer', 'Integer', 'Integer', 'Float'],
            'desc' => ''
        ],


        "setpeddonotdecay" => [
            'name' => 'SetPedDoNotDecay',
            'offset' => '6b020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: boolean
             */
            'params' => ['This', 'Boolean'],
            'desc' => ''
        ],

        "enableuseable" => [
            'name' => 'EnableUseable',
            'offset' => 'e5020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: boolean
             */
            'params' => ['This', 'Boolean'],
            'desc' => ''
        ],

        "setmoveridleposition" => [
            'name' => 'SetMoverIdlePosition',
            'offset' => '3c010000',
            /**
             * Parameters
             * 1: ref to this
             * 2: vec3D
             */
            'params' => ['This', 'vec3D'],
            'desc' => ''
        ],

        "movemovertoidleposition" => [
            'name' => 'MoveMoverToIdlePosition',
            'offset' => '3d010000',
            /**
             * Parameters
             * 1: ref to this
             * 2: vec3D
             */
            'params' => ['This', 'vec3D'],
            'desc' => ''
        ],

        "getplayer" => [
            'name' => 'GetPlayer',
            'offset' => '8a000000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],
//

        "setmoverspeed" => [
            'name' => 'SetMoverSpeed',
            'offset' => '40010000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],

        "playaudiooneshotfromentity" => [
            'name' => 'PlayAudioOneShotFromEntity',
            'offset' => '5c020000',
            /**
             * Parameters
             * none
             */
            'params' => [],
            'desc' => ''
        ],


        "playscriptaudiostreamfromentityauto" => [
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
            'params' => ['String', 'Integer', 'GetPlayer', 'Integer'],
            'desc' => ''
        ],

        "createspheretrigger" => [
            'name' => 'CreateSphereTrigger',
            'offset' => 'a3000000',
            /**
             * Parameters
             * 1: Vec3D
             * 2: Float
             * 3: string
             * - triggerVisionCheck
             */
            'params' => ['Vec3D', 'Float', 'String'],
            'desc' => ''
        ],

        "destroyentity" => [
            'name' => 'DestroyEntity',
            'offset' => 'a0020000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        "getanimationlength" => [
            'name' => 'GetAnimationLength',
            'offset' => '49030000',
            /**
             * Parameters
             * 1: string
             */
            'params' => ['String'],
            'return' => 'Integer',
            'desc' => ''
        ],

        "unlockentity" => [
            'name' => 'UnLockEntity',
            'offset' => '99000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'desc' => ''
        ],

        "spawnentitywithdirection" => [
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
            'params' => ['String', 'Vec3d', 'String', 'Vec3d'],
            'desc' => ''
        ],

        "getentityposition" => [
            'name' => 'GetEntityPosition',
            'offset' => '78000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params' => ['Entity'],
            'return' => 'Vec3d',
            'desc' => ''
        ],

        "setcolourramp" => [
            'name' => 'SetColourRamp',
            'offset' => 'ab030000',
            /**
             * Parameters
             * 1: STRING
             * - FE_colramps
             * 2: int
             * 3: float
             */
            'params' => ['String', 'Integer', 'Float'],
            'return' => 'void',
            'desc' => ''
        ],

        "getambientaudiotrack" => [
            'name' => 'GetAmbientAudioTrack',
            'offset' => '77030000',
            /**
             * Parameters
             * 1: none
             */
            'params' => [],
            'return' => 'Integer',
            'desc' => ''
        ],

        "sethunterhideHealth" => [
            'name' => 'SetHunterHideHealth',
            'offset' => '2f010000',
            /**
             * Parameters
             * 1: string[30]
             * 2: int
             */
            'params' => ['String[]', 'Integer'],
            'return' => 'void',
            'desc' => ''
        ],

        "isentityalive" => [
            'name' => 'IsEntityAlive',
            'offset' => 'aa010000',
            /**
             * Parameters
             * 1: string
             */
            'params' => ['String'],
            'return' => 'Boolean',
            'desc' => ''
        ],

        "setmaxscoreforlevel" => [
            'name' => 'SetMaxScoreForLevel',
            'offset' => '59030000',
            /**
             * Parameters
             * 1: integer
             */
            'params' => ['Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        "fakehunterdestroyall" => [
            'name' => 'FakeHunterDestroyAll',
            'offset' => 'c3030000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "scripthogprocessorstart" => [
            'name' => 'ScriptHogProcessorStart',
            'offset' => '15020000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "enableaction" => [
            'name' => 'EnableAction',
            'offset' => '62030000',
            /**
             * Parameters
             * 1: Integer
             * 2: state
             */
            'params' => ["integer", "Boolean"],
            'return' => 'Void',
            'desc' => ''
        ],

        "killthisscript" => [
            'name' => 'KillThisScript',
            'offset' => 'e7000000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "playerplayfullbodyanim" => [
            'name' => 'PlayerPlayFullBodyAnim',
            'offset' => '94020000',
            /**
             * Parameters
             * 1: string
             * - ASY_REACTKILL_2
             */
            'params' => ['String'],
            'return' => 'Integer',
            'desc' => ''
        ],

        "disableuserinput" => [
            'name' => 'DisableUserInput',
            'offset' => 'f6000000',
            /**
             * Parameters
             * 1: string
             * - ASY_REACTKILL_2
             */
            'params' => ['String'],
            'return' => 'Integer',
            'desc' => ''
        ],

        "setdamage" => [
            'name' => 'SetDamage',
            'offset' => '2f010000',
            /**
             * Parameters
             * 1: this
             * 2: Integer
             */
            'params' => ['This', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        "aitriggersoundknownlocationnoradar" => [
            'name' => 'AITriggerSoundKnownLocationNoRadar',
            'offset' => 'b8020000',
            /**
             * Parameters
             * 1: String
             * - LURE_HIGH
             * 2: Player
             * - GetPlayer
             */
            'params' => ['String', 'Player'],
            'return' => 'Integer',
            'desc' => ''
        ],

        "spawnmovingentity" => [
            'name' => 'SpawnMovingEntity',
            'offset' => '7a000000',
            /**
             * Parameters
             * 1: string Entity Name ?
             * 2: vec3d
             * 4: string script name ?
             */
            'params' => ['String', 'Vec3d', 'String'],
            'return' => '',
            'desc' => ''
        ],

        "isplayerinsafezone" => [
            'name' => 'IsPlayerInSafeZone',
            'offset' => '89020000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "isplayerrunning" => [
            'name' => 'IsPlayerRunning',
            'offset' => 'ee020000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "isplayersprinting" => [
            'name' => 'IsPlayerSprinting',
            'offset' => 'ef020000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "getpedorientation" => [
            'name' => 'GetPedOrientation',
            'offset' => '8d030000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "isgametextdisplaying" => [
            'name' => 'IsGameTextDisplaying',
            'offset' => '07010000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Boolean',
            'desc' => ''
        ],


        "getlastitempickedup" => [
            'name' => 'GetLastItemPickedUp',
            'offset' => 'c9010000',
            /**
             * Parameters
             * 1: player
             */
            'params' => ["Player"],
            'return' => 'Item',
            'desc' => ''
        ],

        "attachtoentity" => [
            'name' => 'AttachToEntity',
            'offset' => '93000000',
            /**
             * Parameters
             * 1: this
             * 2: result of GetEntity
             */
            'params' => ["This", "Entity"],
            'return' => 'Void',
            'desc' => ''
        ],

        "isnameditemininventory" => [
            'name' => 'IsNamedItemInInventory',
            'offset' => '30010000',
            /**
             * Parameters
             * 1: Player
             * 2: Integer
             */
            'params' => ["Player", "Integer"],
            'return' => 'Void',
            'desc' => ''
        ],

        "helisetlight" => [
            'name' => 'HeliSetLight',
            'offset' => '31030000',
            /**
             * Parameters
             * 1: This
             * 2: Boolean
             */
            'params' => ["This", "Boolean"],
            'return' => 'Void',
            'desc' => ''
        ],

        "helisetmovespeed" => [
            'name' => 'HeliSetMoveSpeed',
            'offset' => '3a030000',
            /**
             * Parameters
             * 1: This
             * 2: Float
             */
            'params' => ["This", "Float"],
            'return' => 'Void',
            'desc' => ''
        ],

        "insidetrigger" => [
            'name' => 'InsideTrigger',
            'offset' => 'a5000000',
            /**
             * Parameters
             * 1: Entity
             * 2: Player
             */
            'params' => ["Entity", "Player"],
            'return' => 'Void',
            'desc' => ''
        ],

        "isplayerpositionknown" => [
            'name' => 'IsPlayerPositionKnown',
            'offset' => '6e030000',
            /**
             * Parameters
             *
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "isplayerwalking" => [
            'name' => 'IsPlayerWalking',
            'offset' => 'ed020000',
            /**
             * Parameters
             *
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "isexecutioninprogress" => [
            'name' => 'IsExecutionInProgress',
            'offset' => '51020000',
            /**
             * Parameters
             *
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "isscriptaudiostreamcompleted" => [
            'name' => 'IsScriptAudioStreamCompleted',
            'offset' => 'cf020000'

        ],

        "cutscenecamerastart" => [
            'name' => 'CutSceneCameraStart',
            'offset' => '5e030000',
            /**
             * Parameters
             *
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],


        "playscriptaudiostreamauto" => [
            'name' => 'PlayScriptAudioStreamAuto',
            'offset' => '6a030000',
            /**
             * Parameters
             * 1: String
             * - WACKO3
             * 2: Integer
             */
            'params' => ['String', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        "aientityplayanim" => [
            'name' => 'AiEntityPlayAnim',
            'offset' => 'b3010000',
            /**
             * Parameters
             * 1: Entity
             * 2: string
             * - ASY_MELEE_INTRO_CAMERA
             * 3: boolean
             */
            'params' => ['Entity', 'String', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "setstreamlipsyncspeaker" => [
            'name' => 'SetStreamLipsyncSpeaker',
            'offset' => 'cf030000',
            /**
             * Parameters
             * 1: Player
             * 2: Boolean
             */
            'params' => ['Player', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "aientityplayanimlooped" => [
            'name' => 'AIEntityPlayAnimLooped',
            'offset' => 'b4010000',
            /**
             * Parameters
             * 1: String
             * - SobbingWoman(hunter)
             * 2: String
             * - BRO_FIXVENT_IDLE_3
             * 3: Float
             */
            'params' => ['String', 'String', 'Float'],
            'return' => 'Void',
            'desc' => ''
        ],

        "endscriptaudiostream" => [
            'name' => 'EndScriptAudioStream',
            'offset' => 'ce020000',
            /**
             * Parameters
             * - none
             */
            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "ishunterknockeddown" => [
            'name' => 'IsHunterKnockedDown',
            'offset' => 'cb030000',
            /**
             * Parameters
             * 1: String
             * - SobbingWoman(hunter)
             */
            'params' => ['String'],
            'return' => 'Boolean',
            'desc' => ''
        ],

        "aisetentitystayonpath" => [
            'name' => 'AISetEntityStayOnPath',
            'offset' => '4e020000',
            /**
             * Parameters
             * 1: String
             * 2: Boolean
             */
            'params' => ['String', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "setpedhurtotherpeds" => [
            'name' => 'SetPedHurtOtherPeds',
            'offset' => '1e030000',
            /**
             * Parameters
             * 1: String
             * 2: Boolean
             */
            'params' => ['String', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "getdamage" => [
            'name' => 'GetDamage',
            'offset' => '84000000',
            /**
             * Parameters
             * 1: Player
             */
            'params' => ['Player'],
            'return' => 'Integer',
            'desc' => ''
        ],

        "aisethunteridlepatrol" => [
            'name' => 'AISetHunterIdlePatrol',
            'offset' => 'a3010000',

            'params' => ['String', 'Constant', 'Constant', 'Integer', 'Integer', 'String'],
            'return' => 'Void',
            'desc' => ''
        ],

        "deactivatesavepoint" => [
            'name' => 'DeactivateSavePoint',
            'offset' => '12030000',

            'params' => ['EntityPtr'],
            'return' => 'Void',
            'desc' => ''
        ],

        "calcdistancetoentity" => [
            'name' => 'CalcDistanceToEntity',
            'offset' => '1a030000',

            'params' => ['EntityPtr'],
            'return' => 'Void',
            'desc' => ''
        ],

        "getplayerposition" => [
            'name' => 'GetPlayerPosition',
            'offset' => '8b000000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "aitriggersound" => [
            'name' => 'AITriggerSound',
            'offset' => '5d010000',

            'params' => ['String', 'This'],
            'return' => 'Void',
            'desc' => ''
        ],

        "aiplaycommunication" => [
            'name' => 'AIPlayCommunication',
            'offset' => 'fe010000',

            'params' => ['String', 'This'],
            'return' => 'Void',
            'desc' => ''
        ],

        "setambientaudiotrack" => [
            'name' => 'SetAmbientAudioTrack',
            'offset' => '75030000',

            'params' => ['String', 'This'],
            'return' => 'Void',
            'desc' => ''
        ],
        "iscutsceneinprogress" => [
            'name' => 'IsCutSceneInProgress',
            'offset' => 'f5020000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],
        "setlevelcompleted" => [
            'name' => 'SetLevelCompleted',
            'offset' => '04020000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "thislevelbeencompletedalready" => [
            'name' => 'ThisLevelBeenCompletedAlready',
            'offset' => '04030000',

            'params' => [],
            'return' => 'Boolean',
            'desc' => ''
        ],

        "registernonexecutablehunterinlevel" => [
            'name' => 'RegisterNonExecutableHunterInLevel',
            'offset' => 'b1020000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "sethuntermute" => [
            'name' => 'SetHunterMute',
            'offset' => '76030000',

            'params' => ['Entity', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "playaudioloopedfromentity" => [
            'name' => 'PlayAudioLoopedFromEntity',
            'offset' => '5e020000',

            'params' => ['Entity', 'String', 'String', 'Integer', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        "triggersavepoint" => [
            'name' => 'TriggerSavePoint',
            'offset' => '47030000',

            'params' => ['Entity', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "clearalllevelgoals" => [
            'name' => 'ClearAllLevelGoals',
            'offset' => '00030000',

            'params' => [],
            'return' => 'Void',
            'desc' => ''
        ],

        "setplayerjumpflag" => [
            'name' => 'SetPlayerJumpFlag',
            'offset' => '24030000',

            'params' => ['Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "aisethunteridleactionminmax" => [
            'name' => 'AISetHunterIdleActionMinMax',
            'offset' => '80010000',

            'params' => ['String', 'Integer', 'Integer', 'Integer', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        "killentity" => [
            'name' => 'KillEntity',
            'offset' => '80000000',

            'params' => ['Entity'],
            'return' => 'Void',
            'desc' => ''
        ],

        "sethunterexecutable" => [
            'name' => 'SetHunterExecutable',
            'offset' => '82020000',

            'params' => ['Entity', 'Boolean'],
            'return' => 'Void',
            'desc' => ''
        ],

        "radarpositionsetentity" => [
            'name' => 'RadarPositionSetEntity',
            'offset' => 'e0020000',

            'params' => ['Entity', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],
        "setplayerheading" => [
            'name' => 'SetPlayerHeading',
            'offset' => '80020000',

            'params' => ['Entity', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],
        "sethunterrunspeed" => [
            'name' => 'SetHunterRunSpeed',
            'offset' => 'f1010000',

            'params' => ['String', 'Float'],
            'return' => 'Void',
            'desc' => ''
        ],
        "triggeraddentityclass" => [
            'name' => 'TriggerAddEntityClass',
            'offset' => '10020000',

            'params' => ['Entity', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],
        "killentitywithoutanim" => [
            'name' => 'KillEntityWithoutAnim',
            'offset' => '23030000',

            'params' => ['Entity', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],
        "airemovegoalfromsubpack" => [
            'name' => 'AIRemoveGoalFromSubpack',
            'offset' => '57010000',

            'params' => ['String', 'String', 'String'],
            'return' => 'Void',
            'desc' => ''
        ],

        "getcameraposition" => [
            'name' => 'GetCameraPosition',
            'offset' => '8e010000',

            'params' => [],
            'return' => 'Vec3D',
            'desc' => ''
        ],

        "sethunterhidehealth" => [
            'name' => 'SetHunterHideHealth',
            'offset' => 'ee010000',

            'params' => [],
            'return' => 'Vec3D',
            'desc' => ''
        ],
        "aisetidlepatrolstop" => [
            'name' => 'AISetIdlePatrolStop',
            'offset' => 'a6010000',

            'params' => ['StringArray', 'String', 'Integer', 'Boolean'],
            'return' => 'Vec3D',
            'desc' => ''
        ],

        'switchlightoff' => [
            'name' => 'SwitchLightOff',
            'offset' => "da000000"
        ],

        'playscriptaudiostreamfromposauto' => [
            'name' => 'PlayScriptAudioStreamFromPosAuto',
            'offset' => "6c030000"
        ],

        'airemovehunterfromleadersubpack' => [
            'name' => 'AIRemoveHunterFromLeaderSubpack',
            'offset' => "53010000"
        ],

        'setplayercontrollable' => [
            'name' => 'SetPlayerControllable',
            'offset' => "91020000"
        ],

        'setplayergotonode' => [
            'name' => 'SetPlayerGoToNode',
            'offset' => "93020000"
        ],


        'aientitygohomeifidle' => [
            'name' => 'AIEntityGoHomeIfIdle',
            'offset' => "18020000"
        ],


        'aiignoreentityifdead' => [
            'name' => 'AIIgnoreEntityIfDead',
            'offset' => "4f020000"
        ],

        'removeentity' => [
            'name' => 'RemoveEntity',
            'offset' => "81000000"
        ],

        'playscriptaudiostreamfromposautolooped' => [
            'name' => 'PlayScriptAudioStreamFromPosAutoLooped',
            'offset' => "73030000"
        ],


        'isfrisbeespeechcompleted' => [
            'name' => 'IsFrisbeeSpeechCompleted',
            'offset' => "b0030000"
        ],


        'spawnentitywithvelocity' => [
            'name' => 'SpawnEntityWithVelocity',
            'offset' => "a1020000"
        ],

        'applyforcetophysicsobject' => [
            'name' => 'ApplyForceToPhysicsObject',
            'offset' => "98030000"
        ],

        'isplayerwallsquashed' => [
            'name' => 'IsPlayerWallSquashed',
            'offset' => "e4020000"
        ],

        'switchlighton' => [
            'name' => 'SwitchLightOn',
            'offset' => "db000000"
        ],


        'aiisgoalnameinsubpack' => [
            'name' => 'AIIsGoalNameInSubpack',
            'offset' => "a5020000"
        ],


        'aisetentityallowsurprise' => [
            'name' => 'AISetEntityAllowSurprise',
            'offset' => "6e020000"
        ],

        'triggerremoveentityclass' => [
            'name' => 'TriggerRemoveEntityClass',
            'offset' => "11020000"
        ],


        'aisetidletalkprobability' => [
            'name' => 'AISetIdleTalkProbability',
            'offset' => "cc030000"
        ],

        'aiisidle' => [
            'name' => 'AIIsIdle',
            'offset' => "6a010000"
        ],

        'playerignorethisentity' => [
            'name' => 'PlayerIgnoreThisEntity',
            'offset' => "8b030000"
        ],

        'ailookatentity' => [
            'name' => 'AILookAtEntity',
            'offset' => "fd010000"
        ],


        'aicancelhunteridleaction' => [
            'name' => 'AICancelHunterIdleAction',
            'offset' => "81010000"
        ],


        'getdropposforplayerpickups' => [
            'name' => 'GetDropPosForPlayerPickups',
            'offset' => "96030000"
        ],


        'aisethunteridleaction' => [
            'name' => 'AISetHunterIdleAction',
            'offset' => "7f010000"
        ],

        'lockped' => [
            'name' => 'LockPed',
            'offset' => "9a020000"
        ],


        'hunteruseswitch' => [
            'name' => 'HunterUseSwitch',
            'offset' => "ae020000"
        ],


        'playscriptaudiostreamfromentityautolooped' => [
            'name' => 'PlayScriptAudioStreamFromEntityAutoLooped',
            'offset' => "72030000"
        ],


        'aidefinegoalhidenamedhunter' => [
            'name' => 'AIDefineGoalHideNamedHunter',
            'offset' => "4b020000"
        ],


        'setentityfade' => [
            'name' => 'SetEntityFade',
            'offset' => "82030000"
        ],

        'removescript' => [
            'name' => 'RemoveScript',
            'offset' => "e6000000"
        ],

        'isplayercarryingbody' => [
            'name' => 'IsPlayerCarryingBody',
            'offset' => "b3020000"
        ],


        'settimer' => [
            'name' => 'SetTimer',
            'offset' => "d0020000",
            /**
             * Parameters
             * 1: Minutes
             * 2: Seconds
             */
            'params' => ['Integer', 'Integer'],
            'return' => 'Void',
            'desc' => ''
        ],

        'starttimer' => [
            'name' => 'StartTimer',
            'offset' => "d1020000"
        ],

        'stoptimer' => [
            'name' => 'StopTimer',
            'offset' => "d2020000"
        ],


        'showtimer' => [
            'name' => 'ShowTimer',
            'offset' => "d4020000"
        ],

        'hidetimer' => [
            'name' => 'HideTimer',
            'offset' => "d5020000"
        ],

        'incrementcounter' => [
            'name' => 'IncrementCounter',
            'offset' => "fb020000"
        ],

        'decreasecounter' => [
            'name' => 'DecreaseCounter',
            'offset' => "fc020000"
        ],

        'showcounter' => [
            'name' => 'ShowCounter',
            'offset' => "fd020000"
        ],

        'stringcat' => [
            'name' => 'StringCat',
            'offset' => "6c000000"
        ],

        'integertostring' => [
            'name' => 'IntegerToString',
            'offset' => "5b020000"
        ],

        'aientityignoredeadbodies' => [
            'name' => 'AIEntityIgnoreDeadBodies',
            'offset' => "af020000"
        ],

        'createinventoryitem' => [
            'name' => 'CreateInventoryItem',
            'offset' => "ba000000"
        ],

        'sethunterdropammo' => [
            'name' => 'SetHunterDropAmmo',
            'offset' => "d9020000"
        ],

        'setqtmlength' => [
            'name' => 'SetQTMLength',
            'offset' => "ad030000"
        ],

        'setqtmpresses' => [
            'name' => 'SetQTMPresses',
            'offset' => "ae030000"
        ],


        'setnodeshadow' => [
            'name' => 'SetNodeShadow',
            'offset' => "a5030000"
        ],

        'showtriggers' => [
            'name' => 'ShowTriggers',
            'offset' => "20010000"
        ],


        'createcrawltrigger' => [
            'name' => 'CreateCrawlTrigger',
            'offset' => "d8030000"
        ],

        'aiclearallactiveareaassociations' => [
            'name' => 'AIClearAllActiveAreaAssociations',
            'offset' => "ba010000"
        ],

        'aiassociateoneactiveareawithplayerarea' => [
            'name' => 'AIAssociateOneActiveAreaWithPlayerArea',
            'offset' => "bb010000"
        ],


        'getcurrentinventoryitemtype' => [
            'name' => 'GetCurrentInventoryItemType',
            'offset' => "2a010000"
        ],

        'aitriggersoundnoradar' => [
            'name' => 'AITriggerSoundNoRadar',
            'offset' => "b7020000"
        ],


        'aigethunterlastnodename' => [
            'name' => 'AIGetHunterLastNodeName',
            'offset' => "77010000"
        ],

        'aidefinegoalguardlookatentity' => [
            'name' => 'AIDefineGoalGuardLookAtEntity',
            'offset' => "fc010000"
        ],

        'huntersetgunaccuracyfar' => [
            'name' => 'HunterSetGunAccuracyFar',
            'offset' => "d1010000"
        ],


        'huntersetgunaccuracymid' => [
            'name' => 'HunterSetGunAccuracyMid',
            'offset' => "4d000000"
        ],

        'huntersetgunaccuracynear' => [
            'name' => 'HunterSetGunAccuracyNear',
            'offset' => "d0010000"
        ],


        'setmoveraccel' => [
            'name' => 'SetMoverAccel',
            'offset' => "42010000"
        ],


        'sethunteraimtarget' => [
            'name' => 'SetHunterAimTarget',
            'offset' => "49020000"
        ],

        'sethunterhitaccuracy' => [
            'name' => 'SetHunterHitAccuracy',
            'offset' => "ab010000"
        ],


        'setjittereffect' => [
            'name' => 'SetJitterEffect',
            'offset' => "c4030000"
        ],

        'aireturnsubpackentityname' => [
            'name' => 'AIReturnSubpackEntityName',
            'offset' => "ec010000"
        ],

        'ainumberinsubpack' => [
            'name' => 'AINumberInSubpack',
            'offset' => "67010000"
        ],


        'seteffectposition' => [
            'name' => 'SetEffectPosition',
            'offset' => "ab000000"
        ],

        'createeffect' => [
            'name' => 'CreateEffect',
            'offset' => "a9000000"
        ],

        'seteffectrgbastart' => [
            'name' => 'SetEffectRGBAStart',
            'offset' => "61010000"
        ],

        'seteffectrgbaend' => [
            'name' => 'SetEffectRGBAEnd',
            'offset' => "62010000"
        ],


        'seteffectpausecycle' => [
            'name' => 'SetEffectPauseCycle',
            'offset' => "b9000000"
        ],

        'seteffectpauselength' => [
            'name' => 'SetEffectPauseLength',
            'offset' => "b8000000"
        ],

        'seteffectradius' => [
            'name' => 'SetEffectRadius',
            'offset' => "b6000000"
        ],

        'substr' => [
            'name' => 'SubStr',
            'offset' => "17030000"
        ],

        'setdooropenanglein' => [
            'name' => 'SetDoorOpenAngleIn',
            'offset' => "d3010000"
        ],

        'setdooropenangleout' => [
            'name' => 'SetDoorOpenAngleOut',
            'offset' => "d4010000"
        ],

        'endaudiolooped' => [
            'name' => 'EndAudioLooped',
            'offset' => "60020000"
        ],

        'playscriptaudiostreamfromentityaux' => [
            'name' => 'PlayScriptAudioStreamFromEntityAux',
            'offset' => "d0030000"
        ],


        'airemoveallgoalsfromsubpack' => [
            'name' => 'AIRemoveAllGoalsFromSubpack',
            'offset' => "9a010000"
        ],


        'sethunterruntime' => [
            'name' => 'SetHunterRunTime',
            'offset' => "ef010000"
        ],


        'enablegraphconnection' => [
            'name' => 'EnableGraphConnection',
            'offset' => "87030000"
        ],

        'setqtmsoundprobabilitymodifier' => [
            'name' => 'SetQTMSoundProbabilityModifier',
            'offset' => "c7030000"
        ],

        'setqtmstaminaprobabilitymodifier' => [
            'name' => 'SetQTMStaminaProbabilityModifier',
            'offset' => "c6030000"
        ],

        'setqtmdeadbodyprobabilitymodifier' => [
            'name' => 'SetQTMDeadBodyProbabilityModifier',
            'offset' => "c5030000"
        ],


        'aisethunteridledirection' => [
            'name' => 'AISetHunterIdleDirection',
            'offset' => "9c010000"
        ],


        'aienableclimbinginidle' => [
            'name' => 'AiEnableClimbingInIdle',
            'offset' => "ce030000"
        ],


        'aideletegoaldefinition' => [
            'name' => 'AIDeleteGoalDefinition',
            'offset' => "df010000"
        ],


        'playaudiooneshotfrompos' => [
            'name' => 'PlayAudioOneShotFromPos',
            'offset' => "5d020000"
        ],


        'activateenvexec' => [
            'name' => 'ActivateEnvExec',
            'offset' => "1c030000"
        ],

        'aitriggersoundlocationknown' => [
            'name' => 'AITriggerSoundLocationKnown',
            'offset' => "71020000"
        ],

        'aidefinegoalguard' => [
            'name' => 'AIDefineGoalGuard',
            'offset' => "59010000"
        ],

        'aidefinegoalorbitentity' => [
            'name' => 'AIDefineGoalOrbitEntity',
            'offset' => "fa010000"
        ],

        'aimodifygoalaim' => [
            'name' => 'AIModifyGoalAim',
            'offset' => "3f020000"
        ],

        'aistayinhuntenemy' => [
            'name' => 'AIStayInHuntEnemy',
            'offset' => "66020000"
        ],

        'killsubtitletext' => [
            'name' => 'KillSubtitleText',
            'offset' => "f6020000"
        ],

        'aidefinegoalshootvector' => [
            'name' => 'AIDefineGoalShootVector',
            'offset' => "6a020000"
        ],

        'nil' => [
            'name' => 'nil',
            'offset' => "00000000"
        ],

    ];
}
