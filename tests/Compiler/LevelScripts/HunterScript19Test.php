<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HunterScript19Test extends KernelTestCase
{

    public function test()
    {


        $script = "
scriptmain HunterScript;

entity SobbingWoman(hunter) : et_name;

TYPE
		tLevelState = ( StartOfLevel, PickedUpSyringe, InOffice, LuredHunter, KilledHunter, BeforeElevator, LeftElevator, BeforeBeasts, SpottedByCamera, TurnedOnTV, InCarPark, EndOfLevel );

var
	me : string[30];
	lLevelState : level_var tLevelState;
	bMeleeTutDone: level_var boolean;

script OnCreate;
begin
	me := GetEntityName(this);

	WriteDebug(me, ' : OnCreate');

	{ Make character unlockonable, invulnerable, unexecutable }
	SetPedLockonable(this, TRUE);
	SetEntityInvulnerable(this, TRUE);
	{SetHunterExecutable(this, FALSE);}
	SetDamage(this, 100);
	SetHunterHideHealth(me, 0);
	SetHunterRunSpeed(me, 0.8);

	AISetHunterOnRadar(me, FALSE);

	AIEntityPlayAnimLooped(me, 'BAT_INMATE_SMACK_HEAD_ANIM', 0.0);

	AISetHunterIdleActionMinMax(me, AISCRIPT_IDLE_STANDSTILL, AISCRIPT_HIGHPRIORITY,2000,5000);
	
	SetHunterMeleeTraits(this, MTT_TRAINING);
	
end;

script OnDeath;
VAR
	iTime : integer;
	pos, pos2 : vec3d;
	Door : entityPtr;
begin
	
	while IsExecutionInProgress do sleep(10);
	
	AIDefineGoalGotoNodeIdle('goalLeoLeave', 'leo(hunter)', AISCRIPT_HIGHPRIORITY, 'LEOGETOUT', AISCRIPT_RUNMOVESPEED, TRUE);

	DisableUserInput;
	sleep(400);
	
	iTime := PlayerPlayFullBodyAnim('ASY_REACTKILL_2');
	
	if iTime > 0 then begin
		sleep(iTime);
	end else begin
		writedebug('Could not play player anim');	
	end;
	
	PlayerFullBodyAnimDone;
	
	EnableUserInput;
	
	enableAction(1, TRUE);
	
	if (GetEntity('Syringe_(CT)')) = nil then
	begin
		SetVector(pos, -9.73, 7.62, 26.7296);
		SpawnMovingEntity('Syringe_(CT)', pos, 'ScriptCreateName');		
	end;

	{ Advance level state }
	lLevelState := PickedUpSyringe;
	RunScript('A01_Escape_Asylum', 'OnLevelStateSwitch');
	
	sleep(1000);

	SetEntityInvulnerable(GetPlayer, FALSE);

{ GAV - I commented this out so that Leo keeps doing something after the fight
	AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE1');
	AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE2');
	AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE3');
	AiEntityPlayAnimLooped('leo(hunter)', 'BAT_IDLELOOP_IDLE_ANIM', 0.0);}
		
	ClearLevelGoal('GOAL8');
		
	if (GetDamage(GetPlayer) < 125) AND ((GetEntity('G_First_Aid_(CT)13') <> NIL) OR (GetEntity('G_First_Aid_(CT)14') <> NIL) OR (GetEntity('G_First_Aid_(CT)15') <> NIL)) then
	begin
		{Health Not Full}
		
		SetVector(pos, -13.0487, 6.96591, 26.7296);
		CreateSphereTrigger(pos, 0.330523, 'triggerBlipHealth');
		
		FrisbeeSpeechPlay('ML12', 100,100);
		RadarPositionSetEntity(GetEntity('triggerBlipHealth'), MAP_COLOR_LOCATION);
		KillGameText;
		DisplayGameText('PYU1');
		
		if GetDifficultyLevel <> DIFFICULTY_NORMAL then
		begin
			RunScript('triggerNewMeleeTutTwo', 'ShowRadarHelp');
		end;
	end else
	begin
		{Health is FULL}
		
		if GetDifficultyLevel <> DIFFICULTY_NORMAL then
		begin
			RunScript('triggerNewMeleeTutTwo', 'ShowRadarHelp');
		end;

		if (GetEntity('Syringe_(CT)') <> NIL) AND (IsNamedItemInInventory(GetPlayer, CT_SYRINGE ) = -1) then
		begin
			{Need Syringe}
			bMeleeTutDone := TRUE;
			
			{SAY GET SYRINGE}
			while NOT IsFrisbeeSpeechCompleted do sleep(100);
			FrisbeeSpeechPlay('LEO16', 127, 127);
				
			SetVector(pos, -9.72581, 7.38742, 26.8131);
			CreateSphereTrigger(pos, 0.42, 'SyringeTarget');
			RadarPositionSetEntity(GetEntity('SyringeTarget'), MAP_COLOR_BLUE);
			SetLevelGoal('GOAL2C');
				
			SetVector(pos, -15.8301, 6.18554, 30.7672);
			CreateSphereTrigger(pos, 0.997306, 'triggerSyringeRemind');
			
		end else begin
			{Have Syringe}

			AIEntityCancelAnim('leo(hunter)', 'BAT_IDLELOOP_IDLE_ANIM');			
			
			{ GAV - Added this to ensure the door opens }
			AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE1');
			AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE2');
			AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE3');

			if GetEntity('leo(hunter)') <> NIL then
			begin
				AIAddGoalForSubpack('leo(leader)', 'leopack', 'goalLeoLeave');
			end;
				
			{Open sliding door }
			Door := GetEntity('asylum_cell_door_slide_(SD)');

			while (AIIsGoalNameInSubpack('leo(leader)', 'leopack', 'goalLeoLeave')) do sleep(100);
			DestroyEntity(GetEntity('leo(hunter)'));
						
			UnLockEntity(Door);
			GraphModifyConnections(Door, AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
			
			Sleep(50);
			SetSlideDoorAjarDistance(Door, 1.1);
			SetDoorState(Door, DOOR_OPENING);
		end;
	end;	
	
	RemoveThisScript;
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
'20020000', //Offset in byte
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
'20020000', //Offset in byte
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
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'5e010000', //SetEntityInvulnerable Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'2f010000', //SetDamage Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
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
'ee010000', //SetHunterHideHealth Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc4c3f', //value 1061997773
'10000000', //nested call return result
'01000000', //nested call return result
'f1010000', //SetHunterRunSpeed Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
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
'a8010000', //aisethunteronradar Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
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
'10000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1b000000', //value 27
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'b4010000', //AIEntityPlayAnimLooped Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
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
'80010000', //AISetHunterIdleActionMinMax Call
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
'77020000', //SetHunterMeleeTraits Call
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
'20000000', //Offset in byte
'51020000', //IsExecutionInProgress Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a0030000', //Offset (line number 232)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'68030000', //Offset (line number 218)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
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
'4c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'b1010000', //AIDefineGoalGotoNodeIdle Call
'f6000000', //DisableUserInput Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'90010000', //value 400
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'94020000', //PlayerPlayFullBodyAnim Call
'15000000', //unknown
'04000000', //unknown
'04000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'42000000', //statement (core)(operator greater)
'18050000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50050000', //Offset (line number 340)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'84050000', //Offset (line number 353)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c000000', //StringCat Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1b000000', //value 27
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'96020000', //PlayerFullBodyAnimDone Call
'f5000000', //EnableUserInput Call
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
'62030000', //EnableAction Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'30060000', //Offset (line number 396)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'28070000', //Offset (line number 458)
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'14ae1b41', //value 1092333076
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
'0ad7f340', //value 1089722122
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'39d6d541', //value 1104533049
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //LockEntity Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'7a000000', //SpawnMovingEntity Call
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'24170000', //unknown
'04000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
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
'e4000000', //RunScript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'5e010000', //SetEntityInvulnerable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //clearlevelgoal Call
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'84000000', //GetDamage Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'7d000000', //value 125
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3d000000', //statement (core)(operator smaller)
'64080000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'10000000', //nested call return result
'01000000', //nested call return result
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
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'e4080000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'64090000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'04010000', //displaygametext Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'f8090000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'0f000000', //unknown
'04000000', //unknown
'25000000', //statement (AND operator)
'01000000', //statement (AND operator)
'04000000', //statement (AND operator)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a00c0000', //Offset (line number 808)
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7ac75041', //value 1095812986
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
'bce8de40', //value 1088350396
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'39d6d541', //value 1104533049
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'503aa93e', //value 1051277904
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
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
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'08010000', //killgametext Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'34010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'9f020000', //getdifficultylevel Call
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
'40000000', //statement (core)(operator un-equal)
'280c0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'980c0000', //Offset (line number 806)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c010000', //SetMoverIdlePosition Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54010000', //aiaddleaderenemy Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'3c000000', //statement (init statement start offset)
'd0150000', //Offset (line number 1396)
'9f020000', //getdifficultylevel Call
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
'40000000', //statement (core)(operator un-equal)
'ec0c0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'5c0d0000', //Offset (line number 855)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c010000', //SetMoverIdlePosition Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54010000', //aiaddleaderenemy Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'd40d0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'10000000', //nested call return result
'01000000', //nested call return result
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'71000000', //value 113
'10000000', //nested call return result
'01000000', //nested call return result
'30010000', //IsNamedItemInInventory Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'2a000000', //unknown
'01000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'500e0000', //Offset (line number 916)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'0f000000', //unknown
'04000000', //unknown
'25000000', //statement (AND operator)
'01000000', //statement (AND operator)
'04000000', //statement (AND operator)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50110000', //Offset (line number 1108)
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'90170000', //unknown
'04000000', //unknown
'b0030000', //IsFrisbeeSpeechCompleted Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd80e0000', //Offset (line number 950)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'940e0000', //Offset (line number 933)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'64010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7f000000', //value 127
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7f000000', //value 127
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'eb9c1b41', //value 1092328683
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
'bf65ec40', //value 1089234367
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3b81d641', //value 1104576827
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3d0ad73e', //value 1054280253
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'17487d41', //value 1098729495
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
'f2efc540', //value 1086713842
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3a23f641', //value 1106649914
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'10000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'724f7f3f', //value 1065308018
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84010000', //setvector Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'3c000000', //statement (init statement start offset)
'd0150000', //Offset (line number 1396)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4010000', //AIEntityPlayAnimLooped Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd4010000', //SetDoorOpenAngleOut Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'38130000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd4130000', //Offset (line number 1269)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1c000000', //value 28
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'20000000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a5020000', //AIIsGoalNameInSubpack Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd0140000', //Offset (line number 1332)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'14140000', //Offset (line number 1285)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'a0020000', //DestroyEntity Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'20000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'20000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'20000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'20000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'e8000000', //RemoveThisScript Call
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block
            

        ];

        $compiler = new Compiler();
        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/0#levelscript.srce'));


        $compiler = new Compiler();
        $compiled = $compiler->parse($script, $levelScriptCompiled);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
//                    echo ($index + 1) . '->' . $item . "\n";
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }


}