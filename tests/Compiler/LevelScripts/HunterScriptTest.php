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


        $script = "

scriptmain HunterScript;

entity TruckGuard(hunter): et_name;

var
    gHunterName : string[32];
    spottedYard : level_var boolean;
    killedATruckman : level_var boolean;


script Init;
var
begin
	gHunterName := GetEntityName(this);

    WriteDebug(gHunterName, ' : Init');

	SetHunterMeleeTraits(this, MTT_HOOD_MEDIUM);

	AIAddEntity(gHunterName);

	AISetHunterIdleActionMinMaxRadius(gHunterName, AISCRIPT_IDLE_WANDERSEARCH, AISCRIPT_HIGHPRIORITY, 120, 180, 20.0);

	AIAddHunterToLeaderSubpack('leader(leader)', 'subTruckGuards', gHunterName);

	SetHunterHideHealth(gHunterName, 0);
	
	SetPedDoNotDecay(this, TRUE);
	
	spottedYard := FALSE;
end;


script OnDeath;
var
	pos : vec3d;
	NrHuntersInSubpack : integer; { 4 }
	HunterIndex : integer; { 8 }
	Success : boolean; { 12 }
	HunterName : string[30];                { 32 == 56}

begin
	
	while IsExecutionInProgress do sleep(10);
	
	{ spawn health for player }
    GetDropPosForPlayerPickups(this, pos);
	SpawnMovingEntity('G_First_Aid_(CT)', pos, 'SpawnedPainkiller01');

    NrHuntersInSubpack := 0;
	for HunterIndex := 1 to AINumberInSubpack('leader(leader)', 'subTruckGuards') do
	begin
		Success := AIReturnSubpackEntityName('leader(leader)', 'subTruckGuards', HunterIndex, HunterName);
		if Success = TRUE then
		begin
			if IsEntityAlive(HunterName) then NrHuntersInSubpack := NrHuntersInSubpack + 1;			
		end;
	end;

	if NrHuntersInSubpack = 0 then
	begin
		{ClearLevelGoal('GOAL13');}

        sleep(200);

		{FrisbeeSpeechPlay('LEAVE', 75, 25);}
	end;
	
	if killedATruckman = FALSE then
	begin
		killedATruckman := TRUE;
		while NOT IsFrisbeeSpeechCompleted do sleep(10);
		FrisbeeSpeechPlay('LEO20', 127, 127);
		while NOT IsFrisbeeSpeechCompleted do sleep(10);
		FrisbeeSpeechPlay('BOD1', 127, 127);
		DisplayGameText('PUB1');
		sleep(3000);
		DisplayGameText('PUB2');		
	end;
	
	if InsideTrigger(GetEntity('triggerEnteringTruck'), GetPlayer) then
		RunScript('triggerEnteringTruck', 'OnEnterTrigger');
	
end;

script OnLowSightingOrAbove;
begin
	if spottedYard = FALSE then
	begin
		{PlayDirectorSpeechPlaceholder('LSE2_A');}
		FrisbeeSpeechPlay('LSE2', 75, 25);
		spottedYard := TRUE;
	end;
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
'c8000000', //Offset in byte
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
'c8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49000000', //value 73
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'77020000', //SetHunterMeleeTraits Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8000000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'78000000', //value 120
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b4000000', //value 180
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000a041', //value 1101004800
'10000000', //nested call return result
'01000000', //nested call return result
'a4010000', //AISetHunterIdleActionMinMaxRadius Call
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
'c8000000', //Offset in byte
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
'c8000000', //Offset in byte
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
'ee010000', //SetHunterHideHealth Call
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
'6b020000', //SetPedDoNotDecay Call
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'00000000', //value 0
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'ac170000', //unknown
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
'38000000', //Offset in byte
'51020000', //IsExecutionInProgress Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'38030000', //Offset (line number 206)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'00030000', //Offset (line number 192)
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
'96030000', //GetDropPosForPlayerPickups Call
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
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'7a000000', //SpawnMovingEntity Call
'12000000', //unknown
'01000000', //unknown
'00000000', //unknown
'15000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'15000000', //unknown
'04000000', //unknown
'14000000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58000000', //Offset in byte
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
'68000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'67010000', //unknown
'13000000', //unknown
'02000000', //unknown
'04000000', //unknown
'14000000', //unknown
'23000000', //unknown
'01000000', //unknown
'02000000', //unknown
'41000000', //unknown
'9c040000', //unknown
'3c000000', //statement (init statement start offset)
'58060000', //Offset (line number 406)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58000000', //Offset in byte
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
'68000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'14000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'36000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'ec010000', //aireturnsubpackentityname call
'15000000', //unknown
'04000000', //unknown
'18000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'18000000', //Offset
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
'a4050000', //Offset (line number 361)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'44060000', //Offset (line number 401)
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'36000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'aa010000', //IsEntityAlive Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'44060000', //Offset (line number 401)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'10000000', //Offset
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
'15000000', //unknown
'04000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'2f000000', //unknown
'04000000', //unknown
'10000000', //unknown
'3c000000', //statement (init statement start offset)
'14040000', //Offset (line number 261)
'30000000', //unknown
'04000000', //unknown
'10000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'10000000', //Offset
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
'bc060000', //Offset (line number 431)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e8060000', //Offset (line number 442)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c8000000', //value 200
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'1b000000', //unknown
'e0170000', //unknown
'04000000', //unknown
'01000000', //unknown
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
'40070000', //Offset (line number 464)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'20090000', //Offset (line number 584)
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'e0170000', //unknown
'04000000', //unknown
'b0030000', //unknown
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b4070000', //Offset (line number 493)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'70070000', //Offset (line number 476)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78000000', //GetEntityPosition Call
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
'b0030000', //unknown
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50080000', //Offset (line number 532)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'0c080000', //Offset (line number 515)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80000000', //KillEntity Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88000000', //Offset in byte
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
'b80b0000', //value 3000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //LockEntity Call
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
'8a000000', //GetPlayer Call
'10000000', //nested call return result
'01000000', //nested call return result
'a5000000', //InsideTrigger Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd8090000', //Offset (line number 630)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //LockEntity Call
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
'b0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
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
'1b000000', //unknown
'ac170000', //unknown
'04000000', //unknown
'01000000', //unknown
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
'600a0000', //Offset (line number 664)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e80a0000', //Offset (line number 698)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4b000000', //value 75
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'12000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'01000000', //value 1
'1a000000', //parameter (access level_var)
'01000000', //parameter (access level_var)
'ac170000', //unknown
'04000000', //unknown
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