<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LiftLoon01Test extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true, true, 'The bytecode is not correct');
//return;
        $script = "

scriptmain LiftLoon01;

entity LiftLoonie01(hunter) : et_name;


var
    me : string[30];
    lButtonTutRemoved :  level_var boolean;


script Init;
VAR
	pos : vec3d;                
	animLength : integer;       
begin
    me := GetEntityName(this);

    WriteDebug(me, ' : Init');

	AIDefineGoalGotoNodeIdle('goalAmbush', me, AISCRIPT_HIGHPRIORITY, 'AMBUSHNODE', AISCRIPT_RUNMOVESPEED, TRUE);

	SetPedLockonable(GetEntity('LiftLoonie01(hunter)'), FALSE);

	AiMakeEntityDeaf(me, 0);
	AiMakeEntityBlind(me, 0);
	AIAddHunterToLeaderSubpack('leader2(leader)', 'subBeast1', me);

	while lButtonTutRemoved <> TRUE do sleep(10);
	
	pos := GetEntityPosition(GetEntity('real_asylum_elev'));
	while pos.y < 20.0 do
	begin
		pos := GetEntityPosition(GetEntity('real_asylum_elev'));
		sleep(10);
	end;
	
	SetMoverState(GetEntity('real_asylum_elev'), MOVER_STOPPED);
					
	SetVector(pos, -25.075, 24.09, 35.504);
	MoveEntity(GetEntity('LiftLoonie01(hunter)'), pos, 1);
	SetPedOrientation(GetEntity('LiftLoonie01(hunter)'), 55);
	
	SetVector(pos, -27.644, 24.09, 34.947);
	
	AiEntityPlayAnim('LiftLoonie01(hunter)', 'ASY_NURSE_ATTACK4A');
	EntityIgnoreCollisions(GetEntity('DeadLiftGuy(hunter)'),TRUE);
	EntityPlayAnim(GetEntity('DeadLiftGuy(hunter)'), 'ASY_NURSE_ATTACK4V', FALSE);
	
	sleep(1500);
	
	SetMoverState(GetEntity('real_asylum_elev'), MOVER_FORWARD);

	animLength := GetAnimationLength('ASY_NURSE_ATTACK4A');
	animLength := animLength - 1500;
	sleep(animLength);
	
	AIAddGoalForSubpack('leader2(leader)', 'subBeast1', 'goalAmbush'); 
	AISetIdleHomeNode(me, 'AMBUSHNODE');
	
	AISetHunterOnRadar(me, FALSE);
end;

script SkipMe;
begin
end;

script GreenWeaponTxt;
begin
	sleep(9000);
	DisplayGameText('SYR2');
end;

script OnDeath;
begin
	
	while IsExecutionInProgress do sleep(10);
	
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

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '10000000', //Offset in byte

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //getentityname Call
            '21000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            'b8000000', //Offset in byte
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
            'b8000000', //Offset in byte
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
            '18000000', //Offset in byte
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
            'b1010000', //AIDefineGoalGotoNodeIdle Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '24000000', //Offset in byte
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
            '97020000', //SetPedLockonable Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b8000000', //Offset in byte
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
            '72010000', //AIMakeEntityDeaf Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b8000000', //Offset in byte
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
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '3c000000', //statement (init statement start offset)
            '12000000', //Offset (line number 4.5)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0a000000', //value 10
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
            '1e000000', //value 30
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '52010000', //AIAddHunterToLeaderSubpack Call


//while lButtonTutRemoved <> TRUE do sleep(10);
            '1b000000', //unknown
            '64170000', //unknown
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
            '48030000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '7c030000', //Offset (line number 223)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            'f0020000', //Offset (line number 188)


            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '5c000000', //Offset in byte
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
            '78000000', //GetEntityPosition Call
            '12000000', //unknown
            '03000000', //unknown
            '0c000000', //unknown

            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown



            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown

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
            '0000a041', //value 1101004800
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3d000000', //unknown
            '7c040000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '24050000', //Offset (line number 329)

            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '5c000000', //Offset in byte
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
            '78000000', //GetEntityPosition Call



            '12000000', //unknown
            '03000000', //unknown
            '0c000000', //unknown

            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '44000000', //unknown


            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call


            '3c000000', //statement (init statement start offset)
            'f0030000', //Offset (line number 252)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '5c000000', //Offset in byte
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
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '00000000', //value 0
            '10000000', //nested call return result
            '01000000', //nested call return result
            '3a010000', //SetMoverState Call




            //	SetVector(pos, -25.075, 24.09, 35.504);

            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '9a99c841', //value 1103665562
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
            '52b8c041', //value 1103149138
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '19040e42', //value 1108214809
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84010000', //setvector Call



            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '24000000', //Offset in byte
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
            '0c000000', //unknown
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
            '24000000', //Offset in byte
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
            '37000000', //value 55
            '10000000', //nested call return result
            '01000000', //nested call return result

            '4d000000', //
            '10000000', //nested call return result
            '01000000', //nested call return result

            'b0020000', //SetPedOrientation

            '22000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'e926dd41', //value 1105012457
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
            '52b8c041', //value 1103149138
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            'bac90b42', //value 1108068794
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84010000', //setvector Call



            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '24000000', //Offset in byte
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
            '70000000', //Offset in byte
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
            '84000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
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
            'a2020000', //EntityIgnoreCollisions Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '84000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '14000000', //value 20
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
            '9c000000', //Offset in byte
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
            'dc050000', //value 1500
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '5c000000', //Offset in byte
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
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '3a010000', //SetMoverState Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '70000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '49030000', //GetAnimationLength Call

            '15000000', //unknown
            '04000000', //unknown
            '10000000', //offset
            '01000000', //

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '10000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            'dc050000', //value 1500
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '33000000', //sub
            '04000000', //sub
            '01000000', //sub

            '11000000', //offset


            '01000000', //unknown
            '04000000', //unknown
            '15000000', //unknown
            '04000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '10000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '3c000000', //statement (init statement start offset)
            '12000000', //Offset (line number 4.5)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '0c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0b000000', //value 11
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '56010000', //aiaddgoalforsubpack Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b8000000', //Offset in byte
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
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0b000000', //value 11
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b8000000', //Offset in byte
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
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '28230000', //value 9000
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b0000000', //Offset in byte
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
            '51020000', //IsExecutionInProgress Call
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '900b0000', //Offset (line number 740)
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '6a000000', //sleep Call
            '3c000000', //statement (init statement start offset)
            '580b0000', //Offset (line number 726)
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