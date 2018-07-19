<?php
namespace App\Service\Compiler\FunctionMap;

class Manhunt2 {


    public static $constants = [

        'AISCRIPT_GRAPHLINK_ALLOW_NOTHING'  => [
            'offset' => "00000000"
        ],
        
        'AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING' => [
            "offset" => "03000000"
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

        'CT_G_FIRST_AID' => [
            "offset" => "09000000"
        ],

        'CT_SYRINGE' => [
            "offset" => "71000000"
        ],

    ];

    public static $levelVarBoolean = [

        'cellTwoOpen'                     => [
            'offset' => "74170000"
        ],
        'stealthTwoHeard'                 => [
            'offset' => "c0170000"
        ],
        'stealthThreeHeard'               => [
            'offset' => "c4170000"
        ],
        'stealthTwoLooper'                => [
            'offset' => "b4170000"
        ],
        'stealthThreeLooper'              => [
            'offset' => "b8170000"
        ],
        'reminderSet'                     => [
            'offset' => "e8170000"
        ],
        'spottedYard'                     => [
            'offset' => "ac170000"
        ],
        'checkOpenerNeeded'               => [
            'offset' => "84170000"
        ],
        'aCellHasChanged'                 => [
            'offset' => "88170000"
        ],
        'lElevatorLevel'                  => [
            'offset' => "30170000"
        ],
        'tLevelState'                     => [
            'offset' => "24170000"
        ],

        'stealthOneLooper'                     => [
            'offset' => "b0170000"
        ],

        'stealthOneHeard'                     => [
            'offset' => "bc170000"
        ],

        'SpecialStart'                     => [
            'offset' => "c00f0000"
        ],

        'lLevelState'                     => [
            'offset' => "980f0000"
        ],

    ];


    public static $functions = [

        "randnum"                           => [
            'offset' => "69000000"
        ],
        "getdifficultylevel"                => [
            'offset' => "9f020000"
        ],

        "aicutsceneentityenable"            => [
            'offset' => "a9020000"
        ],

        "cutsceneend"                       => [
            'offset' => "49010000"
        ],
        "clearlevelgoal"                    => [
            'offset' => "42020000"
        ],
        "cutscenestart"                     => [
            'offset' => "48010000"
        ],
        "cutsceneregisterskipscript"        => [
            'offset' => "20030000"
        ],

        "displaygametext"                   => [
            'offset' => "04010000"
        ],

        "frisbeespeechplay"                 => [
            'offset' => "66030000"
        ],

        'getentityname'                     => [
            'offset' => "86000000"
        ],
        "getdoorstate"                      => [
            'offset' => "96000000"
        ],
        "getentity"                         => [
            'offset' => "77000000"
        ],

        "hudtoggleflashflags"               => [
            'offset' => "b2020000"
        ],

        "iswhitenoisedisplaying"            => [
            'offset' => "e7020000"
        ],

        "killgametext"                      => [
            'offset' => "08010000"
        ],
        "killscript"                        => [
            'offset' => "e5000000"
        ],

        "runscript"                         => [
            'offset' => "e4000000"
        ],


        "setdoorstate" => [
            'name'      =>  'SetDoorState',
            'offset'    =>  '97000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state
             * - DOOR_CLOSING  => 3
             */
            'params'    =>  [ 'Entity', 'Integer' ],
            'desc'      =>  ''
        ],



        "setswitchstate"                    => [
            'offset' => "95000000"
        ],
        "setcurrentlod"                     => [
            'offset' => "2d010000"
        ],
        "setshowhudincutscene"              => [
            'offset' => "86030000"
        ],
        "setvector"                         => [
            'offset' => "84010000"
        ],
        "setcameraposition"                 => [
            'offset' => "92010000"
        ],
        "setcameraview"                     => [
            'offset' => "8f010000"
        ],
        "setzoomlerp"                       => [
            'offset' => "b5020000"
        ],
        "setlevelgoal"                      => [
            'offset' => "41020000"
        ],
        "setslidedoorspeed"                 => [
            'offset' => "ae010000"
        ],
        "sleep"                             => [
            'offset' => "6a000000"
        ],


        "togglehudflag"                     => [
            'offset' => "7f020000"
        ],
        "aiaddentity"                       => [
            'offset' => "4d010000"
        ],


        "aisethunteronradar" => [
            'name'      =>  'aisethunteronradar',
            'offset'    =>  'a8010000',
            /**
             * Parameters
             * 1: ref to me (me[30])
             * 2: boolean
             */
            'params'    =>  [ 'String', 'Bollean' ],
            'desc'      =>  'Set the Hunter visibility on the Players Radar'
        ],



        "setnextlevelbyname" => [
            'name'      =>  'SetNextLevelByName',
            'offset'    =>  '4c030000',
            /**
             * Parameters
             * 1: string level name
             * - A02_The_Old_House
             */
            'params'    =>  [ 'String' ],
            'desc'      =>  ''
        ],




        "aisetentityasleader"               => [
            'offset' => "4f010000"
        ],
        "aisetleaderinvisible"              => [
            'offset' => "6d020000"
        ],
        "aiaddleaderenemy"                  => [
            'offset' => "54010000"
        ],
        "aientityalwaysenabled"             => [
            'offset' => "bf010000"
        ],
        "aiaddsubpackforleader"             => [
            'offset' => "50010000"
        ],
        "aisetsubpackcombattype"            => [
            'offset' => "82010000"
        ],
        "aidefinegoalhuntenemy"             => [
            'offset' => "58010000"
        ],
        "aiaddgoalforsubpack"               => [
            'offset' => "56010000"
        ],


        "aiaddplayer"                       => [
            'offset' => "5b010000"
        ],
        "hideentity"                        => [
            'offset' => "83000000"
        ],
        "setslidedoorajardistance"          => [
            'offset' => "9b010000"
        ],

        "setmaxnumberofrats"                => [
            'offset' => "a8020000"
        ],
        "switchlitteron"                    => [
            'offset' => "a4020000"
        ],

        "writedebug"                        => [
            'offset' => "73000000"
        ],
        "setqtmbaseprobability"             => [
            'offset' => "ac030000"
        ] ,

//
        "graphmodifyconnections"  => [
            'name'      =>  'GraphModifyConnections',
            'offset'    =>  'e9000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2:
             * - AISCRIPT_GRAPHLINK_ALLOW_NOTHING => 0
             * - AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING => 3
             */
            'params'    =>  [ 'Entity', 'Integer' ],
            'desc'      =>  ''
        ],

        "unfreezeentity" => [
            'name'      =>  'UnFreezeEntity',
            'offset'    =>  '38010000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params'    =>  [ 'Entity' ],
            'desc'      =>  ''
        ],

        "lockentity" => [
            'name'      =>  'LockEntity',
            'offset'    =>  '98000000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params'    =>  [ 'Entity' ],
            'desc'      =>  ''
        ],

        "entityplayanim" => [
            'name'      =>  'EntityPlayAnim',
            'offset'    =>  'a1010000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: animation id ?
             * - PAT_raindow
             * 3: active state?
             * - true
             */
            'params'    =>  [ 'Entity', 'String', 'Integer' ],
            'desc'      =>  ''
        ],

        "setentityscriptsfromentity" => [
            'name'      =>  'SetEntityScriptsFromEntity',
            'offset'    =>  'd9010000',
            /**
             * Parameters
             * 1:
             * - SLockerC_(O)
             * 2:
             * - SLockerC_(O)01
             * - SLockerC_(O)02
             */
            'params'    =>  [ 'String', 'String' ],
            'desc'      =>  ''
        ],

        "entityignorecollisions" => [
            'name'      =>  'EntityIgnoreCollisions',
            'offset'    =>  'a2020000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state
             * - true
             * - false
             */
            'params'    =>  [ 'Entity', 'Integer' ],
            'desc'      =>  ''
        ],

        "aientitycancelanim" => [
            'name'      =>  'AIEntityCancelAnim',
            'offset'    =>  '17020000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: animation name ?
             * - BAT_INMATE_SMACK_HEAD_ANIM
             */
            'params'    =>  [ 'String', 'String' ],
            'desc'      =>  ''
        ],

        "aisetentityidleoverride" => [
            'name'      =>  'AISetEntityIdleOverRide',
            'offset'    =>  'b5010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state 1 boolean
             * 3: state 2 boolean
             */
            'params'    =>  [ 'String', 'Boolean', 'Boolean' ],
            'desc'      =>  ''
        ],

        "setentityinvulnerable" => [
            'name'      =>  'SetEntityInvulnerable',
            'offset'    =>  '5e010000',
            /**
             * Parameters
             * 1: result of getEntity
             * 2: state boolean
             */
            'params'    =>  [ 'Entity', 'Boolean' ],
            'desc'      =>  ''
        ],

        "aimakeentityblind" => [
            'name'      =>  'AIMakeEntityBlind',
            'offset'    =>  '71010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state boolean
             */
            'params'    =>  [ 'String', 'Boolean' ],
            'desc'      =>  ''
        ],

        "aimakeentitydeaf" => [
            'name'      =>  'AIMakeEntityDeaf',
            'offset'    =>  '72010000',
            /**
             * Parameters
             * 1: Entity name
             * - SobbingWoman(hunter)
             * 2: state boolean
             */
            'params'    =>  [ 'String', 'Boolean' ],
            'desc'      =>  ''
        ],

        "aiaddhuntertoleadersubpack" => [
            'name'      =>  'AIAddHunterToLeaderSubpack',
            'offset'    =>  '52010000',
            /**
             * Parameters
             * 1: Entity name
             * - leader(leader)
             * 2: unknown string
             * - subManWoman
             * 3: Entity name
             * - SobbingWoman(hunter)
             */
            'params'    =>  [ 'String', 'String', 'String' ],
            'desc'      =>  ''
        ],

        "playerdropbody" => [
            'name'      =>  'PlayerDropBody',
            'offset'    =>  'b4020000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [  ],
            'desc'      =>  ''
        ],

        "playerfullbodyanimdone" => [
            'name'      =>  'PlayerFullBodyAnimDone',
            'offset'    =>  '96020000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [ ],
            'desc'      =>  ''
        ],

        "enableuserinput" => [
            'name'      =>  'EnableUserInput',
            'offset'    =>  'f5000000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [ ],
            'desc'      =>  ''
        ],

        "showentity" => [
            'name'      =>  'ShowEntity',
            'offset'    =>  '82000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params'    =>  [ 'Entity' ],
            'desc'      =>  ''
        ],

        "cutscenecamerainit" => [
            'name'      =>  'CutSceneCameraInit',
            'offset'    =>  '5f030000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [ ],
            'desc'      =>  ''
        ],

        "cutscenecamerasetpos" => [
            'name'      =>  'CutSceneCameraSetPos',
            'offset'    =>  '5a030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             * 3: float
             * 4: float
             */
            'params'    =>  [ 'Float', 'Float', 'Float', 'Float' ],
            'desc'      =>  ''
        ],

        "cutscenecamerasettarget" => [
            'name'      =>  'CutSceneCameraSetTarget',
            'offset'    =>  '5b030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             * 3: float
             * 4: float
             */
            'params'    =>  [ 'Float', 'Float', 'Float', 'Float' ],
            'desc'      =>  ''
        ],

        "cutscenecamerasetfov" => [
            'name'      =>  'CutSceneCameraSetFOV',
            'offset'    =>  '5c030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             */
            'params'    =>  [ 'Float', 'Float' ],
            'desc'      =>  ''
        ],

        "cutscenecamerasetroll" => [
            'name'      =>  'CutSceneCameraSetRoll',
            'offset'    =>  '5d030000',
            /**
             * Parameters
             * 1: float
             * 2: float
             */
            'params'    =>  [ 'Float', 'Float' ],
            'desc'      =>  ''
        ],

        "cutscenecamerasethandycam" => [
            'name'      =>  'CutSceneCameraSetHandyCam',
            'offset'    =>  '6d030000',
            /**
             * Parameters
             * 1: state boolean
             */
            'params'    =>  [ 'Boolean' ],
            'desc'      =>  ''
        ],

        "aidefinegoalgotonodeidle" => [
            'name'      =>  'AIDefineGoalGotoNodeIdle',
            'offset'    =>  'b1010000',
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
            'params'    =>  [ 'String', 'String', 'Integer', 'String', 'Integer', 'Boolean' ],
            'desc'      =>  ''
        ],

        "setpedlockonable" => [
            'name'      =>  'SetPedLockonable',
            'offset'    =>  '97020000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: state boolean
             */
            'params'    =>  [ 'Entity', 'Boolean' ],
            'desc'      =>  ''
        ],

        "moveentity" => [
            'name'      =>  'MoveEntity',
            'offset'    =>  '7d000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: ref to vec3d (3x float)
             * 3: integer
             */
            'params'    =>  [ 'Entity', 'Vec3D', 'Integer' ],
            'desc'      =>  ''
        ],

        "setpedorientation" => [
            'name'      =>  'SetPedOrientation',
            'offset'    =>  '4d000000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: integer
             * - 55
             */
            'params'    =>  [ 'Entity', 'Integer' ],
            'desc'      =>  ''
        ],

        "setmoverstate" => [
            'name'      =>  'SetMoverState',
            'offset'    =>  '3a010000',
            /**
             * Parameters
             * 1: result of GetEntity
             * 2: integer
             * - MOVER_FORWARD => 1
             */
            'params'    =>  [ 'Entity', 'Integer' ],
            'desc'      =>  ''
        ],

        "aisetidlehomenode" => [
            'name'      =>  'AISetIdleHomeNode',
            'offset'    =>  '83010000',
            /**
             * Parameters
             * 1: string ref to me (me[30])
             * 2: string
             * - AMBUSHNODE
             */
            'params'    =>  [ 'String', 'String' ],
            'desc'      =>  ''
        ],

        "radarpositionclearentity" => [
            'name'      =>  'RadarPositionClearEntity',
            'offset'    =>  'e1020000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params'    =>  [ 'Entity' ],
            'desc'      =>  ''
        ],

        "createboxtrigger" => [
            'name'      =>  'CreateBoxTrigger',
            'offset'    =>  '28010000',
            /**
             * Parameters
             * 1: vec3D
             * 2: vec3D
             * 3: String
             * - triggerOutOfWindow
             */
            'params'    =>  [ 'Vec3D', 'Vec3D', 'String' ],
            'desc'      =>  ''
        ],

        "removethisscript" => [
            'name'      =>  'RemoveThisScript',
            'offset'    =>  'e8000000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [  ],
            'desc'      =>  ''
        ],

        "sethuntermeleetraits" => [
            'name'      =>  'SetHunterMeleeTraits',
            'offset'    =>  '77020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: int
             * - MTT_HOOD_MEDIUM => 2
             */
            'params'    =>  [ 'This', 'Integer' ],
            'desc'      =>  ''
        ],

        "aisethunteridleactionminmaxradius" => [
            'name'      =>  'AISetHunterIdleActionMinMaxRadius',
            'offset'    =>  'a4010000',
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
            'params'    =>  [ 'String', 'Integer', 'Integer', 'Integer', 'Integer', 'Float' ],
            'desc'      =>  ''
        ],

        "sethunterhidehealth" => [
            'name'      =>  'SetHunterHideHealth',
            'offset'    =>  'ee010000',
            /**
             * Parameters
             * 1: string
             * 2: state int
             */
            'params'    =>  [ 'String', 'Integer'],
            'desc'      =>  ''
        ],

        "setpeddonotdecay" => [
            'name'      =>  'SetPedDoNotDecay',
            'offset'    =>  '6b020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: boolean
             */
            'params'    =>  [ 'This', 'Boolean'],
            'desc'      =>  ''
        ],

        "enableuseable" => [
            'name'      =>  'EnableUseable',
            'offset'    =>  'e5020000',
            /**
             * Parameters
             * 1: ref to this
             * 2: boolean
             */
            'params'    =>  [ 'This', 'Boolean'],
            'desc'      =>  ''
        ],

        "setmoveridleposition" => [
            'name'      =>  'SetMoverIdlePosition',
            'offset'    =>  '3c010000',
            /**
             * Parameters
             * 1: ref to this
             * 2: vec3D
             */
            'params'    =>  [ 'This', 'vec3D'],
            'desc'      =>  ''
        ],

        "movemovertoidleposition" => [
            'name'      =>  'MoveMoverToIdlePosition',
            'offset'    =>  '3d010000',
            /**
             * Parameters
             * 1: ref to this
             * 2: vec3D
             */
            'params'    =>  [ 'This', 'vec3D'],
            'desc'      =>  ''
        ],

        "getplayer" => [
            'name'      =>  'GetPlayer',
            'offset'    =>  '8a000000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [ ],
            'desc'      =>  ''
        ],
//

        "setmoverspeed" => [
            'name'      =>  'SetMoverSpeed',
            'offset'    =>  '40010000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [ ],
            'desc'      =>  ''
        ],

        "playaudiooneshotfromentity" => [
            'name'      =>  'PlayAudioOneShotFromEntity',
            'offset'    =>  '5c020000',
            /**
             * Parameters
             * none
             */
            'params'    =>  [ ],
            'desc'      =>  ''
        ],


        "playscriptaudiostreamfromentityauto" => [
            'name'      =>  'PlayScriptAudioStreamFromEntityAuto',
            'offset'    =>  '6b030000',
            /**
             * Parameters
             * 1: String
             * - NEWLIFT
             * 2: integer
             * 3: result of GetPlayer
             * 4: integer
             */
            'params'    =>  [ 'String', 'Integer', 'GetPlayer', 'Integer' ],
            'desc'      =>  ''
        ],

        "createspheretrigger" => [
            'name'      =>  'CreateSphereTrigger',
            'offset'    =>  'a3000000',
            /**
             * Parameters
             * 1: Vec3D
             * 2: Float
             * 3: string
             * - triggerVisionCheck
             */
            'params'    =>  [ 'Vec3D', 'Float', 'String' ],
            'desc'      =>  ''
        ],

        "destroyentity" => [
            'name'      =>  'DestroyEntity',
            'offset'    =>  'a0020000',
            /**
             * Parameters
             * 1: result of GetEntity
             */
            'params'    =>  [ 'Entity' ],
            'desc'      =>  ''
        ],

        "getanimationlength" => [
            'name'      =>  'GetAnimationLength',
            'offset'    =>  '49030000',
            /**
             * Parameters
             * 1: string
             */
            'params'    =>  [ 'String' ],
            'desc'      =>  ''
        ],

        "unlockentity" => [
            'name'      =>  'UnLockEntity',
            'offset'    =>  '99000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params'    =>  [ 'Entity' ],
            'desc'      =>  ''
        ],

        "spawnEntitywithdirection" => [
            'name'      =>  'SpawnEntityWithDirection',
            'offset'    =>  '7c000000',
            /**
             * Parameters
             * 1: string
             * - Ins_BodA
             * 2: Vec3d
             * 3: string
             * - Runner(hunter)
             * 4: Vec3d
             */
            'params'    =>  [ 'String', 'Vec3d', 'String', 'Vec3d' ],
            'desc'      =>  ''
        ],

        "getentityposition" => [
            'name'      =>  'GetEntityPosition',
            'offset'    =>  '78000000',
            /**
             * Parameters
             * 1: result of getEntity
             */
            'params'    =>  [ 'Entity' ],
            'return'    =>  'Vec3d',
            'desc'      =>  ''
        ],

        "setcolourramp" => [
            'name'      =>  'SetColourRamp',
            'offset'    =>  'ab030000',
            /**
             * Parameters
             * 1: STRING
             * - FE_colramps
             * 2: int
             * 3: float
             */
            'params'    =>  [ 'String', 'Integer', 'Float' ],
            'return'    =>  'void',
            'desc'      =>  ''
        ],

        "getambientaudiotrack" => [
            'name'      =>  'GetAmbientAudioTrack',
            'offset'    =>  '77030000',
            /**
             * Parameters
             * 1: none
             */
            'params'    =>  [ ],
            'return'    =>  'Integer',
            'desc'      =>  ''
        ],

        "sethunterhideHealth" => [
            'name'      =>  'SetHunterHideHealth',
            'offset'    =>  '2f010000',
            /**
             * Parameters
             * 1: string[30]
             * 2: int
             */
            'params'    =>  [ 'String[]', 'Integer' ],
            'return'    =>  'void',
            'desc'      =>  ''
        ],

        "isentityalive" => [
            'name'      =>  'IsEntityAlive',
            'offset'    =>  'aa010000',
            /**
             * Parameters
             * 1: string
             */
            'params'    =>  [ 'String' ],
            'return'    =>  'Boolean',
            'desc'      =>  ''
        ],

        "setmaxscoreforlevel" => [
            'name'      =>  'SetMaxScoreForLevel',
            'offset'    =>  '59030000',
            /**
             * Parameters
             * 1: integer
             */
            'params'    =>  [ 'Integer' ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "fakehunterdestroyall" => [
            'name'      =>  'FakeHunterDestroyAll',
            'offset'    =>  'c3030000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "enableaction" => [
            'name'      =>  'EnableAction',
            'offset'    =>  '62030000',
            /**
             * Parameters
             * 1: Integer
             * 2: state
             */
            'params'    =>  [ "integer", "Boolean" ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "killthisscript" => [
            'name'      =>  'KillThisScript',
            'offset'    =>  'e7000000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [  ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "playerplayfullbodyanim" => [
            'name'      =>  'PlayerPlayFullBodyAnim',
            'offset'    =>  '94020000',
            /**
             * Parameters
             * 1: string
             * - ASY_REACTKILL_2
             */
            'params'    =>  [ 'String' ],
            'return'    =>  'Integer',
            'desc'      =>  ''
        ],

        "disableuserinput" => [
            'name'      =>  'DisableUserInput',
            'offset'    =>  'f6000000',
            /**
             * Parameters
             * 1: string
             * - ASY_REACTKILL_2
             */
            'params'    =>  [ 'String' ],
            'return'    =>  'Integer',
            'desc'      =>  ''
        ],

        "getdamage" => [
            'name'      =>  'GetDamage',
            'offset'    =>  '84000000',
            /**
             * Parameters
             * 1: Player?
             * - GetPlayer
             */
            'params'    =>  [ 'Player' ],
            'return'    =>  'Integer',
            'desc'      =>  ''
        ],

        "aitriggersoundknownlocationnoradar" => [
            'name'      =>  'AITriggerSoundKnownLocationNoRadar',
            'offset'    =>  'b8020000',
            /**
             * Parameters
             * 1: String
             * - LURE_HIGH
             * 2: Player
             * - GetPlayer
             */
            'params'    =>  [ 'String', 'Player' ],
            'return'    =>  'Integer',
            'desc'      =>  ''
        ],

        "spawnmovingentity" => [
            'name'      =>  'SpawnMovingEntity',
            'offset'    =>  '7a000000',
            /**
             * Parameters
             * 1: string Entity Name ?
             * 2: vec3d
             * 4: string script name ?
             */
            'params'    =>  [ 'String', 'Vec3d', 'String' ],
            'return'    =>  '',
            'desc'      =>  ''
        ],

        "isolayerinsafezone" => [
            'name'      =>  'IsPlayerInSafeZone',
            'offset'    =>  '89020000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "isplayerrunning" => [
            'name'      =>  'IsPlayerRunning',
            'offset'    =>  'ee020000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "isplayersprinting" => [
            'name'      =>  'IsPlayerSprinting',
            'offset'    =>  'ef020000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "getpedorientation" => [
            'name'      =>  'GetPedOrientation',
            'offset'    =>  '8d030000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "isgametextdisplaying" => [
            'name'      =>  'IsGameTextDisplaying',
            'offset'    =>  '07010000',
            /**
             * Parameters
             * - none
             */
            'params'    =>  [ ],
            'return'    =>  'Boolean',
            'desc'      =>  ''
        ],


        "getlastitempickedup" => [
            'name'      =>  'GetLastItemPickedUp',
            'offset'    =>  'c9010000',
            /**
             * Parameters
             * 1: player
             */
            'params'    =>  [ "Player" ],
            'return'    =>  'Item',
            'desc'      =>  ''
        ],

        "attachtoentity" => [
            'name'      =>  'AttachToEntity',
            'offset'    =>  '93000000',
            /**
             * Parameters
             * 1: this
             * 2: result of GetEntity
             */
            'params'    =>  [ "This", "Entity" ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "isnameditemininventory" => [
            'name'      =>  'IsNamedItemInInventory',
            'offset'    =>  '30010000',
            /**
             * Parameters
             * 1: Player
             * 2: Integer
             */
            'params'    =>  [ "Player", "Integer" ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "helisetlight" => [
            'name'      =>  'HeliSetLight',
            'offset'    =>  '31030000',
            /**
             * Parameters
             * 1: This
             * 2: Boolean
             */
            'params'    =>  [ "This", "Boolean" ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "helisetmovespeed" => [
            'name'      =>  'HeliSetMoveSpeed',
            'offset'    =>  '3a030000',
            /**
             * Parameters
             * 1: This
             * 2: Float
             */
            'params'    =>  [ "This", "Float" ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "insidetrigger" => [
            'name'      =>  'InsideTrigger',
            'offset'    =>  'a5000000',
            /**
             * Parameters
             * 1: Entity
             * 2: Player
             */
            'params'    =>  [ "Entity", "Player" ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "isplayerpositionknown" => [
            'name'      =>  'IsPlayerPositionKnown',
            'offset'    =>  '6e030000',
            /**
             * Parameters
             *
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

        "isplayerwalking" => [
            'name'      =>  'IsPlayerWalking',
            'offset'    =>  'ed020000',
            /**
             * Parameters
             *
             */
            'params'    =>  [ ],
            'return'    =>  'Void',
            'desc'      =>  ''
        ],

    ];



}