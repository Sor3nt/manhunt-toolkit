<?php
namespace App\Tests\CompilerV2\LevelScripts\A01;

use App\MHT;
require_once 'LevelScriptA01Test.php';

class A16_TV_StudioTest extends LevelScriptA01Test
{

    public function test()
    {

        $levelscript = $this->testGetLevel();

        $script = "
scriptmain ButtonGateOpen;

entity
	buttonOpenGate_(S) : et_name;

const
	cBlinkDelay = 300;

var
	me : string[30];
	NotUsed : level_var boolean;
	
	
script OnCreate;
begin
	me := GetEntityName(this);

	WriteDebug(me, ' : OnCreate');

	SetSwitchState(this, 0);
	RunScript(me, 'BlinkRed');
	RunScript(me, 'FlashTheRoom');
	
	NotUsed := TRUE;
end;


script OnUseByPlayer;

VAR
	pos, pos2 : vec3d;
	Door : entityPtr;
begin

	if NotUsed then
	begin
		NotUsed := FALSE;
		
		{Open gate}
		RunScript(me, 'BlinkGreen');
		SetDoorState(GetEntity('asylum_cell_door_slide01_(SD)'),DOOR_CLOSED);
    UnLockEntity(GetEntity('asylum_cell_door_slide01_(SD)'));
    GraphModifyConnections(GetEntity('asylum_cell_door_slide01_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
    
    SetSlideDoorAjarDistance(GetEntity('asylum_cell_door_slide01_(SD)'), 1.1);
    SetDoorState(GetEntity('asylum_cell_door_slide01_(SD)'), DOOR_CLOSED);
    SetDoorState(GetEntity('asylum_cell_door_slide01_(SD)'), DOOR_OPENING);
		SetVector(pos, -25.2674, 6.97214, 36.8107);
		CreateSphereTrigger(pos, 1.79608, 'triggerSavePoint1');
		
		{RunScript('A01_Escape_Asylum', 'DisplayButtonTutorials');}
				
		AITriggerSoundKnownLocationNoRadar('LURE_HIGH', GetPlayer);
		
	{	SetVector(pos, -18.0927, 5.75534, 40.5269);
		SetVector(pos2, -17.3807, 6.8447, 45.7699);
		CreateBoxTrigger(pos, pos2, 'triggerLoom2a');

		SetVector(pos, -24.5466, 5.75534, 35.9646);
		SetVector(pos2, -19.3041, 6.8447, 36.6766);
		CreateBoxTrigger(pos, pos2, 'triggerLoom2b');
		SetEntityScriptsFromEntity('triggerLoom2a', 'triggerLoom2b');}
	end	
	else
	begin
		if (GetDoorState(GetEntity('asylum_cell_door_slide01_(SD)')) <> DOOR_CLOSED) AND (GetDoorState(GetEntity('asylum_cell_door_slide01_(SD)')) <> DOOR_CLOSING) then
		begin
			{close door}
			RunScript(me, 'BlinkRed');
			GraphModifyConnections(GetEntity('asylum_cell_door_slide01_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);
			SetDoorState(GetEntity('asylum_cell_door_slide01_(SD)'), DOOR_CLOSING);
		end
		else if (GetDoorState(GetEntity('asylum_cell_door_slide01_(SD)')) <> DOOR_OPEN) then
		begin
			{open door}
			RunScript(me, 'BlinkGreen');
			GraphModifyConnections(GetEntity('asylum_cell_door_slide01_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
			SetDoorState(GetEntity('asylum_cell_door_slide01_(SD)'), DOOR_OPENING);
		end;
	end;
end;


script BlinkRed;
begin
	WriteDebug(me, ' : BlinkRed');

	KillScript(me, 'BlinkGreen');

	while TRUE do
	begin
    	SetCurrentLOD(this, 1);
    	sleep(cBlinkDelay);
    	SetCurrentLOD(this, 0);
    	sleep(cBlinkDelay);
	end;
end;


script BlinkGreen;
begin
	WriteDebug(me, ' : BlinkGreen');


	KillScript(me, 'BlinkRed');
	
	NotUsed := FALSE;
	
	while TRUE do
	begin
    	SetCurrentLOD(this, 2);
    	sleep(cBlinkDelay);
    	SetCurrentLOD(this, 0);
    	sleep(cBlinkDelay);
	end;
end;

{script SkipEnd;
begin
	SetCurrentLod(GetEntity('Exit_Indicator'), 1);
end;}

script FlashTheRoom;
begin
	while TRUE do
	begin
		SetCurrentLOD(GetEntity('A01_NISConsole_(L)'), 1);
  	sleep(cBlinkDelay);
  	SetCurrentLOD(GetEntity('A01_NISConsole_(L)'), 2);
  	sleep(cBlinkDelay);
	end;
end;

end.
   

        ";

        $expected = [
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "86000000",
            "21000000",
            "04000000",
            "04000000",
            "d4000000",
            "12000000",
            "03000000",
            "1e000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "21000000",
            "04000000",
            "01000000",
            "04000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "95000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "09000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e4000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "20000000",
            "12000000",
            "02000000",
            "0d000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e4000000",
            "12000000",
            "01000000",
            "01000000",
            "1a000000",
            "01000000",
            "e4170000",
            "04000000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000",
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "34000000",
            "09000000",
            "1c000000",
            "1b000000",
            "e4170000",
            "04000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "74050000",
            "12000000",
            "01000000",
            "00000000",
            "1a000000",
            "01000000",
            "e4170000",
            "04000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "30000000",
            "12000000",
            "02000000",
            "0b000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e4000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "02000000",
            "10000000",
            "01000000",
            "97000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "99000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "03000000",
            "10000000",
            "01000000",
            "e9000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "cdcc8c3f",
            "10000000",
            "01000000",
            "9b010000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "02000000",
            "10000000",
            "01000000",
            "97000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "97000000",
            "22000000",
            "04000000",
            "01000000",
            "0c000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "a323ca41",
            "10000000",
            "01000000",
            "4f000000",
            "32000000",
            "09000000",
            "04000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "c51bdf40",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "283e1342",
            "10000000",
            "01000000",
            "84010000",
            "22000000",
            "04000000",
            "01000000",
            "0c000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "f3e5e53f",
            "10000000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "5c000000",
            "12000000",
            "02000000",
            "12000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "a3000000",
            "21000000",
            "04000000",
            "01000000",
            "70000000",
            "12000000",
            "02000000",
            "0a000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "8a000000",
            "10000000",
            "01000000",
            "b8020000",
            "3c000000",
            "44090000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "96000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "02000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "40000000",
            "f8050000",
            "33000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "96000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "03000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "40000000",
            "84060000",
            "33000000",
            "01000000",
            "01000000",
            "0f000000",
            "04000000",
            "25000000",
            "01000000",
            "04000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "b0070000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "7c000000",
            "12000000",
            "02000000",
            "09000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e4000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "e9000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "03000000",
            "10000000",
            "01000000",
            "97000000",
            "3c000000",
            "44090000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "96000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "40000000",
            "34080000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "44090000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "30000000",
            "12000000",
            "02000000",
            "0b000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e4000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "03000000",
            "10000000",
            "01000000",
            "e9000000",
            "21000000",
            "04000000",
            "01000000",
            "3c000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "97000000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000",
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "0b000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e5000000",
            "12000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "e40a0000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "2d010000",
            "12000000",
            "01000000",
            "2c010000",
            "10000000",
            "01000000",
            "6a000000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "2d010000",
            "12000000",
            "01000000",
            "2c010000",
            "10000000",
            "01000000",
            "6a000000",
            "3c000000",
            "340a0000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000",
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "21000000",
            "04000000",
            "01000000",
            "a4000000",
            "12000000",
            "02000000",
            "0e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "21000000",
            "04000000",
            "01000000",
            "d4000000",
            "12000000",
            "02000000",
            "1e000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "b4000000",
            "12000000",
            "02000000",
            "09000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e5000000",
            "12000000",
            "01000000",
            "00000000",
            "1a000000",
            "01000000",
            "e4170000",
            "04000000",
            "12000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "a00c0000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "02000000",
            "10000000",
            "01000000",
            "2d010000",
            "12000000",
            "01000000",
            "2c010000",
            "10000000",
            "01000000",
            "6a000000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "2d010000",
            "12000000",
            "01000000",
            "2c010000",
            "10000000",
            "01000000",
            "6a000000",
            "3c000000",
            "f00b0000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000",
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "12000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "c80d0000",
            "21000000",
            "04000000",
            "01000000",
            "c0000000",
            "12000000",
            "02000000",
            "13000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "2d010000",
            "12000000",
            "01000000",
            "2c010000",
            "10000000",
            "01000000",
            "6a000000",
            "21000000",
            "04000000",
            "01000000",
            "c0000000",
            "12000000",
            "02000000",
            "13000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "02000000",
            "10000000",
            "01000000",
            "2d010000",
            "12000000",
            "01000000",
            "2c010000",
            "10000000",
            "01000000",
            "6a000000",
            "3c000000",
            "d00c0000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000"
        ];

        $compiler = new \App\Service\CompilerV2\Compiler($script, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $compiler->levelScript = $levelscript;
        $compiler->debug = true;
        $compiled = $compiler->compile();

        if ($compiler->validateCode($expected) === false){

            foreach ($compiled['CODE'] as $index => $newCode) {

                if ($expected[$index] == $newCode['code']){
                    echo $index . " " . $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}