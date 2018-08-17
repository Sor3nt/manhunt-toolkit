<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewMeleeTut2Test extends KernelTestCase
{

    public function test()
    {

        $script = "
scriptmain NewMeleeTut2;

ENTITY
	triggerNewMeleeTutTwo : et_name;
	
VAR
		Invul: boolean;
	
script OnEnterTrigger;

VAR
	Door : entityPtr;
	pos : vec3d;
	CurrDam : integer;

begin

	{door opens, hunter jumps out}
	
		writedebug('enter MELEE');
		AIEntityCancelAnim('SobbingWoman(hunter)', 'BAT_INMATE_SMACK_HEAD_ANIM');

		writedebug('here1 MELEE');
		AISetEntityIdleOverRide('SobbingWoman(hunter)', FALSE, FALSE);
		SetEntityInvulnerable(GetEntity('SobbingWoman(hunter)'), FALSE);
		
		AIMakeEntityBlind('SobbingWoman(hunter)', 0);
		AIMakeEntityDeaf('SobbingWoman(hunter)', 0);
		
		sleep(200);
		AISetHunterOnRadar('SobbingWoman(hunter)', TRUE);
		AIAddHunterToLeaderSubpack('leader(leader)','subManWoman','SobbingWoman(hunter)');

		writedebug('here2 MELEE');
		ClearLevelGoal('GOAL2');
		PlayerDropBody;
		sleep(500);
					
		CutSceneStart;			
	
			AIMakeEntityBlind('SobbingWoman(hunter)', 1);
			AIMakeEntityDeaf('SobbingWoman(hunter)', 1);	
			ShowEntity(GetEntity('SobbingWoman(hunter)'));
	
			CutSceneRegisterSkipScript(this, 'SkipMe');
		
			CutsceneCameraInit;
			CutscenecameraSetPos(0.0, -9.59, 3.20, -13.241);
			CutscenecameraSetTarget(0.0, -9.59, 3.20, -14.241);
			CutsceneCameraSetFOV(0.0, 70.0);
			CutsceneCameraSetRoll(0.0, 0.0);
			CutSceneCameraSetHandyCam(false);
			CutscenecameraStart;

			SetVector(pos, -16.012, 5.947, 14.709);
			MoveEntity(GetPlayer, pos, 1);
			SetPedOrientation(GetPlayer, 0);
			
			SetVector(pos, -16.032, 5.947, 17.645);
			MoveEntity(GetEntity('SobbingWoman(hunter)'), pos, 1); 	
			SetPedOrientation(GetEntity('SobbingWoman(hunter)'), 180);
			
			PlayScriptAudioStreamAuto('WACKO3', 100);
			
			PlayerPlayFullBodyAnim('ASY_MELEE_INTRO_PLAYER');
			AiEntityPlayAnim('SobbingWoman(hunter)', 'ASY_MELEE_INTRO_INMATE');
		
			EntityPlayAnim(GetEntity('A01_cameratripod'), 'ASY_MELEE_INTRO_CAMERA', false);
			
			FrisbeeSpeechPlay('MLT1', 100,100);
			sleep(4000);
			SetStreamLipsyncSpeaker(GetPlayer,true);
			FrisbeeSpeechPlay('MLT2', 100,100);
			sleep(3500);
			FrisbeeSpeechPlay('MLT3', 100,100);
			sleep(4833);

			AIEntityCancelAnim('SobbingWoman(hunter)', 'ASY_MELEE_INTRO_INMATE');
			PlayerFullBodyAnimDone;
			
		CutSceneEnd(false);	

		DestroyEntity(GetEntity('A01_cameratripod'));	
		
		PlayerFullBodyAnimDone;
		AIEntityCancelAnim('SobbingWoman(hunter)', 'ASY_MELEE_INTRO_INMATE');
		SetEntityInvulnerable(GetEntity('SobbingWoman(hunter)'), FALSE);
		AISetEntityIdleOverRide('SobbingWoman(hunter)', TRUE, TRUE);
		AIEntityPlayAnimLooped('SobbingWoman(hunter)', 'BRO_FIXVENT_IDLE_3', 0.0);

		Runscript('triggerNewMeleeTutTwo', 'WaitToAttack');
		
		SetLevelGoal('GOAL8');
		sleep(1000);

		RunScript('triggerNewMeleeTutTwo', 'TurnMeInvulnerable');
		
		{NEW TUTS}
		if (IsEntityAlive('SobbingWoman(hunter)')) then
		begin
			DisplayGameText('MTG2');
			sleep(3500);
		end;

		if (IsEntityAlive('SobbingWoman(hunter)')) then
		begin		
			KillGameText;
			DisplayGameText('MTG3');
			sleep(3500);
		end;

		if (IsEntityAlive('SobbingWoman(hunter)')) then
		begin
			KillGameText;
			DisplayGameText('MTG4');
			sleep(3500);
		end;

		if (IsEntityAlive('SobbingWoman(hunter)')) then
		begin
			AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE1');
			AiEntityPlayAnimLooped('leo(hunter)', 'ASY_LEO_IDLE2', 0.0);
			KillGameText;
			DisplayGameText('MTG5');
			sleep(1500);
		end;

		while GetDamage(GetEntity('SobbingWoman(hunter)')) > 50 do sleep(10);

		if (IsEntityAlive('SobbingWoman(hunter)')) then
		begin
			FrisbeeSpeechPlay('MLT9', 100, 100);
			AIEntityCancelAnim('leo(hunter)', 'ASY_LEO_IDLE2');
			AiEntityPlayAnimLooped('leo(hunter)', 'ASY_LEO_IDLE3', 0.0);	
		end;
		
		{WAIT TILL HE IS ON THE FLOOR}
		while (IsEntityAlive('SobbingWoman(hunter)')) AND (NOT IsHunterKnockedDown('SobbingWoman(hunter)')) do sleep(10);

		if IsEntityAlive('SobbingWoman(hunter)') then
		begin
			FrisbeeSpeechPlay('ML11', 100,100);
			sleep(500);
			KillGameText;
			DisplayGameText('MTG8');
			sleep(3500);
			writedebug('got down');
		end;
	RemoveThisScript;

end;

script ShowRadarHelp;
begin
	while IsGameTextDisplaying do sleep(10);
	DisplayGameText('LFA1');
end;

script WaitToAttack;
VAR
	pos : vec3d;
begin
	SetVector(pos, 0.0, 0.5, 0.0);
	CreateSphereTrigger(pos, 0.7, 'triggerTooNear');
	
	AttachToEntity(GetEntity('triggerTooNear'), GetEntity('SobbingWoman(hunter)'));
	
	while (NOT InsideTrigger(GetEntity('triggerTooNear'), GetPlayer)) do sleep(10);
	
	if GetEntity('SobbingWoman(hunter)') <> NIL then
	begin
		AIEntityCancelAnim('SobbingWoman(hunter)', 'BRO_FIXVENT_IDLE_3');
		AISetEntityIdleOverRide('SobbingWoman(hunter)', FALSE, FALSE);
	end;
	
end;

script TurnMeInvulnerable;
begin
	Invul := TRUE;
	while Invul = TRUE AND IsEntityAlive('SobbingWoman(hunter)') do
	begin
		if GetDamage(GetPlayer) < 30 then
		begin
			SetEntityInvulnerable(GetPlayer, TRUE);
			Invul := FALSE;
		end;
	end;
end;

script SkipMe;
begin
	EndScriptAudioStream;
	AISetHunterOnRadar('SobbingWoman(hunter)', TRUE);
end;

end.

	
        ";

        $expected = [

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block
            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '14000000', //Offset in byte
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
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '1b000000', //value 27
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '17020000', //AIEntityCancelAnim Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '44000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0c000000', //value 12
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call
            '74000000', //writedebugflush Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'c8000000', //value 200
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '54000000', //Offset in byte
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
            '64000000', //Offset in byte
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '52010000', //AIAddHunterToLeaderSubpack Call


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '74000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0c000000', //value 12
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call
            '74000000', //writedebugflush Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '84000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '06000000', //value 6
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '42020000', //clearlevelgoal Call

            'b4020000', //PlayerDropBody Call

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'f4010000', //value 500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '48010000', //cutscenestart Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '71010000', //AIMakeEntityBlind Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '72010000', //AIMakeEntityDeaf Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '82000000', //ShowEntity Call
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '8c000000', //Offset in byte
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
            'a4701941', //value 1092186276
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
            'cdcc4c40', //value 1078774989
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '23db5341', //value 1096014627
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4f000000', //turn prev number into negative
            '32000000', //turn prev number into negative
            '09000000', //turn prev number into negative
            '04000000', //turn prev number into negative
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
            'a4701941', //value 1092186276
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
            'cdcc4c40', //value 1078774989
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '23db6341', //value 1097063203
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4f000000', //turn prev number into negative
            '32000000', //turn prev number into negative
            '09000000', //turn prev number into negative
            '04000000', //turn prev number into negative
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
            '00008c42', //value 1116471296
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
            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown

            '10000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '93188041', //value 1098913939
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
            'd34dbe40', //value 1086213587
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '10586b41', //value 1097553936
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84010000', //setvector Call
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
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

            '4d000000', //
            '10000000', //nested call return result
            '01000000', //nested call return result
            'b0020000', //SetPedOrientation Call


            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '89418041', //value 1098924425
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
            'd34dbe40', //value 1086213587
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'f6288d41', //value 1099770102
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84010000', //setvector Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '7d000000', //MoveEntity Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'b4000000', //value 180
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4d000000', //RadarCreateBlip Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            'b0020000', //SetPedOrientation Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '94000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '07000000', //value 7
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '64000000', //value 100
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a030000', //PlayScriptAudioStreamAuto Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '9c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '17000000', //value 23
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '94020000', //PlayerPlayFullBodyAnim Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '17000000', //value 23
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'b3010000', //AiEntityPlayAnim Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'cc000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
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
            'e0000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '17000000', //value 23
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
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'f8000000', //Offset in byte
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

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'a00f0000', //value 4000
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            'cf030000', //SetStreamLipsyncSpeaker Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00010000', //Offset in byte
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
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'ac0d0000', //value 3500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '08010000', //Offset in byte
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

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'e1120000', //value 4833
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '17000000', //value 23
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '17020000', //AIEntityCancelAnim Call
            '96020000', //PlayerFullBodyAnimDone Call
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '49010000', //cutsceneend Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'cc000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '11000000', //value 17
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            'a0020000', //DestroyEntity Call
            '96020000', //PlayerFullBodyAnimDone Call


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '17000000', //value 23
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '17020000', //AIEntityCancelAnim Call


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10010000', //Offset in byte
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
            'b4010000', //AIEntityPlayAnimLooped Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '24010000', //Offset in byte
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
            '3c010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0d000000', //value 13
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'e4000000', //runscript Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '4c010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '06000000', //value 6
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '41020000', //setlevelgoal Call

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'e8030000', //value 1000
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '24010000', //Offset in byte
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
            '54010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'e4000000', //runscript Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '8c0e0000', //Offset (line number 931)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '68010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '04010000', //displaygametext Call

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'ac0d0000', //value 3500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '1c0f0000', //Offset (line number 967)
            '08010000', //killgametext Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '70010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '04010000', //displaygametext Call

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'ac0d0000', //value 3500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call

            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'ac0f0000', //Offset (line number 1003)
            '08010000', //killgametext Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '78010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '04010000', //displaygametext Call

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'ac0d0000', //value 3500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '08110000', //Offset (line number 1090)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '80010000', //Offset in byte
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
            '90010000', //Offset in byte
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
            '80010000', //Offset in byte
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
            'a0010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
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
            '08010000', //killgametext Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b0010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '04010000', //displaygametext Call

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'dc050000', //value 1500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '32000000', //value 50
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '42000000', //statement (core)(operator greater)
            '8c110000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'c0110000', //Offset (line number 1136)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            '08110000', //Offset (line number 1090)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '28130000', //Offset (line number 1226)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b8010000', //Offset in byte
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
            '80010000', //Offset in byte
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
            'a0010000', //Offset in byte
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
            '80010000', //Offset in byte
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
            'c0010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
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
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'cb030000', //IsHunterKnockedDown Call
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
            'e4130000', //Offset (line number 1273)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            '28130000', //Offset (line number 1226)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'aa010000', //IsEntityAlive Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '18150000', //Offset (line number 1350)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'd0010000', //Offset in byte
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


            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'f4010000', //value 500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '08010000', //killgametext Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'd8010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '04010000', //displaygametext Call
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'ac0d0000', //value 3500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e0010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '09000000', //value 9
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebug Call
            '74000000', //writedebugflush Call
            'e8000000', //RemoveThisScript Call

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

            '07010000', //IsGameTextDisplaying Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '84150000', //Offset (line number 1377)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            '4c150000', //Offset (line number 1363)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'ec010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '04010000', //displaygametext Call
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
            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0000003f', //value 1056964608
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84010000', //setvector Call
            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '3333333f', //value 1060320051
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'f4010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'a3000000', //CreateSphereTrigger Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)


            'f4010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
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
            '04020000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '77000000', //getentity Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '93000000', //AttachToEntity Call

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'f4010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0f000000', //value 15
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
            'a0170000', //Offset (line number 1512)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            '18170000', //Offset (line number 1478)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '04020000', //Offset in byte

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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
            '18180000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'e0180000', //Offset (line number 1592)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '04020000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '1c020000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '17020000', //AIEntityCancelAnim Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '04020000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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


            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '01000000', //value 1
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)

            '60020000', //unknown
            '01000000', //unknown


//  while Invul = TRUE AND IsEntityAlive('SobbingWoman(hunter)') do

            #Invul
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '60020000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            #TRUE
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result


            #'SobbingWoman(hunter)'
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '30020000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result

            #IsEntityAlive
            'aa010000', //IsEntityAlive Call


            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '0f000000', //unknown
            '04000000', //unknown


            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)

            '3f000000', //statement (T_IS_EQUAL)

            'd0190000', //Offset (line number 1652)

            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)



            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '981a0000', //Offset (line number 1702)
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '1e000000', //value 30
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)
            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3d000000', //statement (core)(operator smaller)
            '3c1a0000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '901a0000', //Offset (line number 1700)
            '8a000000', //GetPlayer Call
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
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '60020000', //unknown
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            '2c190000', //Offset (line number 1611)
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
            'ce020000', //EndScriptAudioStream Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '48020000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
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