<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HunterScriptTest extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true, true, 'The bytecode is not correct');
//return;
        $script = "

scriptmain hunterScript;
entity h21(hunter) : et_name;
var	self : string[32];
	MonitorDead : level_var boolean;
	DannySeen : level_var boolean;

script OnCreate;
	begin
		self := GetEntityName(this);
		
		if not(self = 'hJumpAttack(hunter)') then begin
			SetHunterMute(this, true);
			AISetHunterOnRadar(self, true);
			AIAddHunterToLeaderSubpack('leader(leader)', 'subHuntMelee1', self);
    
            if(self = 'h23(hunter)') then begin
                AIMakeEntityBlind(self, 0);
                AIMakeEntityDeaf(self, 0);
                AIRemoveHunterFromLeaderSubpack('leader(leader)', self);
                AIAddHunterToLeaderSubpack('leader(leader)', 'subHuntMelee', self);
            end;
            
        end;
	end;

script OnDeath;
	var pos : vec3d;
	begin
		if(self = 'hJumpAttack(hunter)') then MonitorDead := true;
		if((MonitorDead) and (not IsEntityAlive('h21(hunter)')) and (not IsEntityAlive('h22(hunter)')) and
			(not IsEntityAlive('h23(hunter)')) and (not IsEntityAlive('hJumpAttack(hunter)'))) then begin
			
			if not (InsideTrigger(getentity('tInHouseCheck'), getplayer)) then
			begin
				CutsceneCameraInit;
				CutscenecameraSetPos(0.0, -60.27, -1.76, 49.35);
				CutscenecameraSetPos(6.0, -55.26, -1.72, 49.87);
				CutscenecameraSetTarget(0.0, -56.86, 0.89, 41.92);
				CutsceneCameraSetFOV(0.0, 90.0);
				{CutsceneCameraSetRoll(0.0, 15.0); CHANGING THIS TO ZERO SO WE DON'T HAVE ANY CAMERA CLIPPNG ISSUES}
				CutsceneCameraSetRoll(0.0, 0.0);
				CutSceneCameraSetHandyCam(true);
				CutSceneStart;
				CutscenecameraStart;
				SetPlayerControllable(true);	
				SetVector(pos, -56.91, -1.37, 47.081);
				MoveEntity(GetPlayer, pos, 0);
				SetPedOrientation(GetPlayer, 0);	
				SetVector(pos, -57.7901, -0.498579, 34.411);
				SetPlayerGoToNode('n1st01', pos, true);
				sleep(6000);
				SetLevelCompleted;
				CutsceneEnd(false);
				end
			Else
				Begin	
				Sleep(2000);
				SetVector(pos, -62.9767, -0.497826, 32.093);
				CreateSphereTrigger(pos, 1.0, 'tEndBlip');
				RadarPositionSetEntity(GetEntity('tEndBlip'), MAP_COLOR_LOCATION);
				SetEntityScriptsFromEntity('genTrigger', 'tEndBlip');
				DisplayGameText('GOD_61');
				ClearLevelGoal('GOAL_7');
				SetLevelGoal('GOAL_61');
				end;
		end;
		EndScriptAudioStream;
		KillScript(self, 'PickWanderNode_Base');
		KillScript(self, 'PickWanderNode_1st');
		KillScript(self, 'PickWanderNode_2nd');
		{KillScript(self, 'RandomSpeech');}
		pos := GetEntityPosition(this);
		pos.y := pos.y + 0.4;
		if(GetDamage(GetPlayer) < 45) then SpawnMovingEntity('G_First_Aid_(CT)', pos, 'ScriptCreateName');
	end;



script PickWanderNode_Base;
	begin
		
		while(true) do begin
			{WriteDebug(self, ' idle wander');}
			if(not IsEntityAlive(self)) then killthisscript;
			if(GetEntity(self) <> nil) then begin
				if(GetDamage(GetEntity(self)) > 0) then begin
					case randnum(19) of
						 0: AISetIdleHomeNode(self, 'nBase01');
						 1: AISetIdleHomeNode(self, 'nBase02');
						 2: AISetIdleHomeNode(self, 'nBase03');
						 3: AISetIdleHomeNode(self, 'nBase04');
						 4: AISetIdleHomeNode(self, 'nBase05');
						 5: AISetIdleHomeNode(self, 'nBase06');
						 6: AISetIdleHomeNode(self, 'nBase07');
						 7: AISetIdleHomeNode(self, 'nBase08');
						 8: AISetIdleHomeNode(self, 'nBase09');
						 9: AISetIdleHomeNode(self, 'nBase10');
						10: AISetIdleHomeNode(self, 'nBase11');
						11: AISetIdleHomeNode(self, 'nBase12');
						12: AISetIdleHomeNode(self, 'nBase13');
						13: AISetIdleHomeNode(self, 'nBase14');
						14: AISetIdleHomeNode(self, 'nBase15');
						15: AISetIdleHomeNode(self, 'nBase16');
						16: AISetIdleHomeNode(self, 'nBase17');
						17: AISetIdleHomeNode(self, 'nBase18');
						18: AISetIdleHomeNode(self, 'nBase19');
					end;
					AIEntityGoHomeIfIdle(self);
					Sleep(7000 + randnum(7000));
				end;
			end;
		end;
	end;

script PickWanderNode_1st;
	begin
		while(true) do begin
			{WriteDebug(self, ' idle wander');}
			if(not IsEntityAlive(self)) then killthisscript;
			if(GetEntity(self) <> nil) then begin
				if(GetDamage(GetEntity(self)) > 0) then begin
					case randnum(19) of
						 0: AISetIdleHomeNode(self, 'n1st01');
						 1: AISetIdleHomeNode(self, 'n1st02');
						 2: AISetIdleHomeNode(self, 'n1st03');
						 3: AISetIdleHomeNode(self, 'n1st04');
						 4: AISetIdleHomeNode(self, 'n1st05');
						 5: AISetIdleHomeNode(self, 'n1st06');
						 6: AISetIdleHomeNode(self, 'n1st07');
						 7: AISetIdleHomeNode(self, 'n1st08');
						 8: AISetIdleHomeNode(self, 'n1st09');
						 9: AISetIdleHomeNode(self, 'n1st10');
						10: AISetIdleHomeNode(self, 'n1st11');
						11: AISetIdleHomeNode(self, 'n1st12');
						12: AISetIdleHomeNode(self, 'n1st13');
						13: AISetIdleHomeNode(self, 'n1st14');
						14: AISetIdleHomeNode(self, 'n1st15');
						15: AISetIdleHomeNode(self, 'n1st16');
						16: AISetIdleHomeNode(self, 'n1st17');
						17: AISetIdleHomeNode(self, 'n1st18');
						18: AISetIdleHomeNode(self, 'n1st19');
					end;
					AIEntityGoHomeIfIdle(self);
					Sleep(7000 + randnum(7000));
				end;
			end;
		end;
	end;

script PickWanderNode_2nd;
	begin
		while(true) do begin
			{WriteDebug(self, ' idle wander');}
			if(not IsEntityAlive(self)) then killthisscript;
			if(GetEntity(self) <> nil) then begin
				if(GetDamage(GetEntity(self)) > 0) then begin
					case randnum(19) of
						 0: AISetIdleHomeNode(self, 'n2nd01');
						 1: AISetIdleHomeNode(self, 'n2nd02');
						 2: AISetIdleHomeNode(self, 'n2nd03');
						 3: AISetIdleHomeNode(self, 'n2nd04');
						 4: AISetIdleHomeNode(self, 'n2nd05');
						 5: AISetIdleHomeNode(self, 'n2nd06');
						 6: AISetIdleHomeNode(self, 'n2nd07');
						 7: AISetIdleHomeNode(self, 'n2nd08');
						 8: AISetIdleHomeNode(self, 'n2nd09');
						 9: AISetIdleHomeNode(self, 'n2nd10');
						10: AISetIdleHomeNode(self, 'n2nd11');
						11: AISetIdleHomeNode(self, 'n2nd12');
						12: AISetIdleHomeNode(self, 'n2nd13');
						13: AISetIdleHomeNode(self, 'n2nd14');
						14: AISetIdleHomeNode(self, 'n2nd15');
						15: AISetIdleHomeNode(self, 'n2nd16');
						16: AISetIdleHomeNode(self, 'n2nd17');
						17: AISetIdleHomeNode(self, 'n2nd18');
						18: AISetIdleHomeNode(self, 'n2nd19');
					end;
					AIEntityGoHomeIfIdle(self);
					Sleep(10000 + randnum(5000));
				end;
			end;
		end;
	end;

script OnHighSightingOrAbove;
begin
	writedebug('PLAYER SPOTTED');
	DannySeen := TRUE;
	removethisscript;
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
'78030000', //Offset in byte
'12000000', //parameter (read string array? assign?)
'03000000', //parameter (read string array? assign?)
'20000000', //value 32
'10000000', //parameter (read string array? assign?)
'04000000', //parameter (read string array? assign?)
'10000000', //unknown
'03000000', //unknown
'48000000', //unknown

'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'49000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'd8000000', //Offset (line number 54)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec030000', //Offset (line number 251)
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
'76030000', //SetHunterMute Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a8010000', //aisethunteronradar Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18000000', //Offset in byte
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
'28000000', //Offset in byte
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
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'49000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'6c020000', //Offset (line number 155)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec030000', //Offset (line number 251)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'18000000', //Offset in byte
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
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'53010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18000000', //Offset in byte
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
'48000000', //Offset in byte
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
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'49000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'a4040000', //Offset (line number 297)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd4040000', //Offset (line number 309)
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'e4080000', //unknown
'04000000', //unknown
'1b000000', //unknown
'e4080000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'0f000000', //unknown
'04000000', //unknown
'25000000', //statement (AND operator)
'01000000', //statement (AND operator)
'04000000', //statement (AND operator)
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80000000', //KillEntity Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'0f000000', //unknown
'04000000', //unknown
'25000000', //statement (AND operator)
'01000000', //statement (AND operator)
'04000000', //statement (AND operator)
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'0f000000', //unknown
'04000000', //unknown
'25000000', //statement (AND operator)
'01000000', //statement (AND operator)
'04000000', //statement (AND operator)
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'0f000000', //unknown
'04000000', //unknown
'25000000', //statement (AND operator)
'01000000', //statement (AND operator)
'04000000', //statement (AND operator)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'140d0000', //Offset (line number 837)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0000000', //Offset in byte
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
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'a5000000', //InsideTrigger Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'dc0a0000', //Offset (line number 695)
'5f030000', //CutSceneCameraInit Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b147142', //value 1114707067
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
'ae47e13f', //value 1071728558
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
'66664542', //value 1111844454
'10000000', //nested call return result
'01000000', //nested call return result
'5a030000', //CutSceneCameraSetPos Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c040', //value 1086324736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3d0a5d42', //value 1113393725
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
'f628dc3f', //value 1071393014
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
'e17a4742', //value 1111980769
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
'a4706342', //value 1113813156
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
'0ad7633f', //value 1063507722
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'14ae2742', //value 1109896724
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
'0000b442', //value 1119092736
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'6d030000', //CutSceneCameraSetHandyCam Call
'48010000', //cutscenestart Call
'5e030000', //CutSceneCameraStart Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'91020000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd7a36342', //value 1113826263
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
'295caf3f', //value 1068457001
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
'f2523c42', //value 1111249650
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
'00000000', //value 0
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
'10296742', //value 1114056976
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
'bf45ff3e', //value 1056916927
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
'dda40942', //value 1107928285
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
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
'93020000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'70170000', //value 6000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'04020000', //SetLevelCompleted Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'49010000', //cutsceneend Call
'3c000000', //statement (init statement start offset)
'140d0000', //Offset (line number 837)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd0070000', //value 2000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'24e87b42', //value 1115416612
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
'0de3fe3e', //value 1056891661
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
'3b5f0042', //value 1107320635
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //clearlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'ce020000', //EndScriptAudioStream Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e5000000', //killscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
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
'e5000000', //killscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e5000000', //killscript Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'78000000', //GetEntityPosition Call
'12000000', //assign (to script var)
'03000000', //assign (to script var)
'0c000000', //value
'0f000000', //assign (to script var)
'01000000', //assign (to script var)
'0f000000', //unknown
'04000000', //unknown
'44000000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'0f000000', //unknown
'01000000', //unknown
'32000000', //unknown
'01000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'0f000000', //unknown
'01000000', //unknown
'32000000', //unknown
'01000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcccc3e', //value 1053609165
'10000000', //nested call return result
'01000000', //nested call return result
'50000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'17000000', //unknown
'04000000', //unknown
'02000000', //unknown
'01000000', //unknown
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'84000000', //GetDamage Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'2d000000', //value 45
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3d000000', //statement (core)(operator smaller)
'8c0f0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'14100000', //Offset (line number 1029)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40010000', //SetMoverSpeed Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'7a000000', //SpawnMovingEntity Call
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
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'681b0000', //Offset (line number 1754)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b8100000', //Offset (line number 1070)
'e7000000', //KillThisScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'30110000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'601b0000', //Offset (line number 1752)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'84000000', //GetDamage Call
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
'c8110000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'601b0000', //Offset (line number 1752)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'69000000', //randnum Call
'24000000', //unknown
'01000000', //unknown
'12000000', //unknown
'3f000000', //statement (init start offset)
'78130000', //Offset (line number 1246)
'24000000', //unknown
'01000000', //unknown
'11000000', //unknown
'3f000000', //statement (init start offset)
'dc130000', //Offset (line number 1271)
'24000000', //unknown
'01000000', //unknown
'10000000', //unknown
'3f000000', //statement (init start offset)
'40140000', //Offset (line number 1296)
'24000000', //unknown
'01000000', //unknown
'0f000000', //unknown
'3f000000', //statement (init start offset)
'a4140000', //Offset (line number 1321)
'24000000', //unknown
'01000000', //unknown
'0e000000', //unknown
'3f000000', //statement (init start offset)
'08150000', //Offset (line number 1346)
'24000000', //unknown
'01000000', //unknown
'0d000000', //unknown
'3f000000', //statement (init start offset)
'6c150000', //Offset (line number 1371)
'24000000', //unknown
'01000000', //unknown
'0c000000', //unknown
'3f000000', //statement (init start offset)
'd0150000', //Offset (line number 1396)
'24000000', //unknown
'01000000', //unknown
'0b000000', //unknown
'3f000000', //statement (init start offset)
'34160000', //Offset (line number 1421)
'24000000', //unknown
'01000000', //unknown
'0a000000', //unknown
'3f000000', //statement (init start offset)
'98160000', //Offset (line number 1446)
'24000000', //unknown
'01000000', //unknown
'09000000', //unknown
'3f000000', //statement (init start offset)
'fc160000', //Offset (line number 1471)
'24000000', //unknown
'01000000', //unknown
'08000000', //unknown
'3f000000', //statement (init start offset)
'60170000', //Offset (line number 1496)
'24000000', //unknown
'01000000', //unknown
'07000000', //unknown
'3f000000', //statement (init start offset)
'c4170000', //Offset (line number 1521)
'24000000', //unknown
'01000000', //unknown
'06000000', //unknown
'3f000000', //statement (init start offset)
'28180000', //Offset (line number 1546)
'24000000', //unknown
'01000000', //unknown
'05000000', //unknown
'3f000000', //statement (init start offset)
'8c180000', //Offset (line number 1571)
'24000000', //unknown
'01000000', //unknown
'04000000', //unknown
'3f000000', //statement (init start offset)
'f0180000', //Offset (line number 1596)
'24000000', //unknown
'01000000', //unknown
'03000000', //unknown
'3f000000', //statement (init start offset)
'54190000', //Offset (line number 1621)
'24000000', //unknown
'01000000', //unknown
'02000000', //unknown
'3f000000', //statement (init start offset)
'b8190000', //Offset (line number 1646)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'1c1a0000', //Offset (line number 1671)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'801a0000', //Offset (line number 1696)
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8010000', //aisethunteronradar Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84010000', //setvector Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'e41a0000', //Offset (line number 1721)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'18020000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'581b0000', //value 7000
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'581b0000', //value 7000
'10000000', //nested call return result
'01000000', //nested call return result
'69000000', //randnum Call
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'01000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'44100000', //Offset (line number 1041)
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
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'bc260000', //Offset (line number 2479)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'0c1c0000', //Offset (line number 1795)
'e7000000', //KillThisScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'841c0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b4260000', //Offset (line number 2477)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'84000000', //GetDamage Call
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
'1c1d0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b4260000', //Offset (line number 2477)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'69000000', //randnum Call
'24000000', //unknown
'01000000', //unknown
'12000000', //unknown
'3f000000', //statement (init start offset)
'cc1e0000', //Offset (line number 1971)
'24000000', //unknown
'01000000', //unknown
'11000000', //unknown
'3f000000', //statement (init start offset)
'301f0000', //Offset (line number 1996)
'24000000', //unknown
'01000000', //unknown
'10000000', //unknown
'3f000000', //statement (init start offset)
'941f0000', //Offset (line number 2021)
'24000000', //unknown
'01000000', //unknown
'0f000000', //unknown
'3f000000', //statement (init start offset)
'f81f0000', //Offset (line number 2046)
'24000000', //unknown
'01000000', //unknown
'0e000000', //unknown
'3f000000', //statement (init start offset)
'5c200000', //Offset (line number 2071)
'24000000', //unknown
'01000000', //unknown
'0d000000', //unknown
'3f000000', //statement (init start offset)
'c0200000', //Offset (line number 2096)
'24000000', //unknown
'01000000', //unknown
'0c000000', //unknown
'3f000000', //statement (init start offset)
'24210000', //Offset (line number 2121)
'24000000', //unknown
'01000000', //unknown
'0b000000', //unknown
'3f000000', //statement (init start offset)
'88210000', //Offset (line number 2146)
'24000000', //unknown
'01000000', //unknown
'0a000000', //unknown
'3f000000', //statement (init start offset)
'ec210000', //Offset (line number 2171)
'24000000', //unknown
'01000000', //unknown
'09000000', //unknown
'3f000000', //statement (init start offset)
'50220000', //Offset (line number 2196)
'24000000', //unknown
'01000000', //unknown
'08000000', //unknown
'3f000000', //statement (init start offset)
'b4220000', //Offset (line number 2221)
'24000000', //unknown
'01000000', //unknown
'07000000', //unknown
'3f000000', //statement (init start offset)
'18230000', //Offset (line number 2246)
'24000000', //unknown
'01000000', //unknown
'06000000', //unknown
'3f000000', //statement (init start offset)
'7c230000', //Offset (line number 2271)
'24000000', //unknown
'01000000', //unknown
'05000000', //unknown
'3f000000', //statement (init start offset)
'e0230000', //Offset (line number 2296)
'24000000', //unknown
'01000000', //unknown
'04000000', //unknown
'3f000000', //statement (init start offset)
'44240000', //Offset (line number 2321)
'24000000', //unknown
'01000000', //unknown
'03000000', //unknown
'3f000000', //statement (init start offset)
'a8240000', //Offset (line number 2346)
'24000000', //unknown
'01000000', //unknown
'02000000', //unknown
'3f000000', //statement (init start offset)
'0c250000', //Offset (line number 2371)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'70250000', //Offset (line number 2396)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd4250000', //Offset (line number 2421)
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8020000', //AITriggerSoundKnownLocationNoRadar Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0020000', //SetPedOrientation Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8020000', //setmaxnumberofrats Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0020000', //DestroyEntity Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'38260000', //Offset (line number 2446)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'18020000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'581b0000', //value 7000
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'581b0000', //value 7000
'10000000', //nested call return result
'01000000', //nested call return result
'69000000', //randnum Call
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'01000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'981b0000', //Offset (line number 1766)
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
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'10320000', //Offset (line number 3204)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'60270000', //Offset (line number 2520)
'e7000000', //KillThisScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'd8270000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'08320000', //Offset (line number 3202)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'84000000', //GetDamage Call
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
'70280000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'08320000', //Offset (line number 3202)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'69000000', //randnum Call
'24000000', //unknown
'01000000', //unknown
'12000000', //unknown
'3f000000', //statement (init start offset)
'202a0000', //Offset (line number 2696)
'24000000', //unknown
'01000000', //unknown
'11000000', //unknown
'3f000000', //statement (init start offset)
'842a0000', //Offset (line number 2721)
'24000000', //unknown
'01000000', //unknown
'10000000', //unknown
'3f000000', //statement (init start offset)
'e82a0000', //Offset (line number 2746)
'24000000', //unknown
'01000000', //unknown
'0f000000', //unknown
'3f000000', //statement (init start offset)
'4c2b0000', //Offset (line number 2771)
'24000000', //unknown
'01000000', //unknown
'0e000000', //unknown
'3f000000', //statement (init start offset)
'b02b0000', //Offset (line number 2796)
'24000000', //unknown
'01000000', //unknown
'0d000000', //unknown
'3f000000', //statement (init start offset)
'142c0000', //Offset (line number 2821)
'24000000', //unknown
'01000000', //unknown
'0c000000', //unknown
'3f000000', //statement (init start offset)
'782c0000', //Offset (line number 2846)
'24000000', //unknown
'01000000', //unknown
'0b000000', //unknown
'3f000000', //statement (init start offset)
'dc2c0000', //Offset (line number 2871)
'24000000', //unknown
'01000000', //unknown
'0a000000', //unknown
'3f000000', //statement (init start offset)
'402d0000', //Offset (line number 2896)
'24000000', //unknown
'01000000', //unknown
'09000000', //unknown
'3f000000', //statement (init start offset)
'a42d0000', //Offset (line number 2921)
'24000000', //unknown
'01000000', //unknown
'08000000', //unknown
'3f000000', //statement (init start offset)
'082e0000', //Offset (line number 2946)
'24000000', //unknown
'01000000', //unknown
'07000000', //unknown
'3f000000', //statement (init start offset)
'6c2e0000', //Offset (line number 2971)
'24000000', //unknown
'01000000', //unknown
'06000000', //unknown
'3f000000', //statement (init start offset)
'd02e0000', //Offset (line number 2996)
'24000000', //unknown
'01000000', //unknown
'05000000', //unknown
'3f000000', //statement (init start offset)
'342f0000', //Offset (line number 3021)
'24000000', //unknown
'01000000', //unknown
'04000000', //unknown
'3f000000', //statement (init start offset)
'982f0000', //Offset (line number 3046)
'24000000', //unknown
'01000000', //unknown
'03000000', //unknown
'3f000000', //statement (init start offset)
'fc2f0000', //Offset (line number 3071)
'24000000', //unknown
'01000000', //unknown
'02000000', //unknown
'3f000000', //statement (init start offset)
'60300000', //Offset (line number 3096)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'c4300000', //Offset (line number 3121)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'28310000', //Offset (line number 3146)
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'28030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20030000', //cutsceneregisterskipscript Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00030000', //ClearAllLevelGoals Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0020000', //RadarPositionSetEntity Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'3c000000', //statement (init statement start offset)
'8c310000', //Offset (line number 3171)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'18020000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'10270000', //value 10000
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'88130000', //value 5000
'10000000', //nested call return result
'01000000', //nested call return result
'69000000', //randnum Call
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'01000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'ec260000', //Offset (line number 2491)
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'50090000', //unknown
'04000000', //unknown
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