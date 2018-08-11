<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StealthyThreeTest extends KernelTestCase
{

    public function test()
    {
        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;
        $script = "

scriptmain StealthyThree;

entity StealthTutThree(hunter) : et_name;
{ strings final next mem 240 }
var
    me : string[30];    { 240 + (30 + 2) == 272 } 
    stealthTutSpotted : level_var integer;
    speechSaid : boolean; { 276  // ich hab 272}
   	stealthThreeLooper : level_var boolean;
    stealthThreeHeard : level_var boolean;
    stealthThreeDone : level_var boolean;
    stealthThreeFacingYou : level_var boolean;


script Init;
begin
    me := GetEntityName(this);

    WriteDebug(me, ' : Init');

    { Initialize AI }
	{AIAddEntity(me);}
	AIAddHunterToLeaderSubpack('leader(leader)', 'subStealthTut3', me);

	AISetEntityIdleOverRide(me, FALSE, FALSE);

	AISetHunterIdlePatrol(me,AISCRIPT_IDLE_PATROL, AISCRIPT_HIGHPRIORITY, 2000, 5000, 'pathstealththree');
	AISetIdlePatrolStop(me,'STOPSTEALTHTWO', 2, true);
	{AIMakeEntityDeaf(me, 0);}
	AIMakeEntityBlind(me, 0);
	
	speechSaid := FALSE;
	
	RadarPositionSetEntity(this, MAP_COLOR_HUNTER_IDLE);
	AIDefineGoalGotoNode('goalHideThree', me, AISCRIPT_HIGHPRIORITY, 'HIDERTHREE', AISCRIPT_RUNMOVESPEED, TRUE);
end;

script OnLowHearingOrAbove;
begin
	  if stealthThreeLooper = TRUE then
  		stealthThreeHeard := TRUE;
end;

script ShitOnMe;
VAR
	pos : vec3d;
	orient : real;
begin
	
	RadarPositionClearEntity(this);
	
	CutSceneStart;
			orient := GetPedOrientation(GetPlayer);
			CutSceneRegisterSkipScript(this, 'SkipMe');
		
			CutsceneCameraInit;
			CutscenecameraSetPos(0.0, -43.9962, 1.91624, 32.7128);
			CutscenecameraSetTarget(0.0, -71.1981, -4.72559, 42.0793);
			CutsceneCameraSetFOV(0.0, 45.0);
			CutsceneCameraSetRoll(0.0, 0.0);
			CutSceneCameraSetHandyCam(false);
			CutscenecameraStart;

			AISetEntityIdleOverride(GetEntityName(this), TRUE, TRUE);
		
			SetVector(pos, -44.687,  0, 38.396);
			MoveEntity(GetPlayer, pos, 1);
			SetPedOrientation(GetPlayer, 0);
			
			SetVector(pos, -47.867, 0, 34.31);
			MoveEntity(this, pos, 1); 	
			SetPedOrientation(this, 0);
					
			PlayScriptAudioStreamAuto('SFAIL3', 127);
			
			PlayerPlayFullBodyAnim('ASY_STEALTHFAIL_P3');
			AiEntityPlayAnim(GetEntityName(this), 'ASY_STEALTHFAIL_A3');
		
			EntityPlayAnim(GetEntity('A01_cameratripodShitter'), 'ASY_STEALTHFAIL_C3', false);
			
			sleep(3666);
			
		CutSceneEnd(false);
		PlayerFullBodyAnimDone;
		SetPedOrientation(GetPlayer, orient);
		
		{Play brush anim}
	{	DisableUSerInput;
		PlayerPlayFullBodyAnim('ASY_IDLE_WIPEOFF_ANIM');}
		
		AISetEntityIdleOverride(GetEntityName(this), FALSE, FALSE);
		AIEntityCancelAnim(me, 'ASY_STEALTHFAIL_A3');
		
		stealthThreeDone := TRUE;
		stealthTutSpotted := stealthTutSpotted + 1;
		DestroyEntity(GetEntity('A01_cameratripodShitter'));
		while not IsScriptAudioStreamCompleted do sleep(10);
		PlayScriptAudioStreamFromEntityAuto('LN3', 100, this ,10);
		while not IsScriptAudioStreamCompleted do sleep(10);		
	
	{AIMakeEntityDeaf(me, 0);
	AIMakeEntityBlind(me, 0);}
	
end;

script SkipMe;
begin
	PlayerFullBodyAnimDone;
	AIEntityCancelAnim(GetEntityName(this), 'ASY_STEALTHFAIL_A3');
end;


end.

        ";

        $expected = [

'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'86000000', //getentityname Call
'21000000', //Prepare string read (header)
'04000000', //Prepare string read (header)
'04000000', //Prepare string read (header)
'f0000000', //Offset in byte
'12000000', //parameter (read string array? assign?)
'03000000', //parameter (read string array? assign?)
'1e000000', //value 30
'10000000', //parameter (read string array? assign?)
'04000000', //parameter (read string array? assign?)
'10000000', //unknown
'03000000', //unknown
'48000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'b5010000', //AISetEntityIdleOverRide Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd0070000', //value 2000
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'88130000', //value 5000
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'71010000', //AIMakeEntityBlind Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'14010000', //unknown (276)
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'6f010000', //AIDefineGoalGotoNode Call
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'1b000000', //read LevelVar 
'b8170000', //LevelVar stealthThreeLooper
'04000000', //unknown
'01000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'01000000', //value 1
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'a8040000', //Offset (line number 298)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd8040000', //Offset (line number 310)
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'c4170000', //LevelVar stealthThreeHeard
'04000000', //unknown
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'10000000', //Offset in byte
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'e1020000', //RadarPositionClearEntity Call
'48010000', //cutscenestart Call

'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'8d030000', //GetPedOrientation Call
'15000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'20030000', //cutsceneregisterskipscript Call
'5f030000', //CutSceneCameraInit Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1cfc2f42', //value 1110440988
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5a47f53f', //value 1073039194
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8d90242', //value 1107483112
'10000000', //nested call return result
'01000000', //nested call return result
'5a030000', //CutSceneCameraSetPos Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6d658e42', //value 1116628333
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'09389740', //value 1083652105
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'34512842', //value 1109938484
'10000000', //nested call return result
'01000000', //nested call return result
'5b030000', //CutSceneCameraSetTarget Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003442', //value 1110704128
'10000000', //nested call return result
'01000000', //nested call return result
'5c030000', //CutSceneCameraSetFOV Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'5d030000', //CutSceneCameraSetRoll Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'6d030000', //CutSceneCameraSetHandyCam Call
'5e030000', //CutSceneCameraStart Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'86000000', //getentityname Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'b5010000', //AISetEntityIdleOverRide Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7dbf3242', //value 1110622077
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'81951942', //value 1108972929
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'7d000000', //MoveEntity Call
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'b0020000', //SetPedOrientation Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cf773f42', //value 1111455695
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'713d0942', //value 1107901809
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'7d000000', //MoveEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'b0020000', //SetPedOrientation Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74000000', //writedebugflush Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7f000000', //value 127
'10000000', //nested call return result
'01000000', //nested call return result
'6a030000', //PlayScriptAudioStreamAuto Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c000000', //SpawnEntityWithDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'94020000', //PlayerPlayFullBodyAnim Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'86000000', //getentityname Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b3010000', //AiEntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'a1010000', //EntityPlayAnim Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'520e0000', //value 3666
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'49010000', //cutsceneend Call
'96020000', //PlayerFullBodyAnimDone Call
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result

'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'10000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'b0020000', //SetPedOrientation Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'86000000', //getentityname Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'b5010000', //AISetEntityIdleOverRide Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'd0170000', //unknown
'04000000', //unknown
'1b000000', //unknown
'a0170000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'01000000', //value 1
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'31000000', //unknown
'01000000', //unknown
'04000000', //unknown
'1a000000', //unknown
'01000000', //unknown
'a0170000', //unknown
'04000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'a0020000', //DestroyEntity Call

'cf020000', //IsScriptAudioStreamCompleted Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'380c0000', //Offset (line number 782)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'f40b0000', //Offset (line number 765)


'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'04000000', //value 4
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6b030000', //PlayScriptAudioStreamFromEntityAuto Call
'cf020000', //IsScriptAudioStreamCompleted Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e80c0000', //Offset (line number 826)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'a40c0000', //Offset (line number 809)
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'96020000', //PlayerFullBodyAnimDone Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'86000000', //getentityname Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block

        ];

        $compiler = new Compiler();
        list($sectionCode, $sectionDATA) = $compiler->parse($script);

        if ($sectionCode != $expected){
            foreach ($sectionCode as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $sectionCode[$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }


}