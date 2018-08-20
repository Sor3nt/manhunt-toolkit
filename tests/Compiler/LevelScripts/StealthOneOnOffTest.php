<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StealthOneOnOffTest extends KernelTestCase
{

    public function test()
    {


        $script = "
scriptmain StealthOneOnOff;

ENTITY
	triggerStealthOneAware : et_name;
	
VAR
	stealthOneLooper : level_var boolean;
	stealthOneHeard : level_var boolean;
	alreadyDone : boolean;
	stealthOneDone : level_var boolean;

script OnCreate;
begin
	stealthOneHeard := FALSE;
	alreadyDone := FALSE;
end;
	
script OnEnterTrigger;
VAR
	pos : vec3d;
begin
	{AIMakeEntityDeaf('StealthTut(hunter)', 1);
	AIMakeEntityBlind('StealthTut(hunter)', 1);}
	{AIMakeEntityBlind('StealthTutTwo(hunter)', 0);
	AIMakeEntityBlind('StealthTutThree(hunter)', 0);}
	
	
	
	stealthOneLooper := TRUE;
	RunScript('triggerStealthOneAware', 'LoopBeforeSound');
	while stealthOneLooper = TRUE do
	begin
		sleep(200);
		If (NOT IsPlayerInSafeZone) OR (IsPlayerRunning) OR (IsPlayerSprinting) OR (stealthOneHeard = TRUE)  then
		begin

			if alreadyDone = FALSE then
			begin

				sleep(300);
			
				AIEntityCancelAnim('StealthTut(hunter)', 'ASY_INMATE_BARS_2');
				RunScript('StealthTut(hunter)', 'SpitOnMe');
				
				while stealthOneDone <> TRUE do sleep(10);
					
				SetEntityInvulnerable(GetEntity('StealthTut(hunter)'), true);
				alreadyDone := TRUE;
			end;
			
			{CHANGE BLIP TO RED}
			if stealthOneLooper = TRUE then
				RadarPositionSetEntity(GetEntity('StealthTut(hunter)'), MAP_COLOR_RED);
				
			writedebug('red');
		end;
	end;
	
	RadarPositionClearEntity(GetEntity('StealthTut(hunter)'));
	{RadarPositionClearEntity(GetEntity('StealthTut(hunter)'));}
			
	
end;

script LoopBeforeSound;
begin
	while alreadyDone <> TRUE do
	begin
		writedebug('looping sound before - ', GetAnimationLength('ASY_INMATE_BARS_2'));
		PlayAudioOneShotFromEntity(GetEntity('StealthTut(hunter)'), 'LEVEL', 'INMATE1', 80, 15);
		sleep(GetAnimationLength('ASY_INMATE_BARS_2'));
	end;
end;


script LoopAfterSound;
VAR
	counter : integer;
	
begin
	
	
	if GetEntity('StealthTut(hunter)') <> NIL then begin
	
		AIEntityCancelAnim('StealthTut(hunter)', 'ASY_INMATE_BARS_4');
		AiSetEntityIdleOverride('StealthTut(hunter)', FALSE, FALSE);		
		
		AIMakeEntityDeaf('StealthTut(hunter)', 0);
		AIMakeEntityBlind('StealthTut(hunter)', 0);
		
		AIAddGoalForSubpack('leader(leader)', 'subStealthTut1', 'goalHideOne');
		sleep(300);
		while AIIsGoalNameInSubpack('leader(leader)', 'subStealthTut1', 'goalHideOne') do sleep(10);
		
		AISetEntityIdleOverride('StealthTut(hunter)', TRUE, TRUE);
		AIEntityPlayAnimLooped('StealthTut(hunter)', 'BAT_INMATE_IDLELOOP_CROUCHED_ANIM', 0.0);
		
		SetEntityInvulnerable(GetEntity('StealthTut(hunter)'), TRUE);
	end;
end;

script OnLeaveTrigger;
begin
	{AIMakeEntityDeaf('StealthTut(hunter)', 0);
	AIMakeEntityBlind('StealthTut(hunter)', 0);}
	{RadarPositionSetEntity(GetEntity('StealthTut(hunter)'), MAP_COLOR_YELLOW);}
	
	stealthOneLooper := FALSE;
	RadarPositionSetEntity(GetEntity('StealthTut(hunter)'), MAP_COLOR_YELLOW);
	
end;

end.
        ";

        $expected = [

'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'00000000', //value 0
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'bc170000', //unknown
'04000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'58010000', //aidefinegoalhuntenemy Call
'01000000', //unknown
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
'0c000000', //Offset in byte
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'b0170000', //unknown
'04000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'1b000000', //unknown
'b0170000', //unknown
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
'58010000', //Offset (line number 86)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'88050000', //Offset (line number 354)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c8000000', //value 200
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'89020000', //IsPlayerInSafeZone Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'10000000', //nested call return result
'01000000', //nested call return result
'ee020000', //IsPlayerRunning Call
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'10000000', //nested call return result
'01000000', //nested call return result
'ef020000', //IsPlayerSprinting Call
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'10000000', //nested call return result
'01000000', //nested call return result
'1b000000', //unknown
'bc170000', //unknown
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
'34020000', //Offset (line number 141)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'80050000', //Offset (line number 352)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'58010000', //Offset
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
'b4020000', //Offset (line number 173)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'90040000', //Offset (line number 292)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2c010000', //value 300
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
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
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'1b000000', //unknown
'c8170000', //unknown
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
'40000000', //statement (core)(operator un-equal)
'f0030000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'24040000', //Offset (line number 265)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'98030000', //Offset (line number 230)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'5e010000', //SetEntityInvulnerable Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'58010000', //aidefinegoalhuntenemy Call
'01000000', //unknown
'1b000000', //unknown
'b0170000', //unknown
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
'e8040000', //Offset (line number 314)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'4c050000', //Offset (line number 339)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'04000000', //value 4
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'3c000000', //statement (init statement start offset)
'00010000', //Offset (line number 64)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
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
'e1020000', //RadarPositionClearEntity Call
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
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'58010000', //Offset
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
'4c060000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c8070000', //Offset (line number 498)

'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84000000', //GetDamage Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'49030000', //GetAnimationLength Call
'6e000000', //<---- convert int to string
'74000000', //writedebugflush Call

'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //LockEntity Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'50000000', //value 80
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'5c020000', //PlayAudioOneShotFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84000000', //GetDamage Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'49030000', //GetAnimationLength Call
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'f4050000', //Offset (line number 381)
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
'04000000', //Offset in byte
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
'7c080000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'400c0000', //Offset (line number 784)
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'b5010000', //AISetEntityIdleOverRide Call
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
'72010000', //AIMakeEntityDeaf Call
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
'71010000', //AIMakeEntityBlind Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8000000', //RemoveThisScript Call
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
'f8000000', //Offset in byte
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
'08010000', //killgametext Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2c010000', //value 300
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8000000', //RemoveThisScript Call
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
'f8000000', //Offset in byte
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
'08010000', //killgametext Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a5020000', //AIIsGoalNameInSubpack Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'280b0000', //Offset (line number 714)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'6c0a0000', //Offset (line number 667)
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'b5010000', //AISetEntityIdleOverRide Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'22000000', //value 34
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
'c0000000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'5e010000', //SetEntityInvulnerable Call
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
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'00000000', //value 0
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'b0170000', //unknown
'04000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c010000', //SetMoverIdlePosition Call
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'04000000', //value 4
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
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