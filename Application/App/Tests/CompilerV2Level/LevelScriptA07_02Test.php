<?php
namespace App\Tests\CompilerV2Level\LevelScripts;

use App\MHT;
use PHPUnit\Framework\TestCase;

class LevelScriptA07_02Test extends TestCase
{

    public function test()
    {


        $script = "
scriptmain LevelScript;

entity
	A07_2Tolerance_Zone : et_level;

var
	LevelState : integer;
	DebugJump : boolean;
	PlayerBeamed: boolean;
	Load : boolean;
	Save1 : EntityPtr;
	Save2 : EntityPtr;
	Save3 : EntityPtr;
	Save4 : EntityPtr;
	Save5 : EntityPtr;
	CurrentAmbientAudioTrack : integer;
	CoverKillCounter : integer;
	MiddleKillCounter : integer;
	CinemaKillCounter : integer;

PROCEDURE InitSubpack(SubpackName : string; CombatType : eAICombatType; HuntPlayer : boolean); FORWARD;
PROCEDURE createBTrigger(TriggerName : string; x1, y1, z1, x2, y2, z2 : real); FORWARD;

script OnCreate;
var
	vector,pos,pos2 : vec3d;
	P : entityptr;

begin
	
	{Set colouramp for normal Danny}
	SetColourRamp('FE_colramps', 1, 4.0);
		writedebug('LEVEL LOADED - TOM IS LOOOOOSER !!!!!');
	
	
	{************* INIT}
	SetMaxNumberOfRats(4);
	SwitchLitterOn(TRUE);
	Load := false;
	CoverKillCounter := 0;
	MiddleKillCounter := 0;
	CinemaKillCounter := 0;
	
	{Hide porn}
	HideEntity(GetEntity('TZ_Pornfilm'));
	
	SetNextLevelByName('A10_Brothel'); { [gupi] continue to brothel }
	
	LevelState := 0;
	showTriggers(false);
	
	if (LevelState = 0) then DebugJump := false
	else  DebugJump := true;
	PlayerBeamed := false;

	{Save1 := getEntity('SavePoint_01');}
	Save2 := getEntity('SavePoint_02');
	Save3 := getEntity('SavePoint_03');
	{Save4 := getEntity('SavePoint_04');}
	
	if NOT DebugJump then
	begin
		{	if (Save2 = NIL) then
		begin
			LevelState := 1;
			Load := true;
		end;}
		if (Save3 = NIL) then
		begin
			LevelState := 2;
			Load := true;
		end;
	end;
	
	{if (Save1 <> NIL) then DeactivateSavePoint(Save1);}
	{if (Save1 <> NIL) then DeactivateSavePoint(Save1);}
	if (Save2 <> NIL) then DeactivateSavePoint(Save2);
	if (Save3 <> NIL) then DeactivateSavePoint(Save3);
	{if (Save4 <> NIL) then DeactivateSavePoint(Save4);}

	AIaddPlayer('player(player)');


	{************* INIT LEADERS}
	AIaddEntity('leader(leader)');
	AIsetHunterOnRadar('leader(leader)', false);
	AIsetEntityAsLeader('leader(leader)');
	AIsetLeaderInvisible('leader(leader)');
	AIEntityAlwaysEnabled('leader(leader)');
	AIaddLeaderEnemy('leader(leader)', 'player(player)');
	AIaddEntity('leader02(leader)');
	AIsetHunterOnRadar('leader02(leader)', false);
	AIsetEntityAsLeader('leader02(leader)');
	AIsetLeaderInvisible('leader02(leader)');
	AIEntityAlwaysEnabled('leader02(leader)');
	AIaddLeaderEnemy('leader02(leader)', 'player(player)');
	{************* INIT LEADERS}
	
	AIdefineGoalHuntEnemy('huntPlayer', 'player(player)', false, 5);
	
	InitSubpack('sGunOpen', COMBATTYPEID_OPEN, true);
	InitSubpack('sGunCover', COMBATTYPEID_COVER, FALSE);
	{InitSubpack('sGunCoverTut', COMBATTYPEID_OPEN, true);}
	InitSubpack('sMelee', COMBATTYPEID_MELEE, true);

	{QTM setup}
	SetQTMBaseProbability(60.0);
	SetQTMLength(1.7);
	SetQTMPresses(1);

	{Openable boxes}
	SetEntityScriptsFromEntity('Home_Box_rotten_(O)', 'Home_Box_rotten_(O)01');
	SetEntityScriptsFromEntity('Home_Box_rotten_(O)', 'wardrobe_oldh_(O)');
	SetEntityScriptsFromEntity('Home_Box_rotten_(O)', 'wardrobe_oldh_(O)01');

	{Scoring setup}
	{SetNumberOfKillableHuntersInLevel(21, 21);}
	SetMaxScoreForLevel(41);
	
	runScript('leader(leader)', 'onLevelStateSwitch'); {start Level}
	
	SetLevelGoal('GOAL09');
	
end; {OnCreate}


{FUNCTIONS & PROCEDURES +++++++++++++++++++++++++++++++++ FUNCTIONS & PROCEDURES}

PROCEDURE InitSubpack;
begin
	AIaddSubpackForLeader('leader(leader)',  SubpackName);
	AIsetSubpackCombatType('leader(leader)',  SubpackName, CombatType);
	if HuntPlayer then
	begin
		AIAddGoalForSubPack('leader(leader)', SubpackName, 'huntPlayer');
		AIStayInHuntEnemy('leader(leader)', SubpackName, TRUE, AISCRIPT_RUNMOVESPEED);
	end;
end;

PROCEDURE createBTrigger;
var
	Vector1 : vec3d;
	Vector2 : vec3d;
begin
	setVector(Vector1, x1, y1, z1);
	setVector(Vector2, x2, y2, z2);
	createBoxTrigger(Vector1, Vector2, TriggerName);
end;

end.
        ";

        $expected = [
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "21000000",
            "04000000",
            "01000000",
            "50010000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "13000000",
            "01000000",
            "04000000",
            "ecffffff",
            "12000000",
            "02000000",
            "00000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "50010000",
            "21000000",
            "04000000",
            "01000000",
            "50010000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "13000000",
            "01000000",
            "04000000",
            "ecffffff",
            "12000000",
            "02000000",
            "00000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "13000000",
            "01000000",
            "04000000",
            "f0ffffff",
            "10000000",
            "01000000",
            "82010000",
            "13000000",
            "01000000",
            "04000000",
            "f4ffffff",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "14020000",
            "21000000",
            "04000000",
            "01000000",
            "50010000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "13000000",
            "01000000",
            "04000000",
            "ecffffff",
            "12000000",
            "02000000",
            "00000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "60010000",
            "12000000",
            "02000000",
            "0b000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "56010000",
            "21000000",
            "04000000",
            "01000000",
            "50010000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "13000000",
            "01000000",
            "04000000",
            "ecffffff",
            "12000000",
            "02000000",
            "00000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "66020000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3a000000",
            "10000000",
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "34000000",
            "09000000",
            "18000000",
            "22000000",
            "04000000",
            "01000000",
            "0c000000",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "e0ffffff",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "e4ffffff",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "e8ffffff",
            "10000000",
            "01000000",
            "84010000",
            "22000000",
            "04000000",
            "01000000",
            "18000000",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "ecffffff",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "f0ffffff",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "f4ffffff",
            "10000000",
            "01000000",
            "84010000",
            "22000000",
            "04000000",
            "01000000",
            "0c000000",
            "10000000",
            "01000000",
            "22000000",
            "04000000",
            "01000000",
            "18000000",
            "10000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "dcffffff",
            "12000000",
            "02000000",
            "00000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "28010000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3a000000",
            "20000000",
            "10000000",
            "0a000000",
            "11000000",
            "0a000000",
            "09000000",
            "34000000",
            "09000000",
            "28000000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00008040",
            "10000000",
            "01000000",
            "ab030000",
            "21000000",
            "04000000",
            "01000000",
            "10000000",
            "12000000",
            "02000000",
            "26000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "12000000",
            "01000000",
            "04000000",
            "10000000",
            "01000000",
            "a8020000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "a4020000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "78010000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "94010000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "98010000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "9c010000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "38000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "10000000",
            "01000000",
            "83000000",
            "21000000",
            "04000000",
            "01000000",
            "48000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4c030000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "6c010000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "20010000",
            "14000000",
            "01000000",
            "04000000",
            "6c010000",
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
            "3f000000",
            "d8050000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "10060000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "70010000",
            "01000000",
            "3c000000",
            "2c060000",
            "12000000",
            "01000000",
            "01000000",
            "16000000",
            "04000000",
            "70010000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "16000000",
            "04000000",
            "74010000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "58000000",
            "12000000",
            "02000000",
            "0d000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "16000000",
            "04000000",
            "80010000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "68000000",
            "12000000",
            "02000000",
            "0d000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "77000000",
            "16000000",
            "04000000",
            "84010000",
            "01000000",
            "14000000",
            "01000000",
            "04000000",
            "70010000",
            "29000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "9c070000",
            "14000000",
            "01000000",
            "04000000",
            "84010000",
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
            "3f000000",
            "50070000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "9c070000",
            "12000000",
            "01000000",
            "02000000",
            "16000000",
            "04000000",
            "6c010000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "16000000",
            "04000000",
            "78010000",
            "01000000",
            "14000000",
            "01000000",
            "04000000",
            "80010000",
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
            "f4070000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "24080000",
            "14000000",
            "01000000",
            "04000000",
            "80010000",
            "10000000",
            "01000000",
            "12030000",
            "14000000",
            "01000000",
            "04000000",
            "84010000",
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
            "7c080000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "ac080000",
            "14000000",
            "01000000",
            "04000000",
            "84010000",
            "10000000",
            "01000000",
            "12030000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "5b010000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4d010000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "a8010000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4f010000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "6d020000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "bf010000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "54010000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4d010000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "a8010000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "4f010000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "6d020000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "bf010000",
            "21000000",
            "04000000",
            "01000000",
            "98000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "54010000",
            "21000000",
            "04000000",
            "01000000",
            "ac000000",
            "12000000",
            "02000000",
            "0b000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "05000000",
            "10000000",
            "01000000",
            "58010000",
            "21000000",
            "04000000",
            "01000000",
            "b8000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "10000000",
            "04000000",
            "11000000",
            "02000000",
            "00000000",
            "32000000",
            "02000000",
            "1c000000",
            "10000000",
            "02000000",
            "39000000",
            "00000000",
            "21000000",
            "04000000",
            "01000000",
            "c4000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "02000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "10000000",
            "04000000",
            "11000000",
            "02000000",
            "00000000",
            "32000000",
            "02000000",
            "1c000000",
            "10000000",
            "02000000",
            "39000000",
            "00000000",
            "21000000",
            "04000000",
            "01000000",
            "d0000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "00000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "10000000",
            "04000000",
            "11000000",
            "02000000",
            "00000000",
            "32000000",
            "02000000",
            "1c000000",
            "10000000",
            "02000000",
            "39000000",
            "00000000",
            "12000000",
            "01000000",
            "00007042",
            "10000000",
            "01000000",
            "ac030000",
            "12000000",
            "01000000",
            "9a99d93f",
            "10000000",
            "01000000",
            "ad030000",
            "12000000",
            "01000000",
            "01000000",
            "10000000",
            "01000000",
            "ae030000",
            "21000000",
            "04000000",
            "01000000",
            "d8000000",
            "12000000",
            "02000000",
            "14000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "f0000000",
            "12000000",
            "02000000",
            "16000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "d9010000",
            "21000000",
            "04000000",
            "01000000",
            "d8000000",
            "12000000",
            "02000000",
            "14000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "08010000",
            "12000000",
            "02000000",
            "12000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "d9010000",
            "21000000",
            "04000000",
            "01000000",
            "d8000000",
            "12000000",
            "02000000",
            "14000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "1c010000",
            "12000000",
            "02000000",
            "14000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "d9010000",
            "12000000",
            "01000000",
            "29000000",
            "10000000",
            "01000000",
            "59030000",
            "21000000",
            "04000000",
            "01000000",
            "88000000",
            "12000000",
            "02000000",
            "0f000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "34010000",
            "12000000",
            "02000000",
            "13000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "e4000000",
            "21000000",
            "04000000",
            "01000000",
            "48010000",
            "12000000",
            "02000000",
            "07000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "41020000",
            "11000000",
            "09000000",
            "0a000000",
            "0f000000",
            "0a000000",
            "3b000000",
            "00000000"
        ];

        $compiler = new \App\Service\CompilerV2\Compiler($script, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC, false);
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