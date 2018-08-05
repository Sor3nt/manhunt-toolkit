<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LevelScriptTest extends KernelTestCase
{

    public function test()
    {
//        $this->assertEquals(true, true, 'The bytecode is not correct');
//return;
        $script = "

scriptmain LevelScript;

entity
	A01_Escape_Asylum : et_level;


type
	tLevelState = ( StartOfLevel, PickedUpSyringe, InOffice, LuredHunter, KilledHunter, BeforeElevator, LeftElevator, BeforeBeasts, SpottedByCamera, TurnedOnTV, InCarPark, EndOfLevel );
	tElevatorLevel = ( ElevatorUp, ElevatorDown );


var
	{ Set this flag to TRUE to make developing the level easier. Set it to FALSE for the final game. }
	lDebuggingFlag : Boolean;

	{ This tells what level the state is in. After setting (increasing) it, call OnLevelStateSwitch. }
	lLevelState : tLevelState;

	{ This flag is  TRUE when we are loading a saved game, not starting the level fresh. }
	lLoadingFlag : boolean;

	{ For savepoints: ambient audio stream played }
	lCurrentAmbientAudioTrack : integer;

    lElevatorLevel : tElevatorLevel;
	lPlayerHasUsedElevatorFlag : boolean;

	lExplainedBlockFlag : boolean;

    lBeastCutsceneSyncFlag : boolean;

	lCurrentLoonieName : string[30];

	{ [gupi] keep track of tutorial display scripts }	
	lSavePointTutRemoved : boolean;
	lButtonTutRemoved : boolean;

	OfficeDoorUnlocked : boolean;
	{officeKeyPickedUp :  boolean;}
	runSyringeSwitch : boolean;
	
	cellOneOpen : boolean;
	cellTwoOpen : boolean;
	{cellThreeOpen : boolean;}
	cellFourOpen : boolean;
	cellFiveOpen : boolean;
	
	cellCutscenePlayed : boolean;
	checkOpenerNeeded : boolean;
	
	aCellHasChanged : boolean;
	
	iLockersOpen : integer;
	
	bMeleeTutDone: boolean;		{Done Tut up to & incl. Pick up Health}
	
	leoIsReady : boolean;
	AIInited : boolean;
	switchReady : boolean;
	
	stealthTutSpotted : integer;
	
	lureTrigs : boolean;
	driverFail : boolean;
	spottedYard : boolean;
	
	stealthOneLooper : boolean;
	stealthTwoLooper : boolean;
	stealthThreeLooper : boolean;
	stealthOneHeard : boolean;
	stealthTwoHeard : boolean;
	stealthThreeHeard : boolean;
	stealthOneDone : boolean;
	stealthTwoDone : boolean;
	stealthThreeDone : boolean;
	stealthTwoFacingYou : boolean;
	stealthThreeFacingYou : boolean;
	holdMe : boolean;
	
	killedATruckman : boolean;
	NotUsed : boolean;
	
	reminderSet : boolean;

	{Only display the locker tutorial once}
	bLockerTutDisplayed : boolean;

{ Function and procedure declarations }
procedure InitAI; FORWARD;
{procedure InitMonitors; FORWARD;}


script OnCreate;
var
	SavePoint : EntityPtr;
	pos, pos2 : Vec3D;

begin
	{Set colouramp for normal Danny}
	SetColourRamp('FE_colramps', 1, 4.0);	
	
	WriteDebug('A01_Escape_Asylum : OnCreate');

	{ [gupi] moved to OnCreate script to ease debugging }
	SetNextLevelByName('A02_The_Old_House'); { proceed to old house }

	lSavePointTutRemoved := false;
	lButtonTutRemoved := false;

	lDebuggingFlag := FALSE;
	{lDebuggingFlag := TRUE;}
	
	cellCutscenePlayed := FALSE;	
	runSyringeSwitch := FALSE;

	leoIsReady := FALSE;

	bLockerTutDisplayed := FALSE;
	
	stealthTutSpotted := 0;
	
	lureTrigs := FALSE;
	bMeleeTutDone := FALSE;
	
		{ Are we debugging the level ? }
	if lDebuggingFlag = TRUE then
	begin
		KillThisScript;
	end;

	{ Set up scoring }
{	SetMaxScoreForLevel(27);}

	{ Disable diving (will be taught in Old House) }
	EnableAction(7, FALSE);
	
	FakeHunterDestroyAll;
	{SetNumberOfKillableHuntersInLevel(14, 11);}
	SetMaxScoreForLevel(45);
	
	AIInited := FALSE;

	{ Initialize AI }
	InitAI;
	
	{while AIInited = FALSE do
		sleep(10);}
		
	{ Initialize visuals }
	SetMaxNumberOfRats(0);
	SwitchLitterOn(FALSE);
	
	{Disable QTMs}
	SetQTMBaseProbability(-100.0);

	{ Hide objects needed for end cutscene }
	HideEntity(GetEntity('Truck_Asyl_Endcutscene'));
{	HideEntity(GetEntity('Player_Asyl_Endcutscene'));}
	HideEntity(GetEntity('BoxBlocker'));

	{ Set up cell doors. This must be done ASAP to stop hunters from wandering out of their cells on PS2. }
	{FIRST CELL - HALF OPEN}
	SetSlideDoorAjarDistance(GetEntity('cell1_(SD)'), 1.1);
	SetSlideDoorAjarDistance(GetEntity('cell2_(SD)'), 1.1);
	{SetSlideDoorAjarDistance(GetEntity('cell3_(SD)'), 1.1);}
	SetSlideDoorAjarDistance(GetEntity('cell4_(SD)'), 1.1);
	SetSlideDoorAjarDistance(GetEntity('cell5_(SD)'), 1.1);

	UnFreezeEntity(GetEntity('asylum_doorA_SL(D)'));
	LockEntity(GetEntity('asylum_doorA_SL(D)'));
	GraphModifyConnections(GetEntity('asylum_doorA_SL(D)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);

	UnFreezeEntity(GetEntity('asylum_doorA_SR(D)'));
	LockEntity(GetEntity('asylum_doorA_SR(D)'));
	GraphModifyConnections(GetEntity('asylum_doorA_SR(D)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);

	UnFreezeEntity(GetEntity('cell1_(SD)'));
	GraphModifyConnections(GetEntity('cell1_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);

	UnFreezeEntity(GetEntity('cell2_(SD)'));
	GraphModifyConnections(GetEntity('cell2_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);

	UnFreezeEntity(GetEntity('cell4_(SD)'));
	GraphModifyConnections(GetEntity('cell4_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
	
	UnFreezeEntity(GetEntity('cell5_(SD)'));
	GraphModifyConnections(GetEntity('cell5_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);


	UnFreezeEntity(GetEntity('gate01(SD)'));
	SetDoorState(GetEntity('gate01(SD)'),DOOR_CLOSED);
	GraphModifyConnections(GetEntity('gate01(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);

	UnFreezeEntity(GetEntity('gate02(SD)'));
	SetDoorState(GetEntity('gate02(SD)'),DOOR_CLOSED);
	LockEntity(GetEntity('gate02(SD)'));
	GraphModifyConnections(GetEntity('gate02(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);
	
	{RAIN}
	EntityPlayAnim(GetEntity('PFXRainWindow'), 'PAT_raindow', true);
	EntityPlayAnim(GetEntity('PFXRainWindow01'), 'PAT_raindow', true);
	EntityPlayAnim(GetEntity('PFXRainWindow02'), 'PAT_raindow', true);
	EntityPlayAnim(GetEntity('PFXRainWindow03'), 'PAT_raindow', true);
	EntityPlayAnim(GetEntity('PFXRainWindow04'), 'PAT_raindow', true);
	
	{LOCKERS}
	SetEntityScriptsFromEntity('SLockerC_(O)', 'SLockerC_(O)01');
	SetEntityScriptsFromEntity('SLockerC_(O)', 'SLockerC_(O)02');
	
	{Stop the elev blocker}
	EntityIgnoreCollisions(GetEntity('elev_door_block'), TRUE);
	
	{ Initialize monitors }
{	InitMonitors;}

	{ Hide save points and determine if we're loading a game }
	lLoadingFlag := TRUE;


	SavePoint := GetEntity('save_point_01');


	if NIL <> SavePoint then
	begin
		lLoadingFlag := FALSE;
		DeactivateSavePoint(SavePoint);
	end;
	SavePoint := GetEntity('save_point_02');
	if NIL <> SavePoint then
	begin
		DeactivateSavePoint(SavePoint);
	end;
	SavePoint := GetEntity('save_point_03');
	if NIL <> SavePoint then
	begin
		DeactivateSavePoint(SavePoint);
	end;
	SavePoint := GetEntity('save_point_04');
	if NIL <> SavePoint then
	begin
		DeactivateSavePoint(SavePoint);
	end;
		
	{Init Leo so his script file's OnCreate is called}

	{ Start level state machine }
	
	
	if lLoadingFlag = FALSE then
	begin
		lLevelState := StartOfLevel;
		RunScript('A01_Escape_Asylum', 'OnLevelStateSwitch');
	end
	else
	begin
		RunScript('A01_Escape_Asylum', 'OnLevelStateLoad');
	end;
	        
	 {DEBUGGGGGG      *******************}
	
	{SetVector(pos, 20.7197, 17.1234, 42.1878);

	 MoveEntity(GetPlayer, pos, 1);
	 lLevelState := InCarPark;
		RunScript('A01_Escape_Asylum', 'OnLevelStateLoad');}
	 {************************}
	    

end;


{ (Needed to advance state from within OnLevelStateSwitch. }
script Roundabout;
begin
	sleep(50);
	RunScript('A01_Escape_Asylum', 'OnLevelStateSwitch');
end;


script OnLevelStateSwitch;
{ The level is switching from one state to another.
  It is NOT called to deal with level loading - OnLevelStateLoad takes care of that. }

var
	HunterName : string[30];
	Door, Hunter : EntityPtr;
	pos, pos2 : Vec3D;
	Timer : integer;

begin
	case lLevelState of
	{ Start of the level }
	StartOfLevel: begin
		WriteDebug('Switching to state: StartOfLevel');
		
		AIAddEntity('leo(leader)');
		
		SetVector(pos, -13.1617, 0.0, 14.4017);
		SetVector(pos2, -9.86173, 2.5, 14.9017);
		CreateBoxTrigger(pos, pos2, 'triggerExecTutFinal');

		{ Create triggers }
   		SetVector(pos, -13.1617, 0.0, 17.5953);
		SetVector(pos2, -9.86173, 2.5, 18.0953);
		CreateBoxTrigger(pos, pos2, 'triggerBloodSpurt2');

		SetVector(pos, -3.94137, 1.75921, 1.96379);
		SetVector(pos2, -2.80827, 5.34154, 5.05305);
		CreateBoxTrigger(pos, pos2, 'triggerRadarTut');
		
		SetVector(pos, -6.80582, 3.53097, -0.250218);
		SetVector(pos2, -5.80582, 6.03097, 1.74978);
		CreateBoxTrigger(pos, pos2, 'triggerWheelChair');
		
		{ Create trigger in office }
		SetVector(pos, -17.8864, 6.20619, 32.4688);
		SetVector(pos2, -14.4864, 8.70619, 32.9688);
		CreateBoxTrigger(pos, pos2, 'triggerDoorLocked');
				
		SetVector(pos, -17.5835, 5.83983, 8.89683);
		SetVector(pos2, -13.9993, 8.1545, 9.51017);
		CreateBoxTrigger(pos, pos2, 'triggerNewMeleeTutTwo');
		
		SetVector(pos, -33.654, 0.0140175, 40.7009);
		SetVector(pos2, -33.0089, 2.32868, 44.196);
		CreateBoxTrigger(pos, pos2, 'triggerChairFly');
				
		SetVector(pos, -13.3137, 0.0, 30.9594);
		SetVector(pos2, -9.7295, 2.31466, 31.5728);
		CreateBoxTrigger(pos, pos2, 'triggerNewMeleeTutSBit');
		
		SetVector(pos, -45.9896, -0.0116219, 22.5831);
		SetVector(pos2, -42.9896, 2.16559, 22.9088);
		CreateBoxTrigger(pos, pos2, 'triggerRadarCutscene');
		
		SetVector(pos, -51.8878, 0.0, 16.092);
		SetVector(pos2, -41.9805, 5.59892, 20.1157);
		CreateBoxTrigger(pos, pos2, 'triggerStealthOneAware');
		
		SetVector(pos, -52.4107, 0.0, 24.695);
		SetVector(pos2, -42.5034, 5.59892, 28.8932);
		CreateBoxTrigger(pos, pos2, 'triggerStealthTwoAware');
		
		SetVector(pos, -52.4107, 0.0, 33.5668);
		SetVector(pos2, -42.5034, 5.59892, 37.7924);
		CreateBoxTrigger(pos, pos2, 'triggerStealthThreeAware');
		
		SetVector(pos, -42.7699, 0.0, 40.6692);
		SetVector(pos2, -42.0833, 4.28021, 44.217);
		CreateBoxTrigger(pos, pos2, 'triggerStealthSummary');
		
		SetVector(pos, -20.7716, 0.0, 39.9957);
		SetVector(pos2, -20.3514, 2.79072, 45.3256);
		CreateBoxTrigger(pos, pos2, 'triggerSteamShoot');
		
		SetVector(pos, -13.3137, 0.0, 34.5461);
		SetVector(pos2, -9.7295, 2.31466, 35.1595);
		CreateBoxTrigger(pos, pos2, 'triggerBloodSpurt1');
		
		SetVector(pos, -53.1723, 0.0, 6.02074);
		SetVector(pos2, -41.0816, 1.33944, 11.6464);
		CreateBoxTrigger(pos, pos2, 'triggerWhyteSpeech');
		
		SetCurrentLOD(GetEntity('Asylum_cell_flicker'),1);
		SetCurrentLOD(GetEntity('Asylum_bars_flicker'), 1);
		SwitchLightOff (getentity('CJ_LIGHT_on_(L)39'));
		SetCurrentLOD(GetEntity('dead_staff'),1);

		{ Disable elevator button }
{		EnableUseable(GetEntity('buttonElevatorLower_(S)'), FALSE);}
		RunScript('buttonElevatorLower_(S)', 'BlinkRed');

		{ Initialize global variables }
		lPlayerHasUsedElevatorFlag := FALSE;
		lExplainedBlockFlag := FALSE;
		lBeastCutsceneSyncFlag := FALSE;

		{ Set initial player health to 50% }
{		SetDamage(GetEntity('player(player)'), 50);}
		
		SetLevelGoal('GOAL1');
		
		EntityPlayAnim(GetEntity('whyte(hunter)'), 'ASY_IDLE_STRANGLED_ANIM', true);

		RunScript('leo(leader)', 'IntroCamRun');
		
		sleep(1000);
		while IsCutSceneInProgress do
			sleep(50);
			
		writedebug('Intro has finished');

		{ Initialize sobbing woman (this will force creation of this hunter and a call to OnCreate). }
		AIAddEntity('SobbingWoman(hunter)');
		RegisterNonExecutableHunterInLevel;
		SetHunterMute(GetEntity('SobbingWoman(hunter)'), TRUE);
		HideEntity(GetEntity('SobbingWoman(hunter)'));
		
		AIAddEntity('StealthTut(hunter)');
		RunScript('StealthTut(hunter)', 'Init');
		
		AIAddEntity('StealthTutTwo(hunter)');
		RunScript('StealthTutTwo(hunter)', 'Init');
		
		AIAddEntity('StealthTutThree(hunter)');
		RunScript('StealthTutThree(hunter)', 'Init');
		
		AIAddEntity('NewExecTut(hunter)');
		RunScript('NewExecTut(hunter)', 'Init');

		RunScript('StartLoon03(hunter)', 'Init');
		
		{Init muttering guy}
		Runscript('ExecTut(hunter)', 'Init');
		
		sleep(100);
		SetLevelGoal('GOAL4');
		
		while IsCutSceneInProgress do
			sleep(50);
						
		HideEntity(GetEntity('E1_Cell_Door_(D)04'));
		
		{AIAddEntity('DyingMan(hunter)');
		SetPedDoNotDecay(GetEntity('DyingMan(hunter)'), TRUE);}
	
		{ Make character unlockonable, invulnerable, unexecutable }
		{SetPedLockonable(GetEntity('DyingMan(hunter)'), FALSE);
		SetEntityInvulnerable(GetEntity('DyingMan(hunter)'), TRUE);
		SetHunterExecutable(GetEntity('DyingMan(hunter)'), FALSE);
		AISetEntityIdleOverride('DyingMan(hunter)', TRUE, TRUE);}
		
		{AIEntityPlayAnim('DyingMan(hunter)', 'ASY_INMATE_IDLE_1');}

		sleep(1500);

		EntityPlayAnim(GetEntity('PFXDrip1'), 'PAT_drips01', true);
		EntityPlayAnim(GetEntity('PFXLand1'), 'PAT_drpcol01', true);
		EntityPlayAnim(GetEntity('PFXLand2'), 'PAT_drpcol01', true);
		EntityPlayAnim(GetEntity('PFXDrip02'), 'PAT_drips01', true);
		{SOUND OF DRIPPING}
		PlayAudioLoopedFromEntity(GetEntity('PFXDrip1'), 'LEVEL', 'WATERDRIP', 40, 8);
		PlayAudioLoopedFromEntity(GetEntity('PFXDrip02'), 'LEVEL', 'WATERDRIP', 40, 8);

		EntityPlayAnim(GetEntity('PFXDrip03'), 'PAT_drips01', true);
		EntityPlayAnim(GetEntity('PFXLand3'), 'PAT_drpcol01', true);
		PlayAudioLoopedFromEntity(GetEntity('PFXDrip03'), 'LEVEL', 'WATERDRIP', 40, 8);
		
		
		while not IsScriptAudioStreamCompleted do sleep(10);
		FrisbeeSpeechPlay('LEO31', 100,10);
    
		DisplayGameText('MOVE1');
		SetLevelGoal('GOAL1A');

		
		while NOT FrisbeeSpeechIsFinished('LEO31') do sleep(10);
		sleep(500);
        
		FrisbeeSpeechPlay('LEO32', 100,10);
		while NOT FrisbeeSpeechIsFinished('LEO32') do sleep(10);
		
		{KillEntityWithoutAnim(GetEntity('DyingMan(hunter)'));}
	end;

  PickedUpSyringe: begin
		WriteDebug('Switching to state: PickedUpSyringe');
		
		if runSyringeSwitch = FALSE then
		begin
			
			
			{if (GetEntity('Syringe_(CT)')) <> NIL then
			begin
				clearlevelgoal('GOAL2C');
				RadarPositionClearEntity(GetEntity('SyringeTarget'));
			end;}
			{ Set up elevator doors }
			RunScript('Upt_Elevator_DoorIL_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorIR_(SD)');
			RunScript('Upt_Elevator_DoorIR_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorDR_(SD)');
			RunScript('Upt_Elevator_DoorDR_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorDL_(SD)');
			RunScript('Upt_Elevator_DoorDL_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUR_(SD)');
			RunScript('Upt_Elevator_DoorUR_(SD)', 'Init');
			RunScript('Upt_Elevator_DoorUR_(SD)', 'Open');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUL_(SD)');
			RunScript('Upt_Elevator_DoorUL_(SD)', 'Init');
			RunScript('Upt_Elevator_DoorUL_(SD)', 'Open');
			
			runSyringeSwitch := TRUE;
			
		end;
  end;

	InOffice: begin
		WriteDebug('Switching to state: InOffice');

		{ClearLevelGoal('GOAL2B');}
		{Play anim on cubicle guy}
	   RunScript('player(player)', 'PickUpPhoneCutScene');

		{ClearLevelGoal('GOAL2');}

		DestroyEntity(GetEntity('triggerDoorLocked'));
		{sleep(2000);}
		if runSyringeSwitch = FALSE then
		begin
			if (GetEntity('Syringe_(CT)')) <> NIL then
			begin
				clearlevelgoal('GOAL2C');
				RadarPositionClearEntity(GetEntity('SyringeTarget'));
			end;
		
			{ Set up elevator doors }
			RunScript('Upt_Elevator_DoorIL_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorIR_(SD)');
			RunScript('Upt_Elevator_DoorIR_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorDR_(SD)');
			RunScript('Upt_Elevator_DoorDR_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorDL_(SD)');
			RunScript('Upt_Elevator_DoorDL_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUR_(SD)');
			RunScript('Upt_Elevator_DoorUR_(SD)', 'Init');
			SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUL_(SD)');
			RunScript('Upt_Elevator_DoorUL_(SD)', 'Init');
			
			runSyringeSwitch := TRUE;
			
		end;
	end;

	BeforeElevator: begin
		WriteDebug('Switching to state: BeforeElevator');
		switchReady := FALSE;

		{ Store current ambient stream in a global variable prior to saving }
		lCurrentAmbientAudioTrack := GetAmbientAudioTrack;
	
		{ Make dead body go away (it's not really dead, the player isn't near it, not doing this currently causes an assert because of a related
		  code bug (\"[ADRIAN] Saving a corpse that isn't dead?\") }
		DestroyEntity(GetEntity('RadarTut(hunter)'));
	
		{ Wait to give previous command time to finish }
		sleep(50);
	
		{ Save game }
	  TriggerSavePoint(GetEntity('save_point_01'), TRUE);

		{ Set level goals }
    ClearAllLevelGoals;
		SetLevelGoal('GOAL10');
		SetLevelGoal('GOAL6');
		{SetLevelGoal('GOAL6A');}

		{ [gupi] use sub-script to display tutorial messages here so that we can display them asynchronously }	
		RunScript('A01_Escape_Asylum', 'DisplaySavePointTutorials');

	{ Init running guy }
{		RunScript('leader(leader)', 'InitRunner');}
    
    {Runscript('DeadLiftGuy(hunter)', 'Init');}
    
		SetVector(pos, -29.4144, 5.89855, 53.3413);
		SetVector(pos2, -25.7069, 7.61538, 53.7227);
		CreateBoxTrigger(pos, pos2, 'triggerPickman');
		
		SetVector(pos, -30.764, 5.89855, 55.2919);
		SetVector(pos2, -30.2079, 7.61538, 58.8733);
		CreateBoxTrigger(pos, pos2, 'triggerPickmanSecond');
		 		
		AIAddEntity('LiftLoonie01(hunter)');
		Runscript('LiftLoonie01(hunter)', 'Init');
		
		
{		AIAddEntity('Mutter02(hunter)');
		AIAddHunterToLeaderSubpack('leader2(leader)', 'subMutters', 'Mutter02(hunter)');
		AiMakeEntityDeaf('Mutter02(hunter)', 0);
		AiMakeEntityBlind('Mutter02(hunter)', 0);
		AIDefineGoalGotoNode('goalMutter02', 'Mutter02(hunter)', AISCRIPT_HIGHPRIORITY, 'MUTTERGOTO2', AISCRIPT_WALKMOVESPEED, TRUE);		}

		{CreateSphereTrigger(GetEntityPosition(GetEntity('Mutter02(hunter)')), 2.00014, 'tMutterAttack02');}

		switchReady := TRUE;

	end;

	LeftElevator: begin
		WriteDebug('Switching to state: LeftElevator');

        { Destroy unnecessary hunters }
		if NIL <> GetEntity('WackyRunner(hunter)') then DestroyEntity(GetEntity('WackyRunner(hunter)'));
		if NIL <> GetEntity('NewExecTut(hunter)') then DestroyEntity(GetEntity('NewExecTut(hunter)'));
		if NIL <> GetEntity('FightScene1(leader)') then DestroyEntity(GetEntity('FightScene1(leader)'));
		if NIL <> GetEntity('FightScene2(leader)') then DestroyEntity(GetEntity('FightScene2(leader)'));
		if NIL <> GetEntity('StartLoon03(hunter)') then DestroyEntity(GetEntity('StartLoon03(hunter)'));
		
		{LIGHTS}
		RunScript('player(player)', 'FlickeringLightsLeft');
		RunScript('player(player)', 'FlickeringLightsRight');
		RunScript('player(player)', 'FlickeringLightsHall');
		
		{ In case the player didn't execute the first hunter, make sure the HUD is shown }
		ToggleHudFlag(HID_ALL_PLAYER_ITEMS, TRUE);

		{ Player can jump again }
		SetPlayerJumpFlag(TRUE);

		{ Create triggers }
		SetVector(pos, -2.87157, 24.9674, -6.49301);
		CreateSphereTrigger(pos, 1.92, 'triggerSavePoint2');

		SetVector(pos, -25.5945, 24.162, 33.9065);
		SetVector(pos2, -25.0945, 27.162, 40.9065);
    CreateBoxTrigger(pos, pos2, 'triggerNearingMeleeTut');

		SetVector(pos, -15.26811, 25.1953, 37.3164);
		CreateSphereTrigger(pos, 1.5, 'triggerMeleeSounds');
		
		SetVector(pos, -12.5526, 24.3186, 35.1171);
		SetVector(pos2, -9.525, 27.1686, 35.8971);
		CreateBoxTrigger(pos, pos2, 'triggerJumpTut3');
		
		SetVector(pos, -16.5205, 25.1953, 37.3164);
		CreateSphereTrigger(pos, 1.5, 'triggerWallSquash');


		{ Wait for guy to run past }
		Sleep(1000);
		
		SetVector(pos, -7.00874, 25.6269, -6.44073);
		CreateSphereTrigger(pos, 1.47317, 'triggerBarBeating');
		
		
		AIAddEntity('FightScene3(leader)');
		AISetEntityAsLeader('FightScene3(leader)');
		AIEntityAlwaysEnabled('FightScene3(leader)');
		AIAddSubpackForLeader('FightScene3(leader)', 'fscene3');
		AISetSubpackCombatType('FightScene3(leader)', 'fscene3', COMBATTYPEID_MELEE);
		AIAddHunterToLeaderSubpack('FightScene3(leader)', 'fscene3', 'FightScene3(leader)');
		SetPedLockonable (GetEntity ('FightScene3(leader)'), FALSE);
		AISetHunterOnRadar('FightScene3(leader)', FALSE);
		
		AIAddEntity('DummyBanger(hunter)');
		SetEntityInvulnerable(GetEntity('DummyBanger(hunter)'), TRUE);
		SetPedLockonable (GetEntity ('DummyBanger(hunter)'), FALSE);
		AIEntityPlayAnimLooped('DummyBanger(hunter)', 'BAT_INMATE_SMACK_HEAD_ANIM', 0.0);
		AISetHunterOnRadar('DummyBanger(leader)', FALSE);
		
		AIAddEntity('FightScene4(leader)');
		AISetEntityAsLeader('FightScene4(leader)');
		AIEntityAlwaysEnabled('FightScene4(leader)');
		AIAddSubpackForLeader('FightScene4(leader)', 'fscene4');
		AISetSubpackCombatType('FightScene4(leader)', 'fscene4', COMBATTYPEID_MELEE);
		AIAddHunterToLeaderSubpack('FightScene4(leader)', 'fscene4', 'FightScene4(leader)');
		SetPedLockonable (GetEntity ('FightScene4(leader)'), FALSE);
		AISetHunterOnRadar('FightScene4(leader)', FALSE);
		
		AISetHunterIdleActionMinMax('FightScene3(leader)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_LOWPRIORITY, 1, 2);
		AISetHunterIdleActionMinMax('FightScene4(leader)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_LOWPRIORITY, 1, 2);
		
		SetPedHurtOtherPeds('FightScene3(leader)', true);	
		SetPedHurtOtherPeds('FightScene4(leader)', true);	
		
		{Make Enemies}
		AIDefineGoalHuntEnemy('goalHuntFScene3', 'FightScene4(leader)', true, 1);
		AIDefineGoalHuntEnemy('goalHuntFScene4', 'FightScene3(leader)', true, 1);
		
		AIAddGoalForSubpack('FightScene3(leader)', 'fscene3', 'goalHuntFScene3');
		AIAddGoalForSubpack('FightScene4(leader)', 'fscene4', 'goalHuntFScene4');
		
		AIAddLeaderEnemy('FightScene3(leader)', 'FightScene4(leader)');
		AIAddLeaderEnemy('FightScene4(leader)', 'FightScene3(leader)');
		SetEntityInvulnerable(GetEntity('FightScene3(leader)'), TRUE);
		
	end;

	BeforeBeasts: begin
		WriteDebug('Switching to state: BeforeBeasts');

		{ Store current ambient stream in a global variable prior to saving }
		lCurrentAmbientAudioTrack := GetAmbientAudioTrack;

	    TriggerSavePoint(GetEntity('save_point_02'), TRUE);

		{Get rid of fighters}
		if GetEntity('DummyBanger(hunter)') <> NIL then DestroyEntity(GetEntity('DummyBanger(hunter)'));
		if GetEntity('FightScene3(leader)') <> NIL then DestroyEntity(GetEntity('FightScene3(leader)'));
		if GetEntity('FightScene4(leader)') <> NIL then DestroyEntity(GetEntity('FightScene4(leader)'));
		
     { Create triggers }
		SetVector(pos, 4.53198, 24.1012, -1.43828);
		SetVector(pos2, 5.17104, 27.4754, 2.40105);
		CreateBoxTrigger(pos, pos2, 'triggerEnteringCellArea');

		SetVector(pos, -10.1035, 18.0687, 13.0312);
		SetVector(pos2, -6.10354, 21.0687, 14.0312);
		CreateBoxTrigger(pos, pos2, 'triggerSavePoint3');

    SetVector(pos, 6.23484, 19.0737, 3.53492);
    SetVector(pos2, 31.2348, 22.0737, 10.5349);
    CreateBoxTrigger(pos, pos2, 'triggerBeastArea');

		{SetVector(pos, 15.0, 24.08, -1.23252);
		SetVector(pos2, 17.81, 27.5, 2.88717);
		CreateBoxTrigger(pos, pos2, 'triggerCellSounds');}

      SetVector(pos, -10.1718, 18.09, 11.38);
      SetVector(pos2, -6.12823, 20.59, 11.48);
      CreateBoxTrigger(pos, pos2, 'triggerSecuCam');
        
			{SetVector(pos, 18.8352, 17.8278, 1.95659);
			SetVector(pos2, 24.2485, 20.4149, 11.914);
			CreateBoxTrigger(pos, pos2, 'triggerMadman');
			
			SetVector(pos, 13.3311, 17.8278, 1.95659);
			SetVector(pos2, 18.7445, 20.4149, 11.914);
			CreateBoxTrigger(pos, pos2, 'triggerMadmanOtherSide');
			
			SetEntityScriptsFromEntity('triggerMadman', 'triggerMadmanOtherSide');}
			
			{ Boxtrigger triggerDoor1Bugfix }
			SetVector(pos, 6.62871, 17.8278, 1.75262);
			SetVector(pos2, 8.2053, 20.4149, 2.09262);
			CreateBoxTrigger(pos, pos2, 'triggerDoor1Bugfix');
		
			{ Boxtrigger triggerDoor2Bugfix }
			SetVector(pos, 12.6451, 17.8278, 1.7842);
			SetVector(pos2, 14.2217, 20.4149, 2.1242);
			CreateBoxTrigger(pos, pos2, 'triggerDoor2Bugfix');
		
			{ Boxtrigger triggerDoor3Bugfix }
			SetVector(pos, 18.632, 17.8278, 1.8851);
			SetVector(pos2, 20.2086, 20.4149, 2.2251);
			CreateBoxTrigger(pos, pos2, 'triggerDoor3Bugfix');
		
			{ Boxtrigger triggerDoor4Bugfix }
			SetVector(pos, 24.5805, 17.8278, 1.81771);
			SetVector(pos2, 26.1571, 20.4149, 2.15771);
			CreateBoxTrigger(pos, pos2, 'triggerDoor4Bugfix');
			
			SetVector(pos, 5.51342, 18.5139, 3.09539);
			SetVector(pos2, 27.217, 19.7435, 11.4264);
			CreateBoxTrigger(pos, pos2, 'triggerStartLightning');
			
			SetVector(pos, 6.86906, 17.9561, 1.30207);
			SetVector(pos2, 8.09926, 18.7576, 3.13897);
			CreateBoxTrigger(pos, pos2, 'triggerLightReveal');

    	ClearAllLevelGoals;
    	SetLevelGoal('GOAL10');

      { Initialize hunters }
      AIAddEntity('Beast1(hunter)');
      RunScript('Beast1(hunter)', 'Init');
      AIAddEntity('Beast2(hunter)');
      RunScript('Beast2(hunter)', 'Init');
      
      AIAddEntity('DeadIntroNurse(hunter)');
      RegisterNonExecutableHunterInLevel;
      KillEntity(GetEntity('DeadIntroNurse(hunter)'));
      
   {   if GetEntity('leo(hunter)') <> NIL then
      	DestroyEntity(GetEntity('leo(hunter)'));}
	end;

	SpottedByCamera: begin
		WriteDebug('Switching to state: SpottedByCamera');

        { Destroy unnecessary hunters }
		if NIL <> GetEntity('JumpTut(hunter)') then DestroyEntity(GetEntity('JumpTut(hunter)'));
		if NIL <> GetEntity('Nursie(hunter)') then DestroyEntity(GetEntity('Nursie(hunter)'));
		if NIL <> GetEntity('DeadIntroNurse(hunter)') then DestroyEntity(GetEntity('DeadIntroNurse(hunter)'));
		if NIL <> GetEntity('Jumper(hunter)') then DestroyEntity(GetEntity('Jumper(hunter)'));
		if NIL <> GetEntity('DeadLiftGuy(hunter)') then DestroyEntity(GetEntity('DeadLiftGuy(hunter)'));
		

		{ Set level goals }
		ClearAllLevelGoals;
		SetLevelGoal('GOAL10');
		
		checkOpenerNeeded := FALSE;
		
        { Open door }
  	GraphModifyConnections(GetEntity('asylum_doorA_SL(D)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
  	GraphModifyConnections(GetEntity('asylum_doorA_SR(D)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
  
  	UnLockEntity(GetEntity('asylum_doorA_SL(D)'));
  	UnLockEntity(GetEntity('asylum_doorA_SR(D)'));
    
    	
    UnLockEntity(GetEntity('gate02(SD)'));
		GraphModifyConnections(GetEntity('gate02(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);
		SetDoorState(GetEntity('gate02(SD)'),DOOR_CLOSING);
	
		UnLockEntity(GetEntity('gate01(SD)'));
		GraphModifyConnections(GetEntity('gate01(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);
		SetDoorState(GetEntity('gate01(SD)'),DOOR_CLOSING);

		{ Create triggers }
		SetVector(pos, 1.13528, 22.4246, 46.9438);
		SetVector(pos2, 4.90758, 24.9246, 47.3238);
		CreateBoxTrigger(pos, pos2, 'triggerNearingTVArea');

		SetVector(pos, 13.9248, 15.4573, 46.5688);
		SetVector(pos2, 17.7029, 18.4573, 50.2312);
		CreateBoxTrigger(pos, pos2, 'triggerCarparkStart');
		
		{SetVector(pos, -4.62544, 17.9702, 17.0601);
		SetVector(pos2, -2.27975, 18.9197, 18.3229);
		CreateBoxTrigger(pos, pos2, 'triggerRadioChatter');}

  	{ Initialize hunters }
  	SetEntityScriptsFromEntity('TVLoonie(hunter)', 'TVLoonie01(hunter)');
  	SetEntityScriptsFromEntity('TVLoonie(hunter)', 'TVLoonie02(hunter)');
          
    checkOpenerNeeded := FALSE;
    
	end;

	InCarPark: begin
		WriteDebug('Switching to state: InCarPark');

        { Destroy unnecessary hunters }
		if NIL <> GetEntity('Beast1(hunter)') then DestroyEntity(GetEntity('Beast1(hunter)'));
		if NIL <> GetEntity('Beast2(hunter)') then DestroyEntity(GetEntity('Beast2(hunter)'));
		if NIL <> GetEntity('TVLoonie01(hunter)') then DestroyEntity(GetEntity('TVLoonie01(hunter)'));
		if NIL <> GetEntity('TVLoonie01(hunter)') then DestroyEntity(GetEntity('TVLoonie01(hunter)'));
		if NIL <> GetEntity('ExecTut02(hunter)') then DestroyEntity(GetEntity('ExecTut02(hunter)'));
		if NIL <> GetEntity('fighter02(hunter)') then DestroyEntity(GetEntity('fighter02(hunter)'));
		if NIL <> GetEntity('RadioHunter(hunter)') then DestroyEntity(GetEntity('RadioHunter(hunter)'));
		if NIL <> GetEntity('ExecTut(hunter)') then DestroyEntity(GetEntity('ExecTut(hunter)'));
		if NIL <> GetEntity('LiftLoonie01(hunter)') then DestroyEntity(GetEntity('LiftLoonie01(hunter)'));
		
		HideEntity(GetEntity('PFXRainWindow'));
		HideEntity(GetEntity('PFXRainWindow01'));
		HideEntity(GetEntity('PFXRainWindow02'));
		HideEntity(GetEntity('PFXRainWindow03'));
		HideEntity(GetEntity('PFXRainWindow04'));
		
		killedATruckman := FALSE;

		{ Store current ambient stream in a global variable prior to saving }
		lCurrentAmbientAudioTrack := GetAmbientAudioTrack;

		Sleep(1000);

    { Play cut-scene }
{    AIAddEntity('GuardEndScene(hunter)');
		AIAddHunterToLeaderSubpack('leader(leader)', 'subMeleeTut', 'GuardEndScene(hunter)');}
			
		AIAddEntity('DriverEndScene(hunter)');
		AIAddHunterToLeaderSubpack('leader(leader)', 'subMeleeTut', 'DriverEndScene(hunter)');
		HideEntity(GetEntity('DriverEndScene(hunter)'));
		SetHunterExecutable(GetEntity('DriverEndScene(hunter)'), FALSE);
		
{		RunScript('player(player)', 'CarParkSpotlight');}
		{RunScript('player(player)', 'CarParkPuzzle');}
		
		SetVector(pos, 30.2101, 17.1234, 37.434);
		CreateSphereTrigger(pos, 0.8, 'triggerEnteringTruck');
		
		SetVector(pos, 25.4899, 16.0642, 35.5);
		SetVector(pos2, 48.7999, 19.0033, 51.0277);
		CreateBoxTrigger(pos, pos2, 'triggerDeadNearTruck');
		
		RunScript('player(player)', 'InCarParkCutScene');
		sleep(100);
		while IsCutSceneInProgress do sleep(50);
		
		    { Initialize carpark hunters }
		SetEntityScriptsFromEntity('TruckGuard(hunter)', 'TruckGuard1(hunter)');
		RunScript('TruckGuard1(hunter)', 'Init');
		SetEntityScriptsFromEntity('TruckGuard(hunter)', 'TruckGuard2(hunter)');
		RunScript('TruckGuard2(hunter)', 'Init');

    { Trigger save point }
		TriggerSavePoint(GetEntity('save_point_04'), TRUE);

    { Change level goals }
		ClearAllLevelGoals;
		{SetLevelGoal('GOAL13');
		SetLevelGoal('GOAL12');}
		SetLevelGoal('GOAL15');
		
		RadarPositionSetEntity(GetEntity('triggerEnteringTruck'), MAP_COLOR_BLUE);
		
		{SetVector(pos, 31.1342, 18.2325, 37.5213);
		CreateSphereTrigger(pos, 1.53063, 'triggerTruckSee');}
		
	end;

	EndOfLevel: begin
		
		SetLevelCompleted;
	end;

	end; {case of}
end;

script DisplaySavePointTutorials;
begin
	
	while IsGameTextDisplaying do
		Sleep(100);
	
	if (NOT ThisLevelBeenCompletedAlready	) then
	begin
		{ Explain how game saving works }
		DisplayGameText('H_SAVE');
		while IsGameTextDisplaying do
			Sleep(100);
	
		DisplayGameText('H_SAVE1');
		while IsGameTextDisplaying do
			Sleep(100);
	end;
	
	if GetDifficultyLevel <> DIFFICULTY_NORMAL then begin
		DisplayGameText('H_RUNB');
	end else begin
		DisplayGameText('H_RUN');
	end;
	
	SetLevelGoal('GOAL1B');
		while IsGameTextDisplaying do
			Sleep(100);
			
	DisplayGameText('H_RUN2');
		while IsGameTextDisplaying do
			Sleep(100);
	
	lSavePointTutRemoved := true;
	RemoveThisScript;
end;


{ [gupi] }
script DisplayButtonTutorials;
begin
	{ Tell player to enter elevator }
	

 {   FrisbeeSpeechPlay('LIFT1', 75, 25);
    while not FrisbeeSpeechIsFinished('LIFT1') do
        sleep(50);
        
    sleep(1200);}
    
    while IsGameTextDisplaying do
			
			Sleep(100);

	{ Explain how buttons work }
	DisplayGameText('LIFT2');


	RemoveThisScript;
end;


script OnLevelStateLoad;
var
	pos, pos2 : Vec3D;

begin
    { Turn HUD on (just in case) }
	ToggleHudFlag(HID_ALL_PLAYER_ITEMS, TRUE);

	{ Restore ambient audio stream from loaded global variable }
	SetAmbientAudioTrack(lCurrentAmbientAudioTrack);

	case lLevelState of 
		
	BeforeElevator: begin
		WriteDebug('Skipping to state: BeforeElevator');

		{ Set player angle }
		SetPlayerHeading(-90.0);

		{ Set level goals }
    ClearAllLevelGoals;
		SetLevelGoal('GOAL10');
		SetLevelGoal('GOAL6');
		SetLevelGoal('GOAL6A');
		
		AIAddEntity('leo(leader)');

	  { Open door the button opened }
		UnLockEntity(GetEntity('asylum_cell_door_slide01_(SD)'));
		GraphModifyConnections(GetEntity('asylum_cell_door_slide01_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
		SetSlideDoorAjarDistance(GetEntity('asylum_cell_door_slide01_(SD)'), 1.1);
		SetDoorState(GetEntity('asylum_cell_door_slide01_(SD)'), DOOR_OPENING);
			
		{ Enable elevator button }
    EnableUseable(GetEntity('buttonElevatorLower_(S)'), TRUE);
    RunScript('buttonElevatorLower_(S)', 'BlinkRed');

		{ Set up elevator doors }
		RunScript('Upt_Elevator_DoorIL_(SD)', 'Init');
		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorIR_(SD)');
		RunScript('Upt_Elevator_DoorIR_(SD)', 'Init');
		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorDR_(SD)');
		RunScript('Upt_Elevator_DoorDR_(SD)', 'Init');
		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorDL_(SD)');
		RunScript('Upt_Elevator_DoorDL_(SD)', 'Init');
		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUR_(SD)');
		RunScript('Upt_Elevator_DoorUR_(SD)', 'Init');
		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUL_(SD)');
		RunScript('Upt_Elevator_DoorUL_(SD)', 'Init');
		
		{FIGHTERS}
		
		AIAddEntity('FightScene1(leader)');
		AISetEntityAsLeader('FightScene1(leader)');
		AIEntityAlwaysEnabled('FightScene1(leader)');
		AIAddSubpackForLeader('FightScene1(leader)', 'fscene1');
		AISetSubpackCombatType('FightScene1(leader)', 'fscene1', COMBATTYPEID_MELEE);
		AIAddHunterToLeaderSubpack('FightScene1(leader)', 'fscene1', 'FightScene1(leader)');
		AIDefineGoalGotoNodeIdle('goalFightDead', 'FightScene1(leader)', AISCRIPT_VERYHIGHPRIORITY, 'LIFTRUNOFF', AISCRIPT_RUNMOVESPEED, FALSE);
		SetPedLockonable (GetEntity ('FightScene1(leader)'), FALSE);
		AISetHunterOnRadar('FightScene1(leader)', FALSE);
		
		AIAddEntity('FightScene2(leader)');
		AISetEntityAsLeader('FightScene2(leader)');
		AIEntityAlwaysEnabled('FightScene2(leader)');
		AIAddSubpackForLeader('FightScene2(leader)', 'fscene2');
		AISetSubpackCombatType('FightScene2(leader)', 'fscene2', COMBATTYPEID_MELEE);
		AIAddHunterToLeaderSubpack('FightScene2(leader)', 'fscene2', 'FightScene2(leader)');
		SetPedLockonable (GetEntity ('FightScene2(leader)'), FALSE);
		AISetHunterOnRadar('FightScene2(leader)', FALSE);
		
		AISetHunterIdleActionMinMax('FightScene1(leader)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_LOWPRIORITY, 1, 2);
		AISetHunterIdleActionMinMax('FightScene2(leader)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_LOWPRIORITY, 1, 2);
		
		SetPedHurtOtherPeds('FightScene1(leader)', true);	
		SetPedHurtOtherPeds('FightScene2(leader)', true);	
		
		{Make Enemies}
		AIDefineGoalHuntEnemy('goalHuntFScene1', 'FightScene2(leader)', true, 1);
		AIDefineGoalHuntEnemy('goalHuntFScene2', 'FightScene1(leader)', true, 1);
		
		AIAddGoalForSubpack('FightScene1(leader)', 'fscene1', 'goalHuntFScene1');
		AIAddGoalForSubpack('FightScene2(leader)', 'fscene2', 'goalHuntFScene2');
		
		AIAddLeaderEnemy('FightScene1(leader)', 'FightScene2(leader)');
		AIAddLeaderEnemy('FightScene2(leader)', 'FightScene1(leader)');
		SetEntityInvulnerable(GetEntity('FightScene1(leader)'), TRUE);

		{ Init running guy }
{		RunScript('leader(leader)', 'InitRunner');}
		
		RunScript('buttonOpenGate_(S)', 'BlinkGreen');
		EnableUseable(GetEntity('buttonOpenGate_(S)'), TRUE);
		
		if GetEntity('ExecTut(hunter)') <> NIL then
			Runscript('ExecTut(hunter)', 'Init');
		    
{    Runscript('DeadLiftGuy(hunter)', 'Init');}
    
		SetVector(pos, -29.4144, 5.89855, 53.3413);
		SetVector(pos2, -25.7069, 7.61538, 53.7227);
		CreateBoxTrigger(pos, pos2, 'triggerPickman');
		
		SetVector(pos, -30.764, 5.89855, 55.2919);
		SetVector(pos2, -30.2079, 7.61538, 58.8733);
		CreateBoxTrigger(pos, pos2, 'triggerPickmanSecond');
		
		{SetVector(pos, -12.5526, 24.3186, 35.1171);
		SetVector(pos2, -9.525, 27.1686, 35.8971);
		CreateBoxTrigger(pos, pos2, 'triggerJumpTut3');}

		AiAddEntity('LiftLoonie01(hunter)');
		Runscript('LiftLoonie01(hunter)', 'Init');
		 
		sleep(1000);

		AIAddEntity('WackyRunner(hunter)');
		AIAddHunterToLeaderSubpack('FightScene2(leader)', 'fscene2', 'WackyRunner(hunter)');
		AIDefineGoalGotoNodeIdle('goalWackRun', 'WackyRunner(hunter)', AISCRIPT_HIGHPRIORITY, 'WACKYRUN', AISCRIPT_RUNMOVESPEED, TRUE);
		SetHunterRunSpeed('WackyRunner(hunter)', 1.2);
		AIMakeEntityBlind('WackyRunner(hunter)', 0);
		AiMakeEntityDeaf('WackyRunner(hunter)', 0);
		SetPedLockonable (GetEntity ('WackyRunner(hunter)'), FALSE);
		SetHunterExecutable(GetEntity ('WackyRunner(hunter)'), FALSE);
		AIAddGoalForSubpack('FightScene2(leader)', 'fscene2', 'goalWackRun');
		AISetHunterOnRadar('WackyRunner(hunter)', FALSE);
		
		SetVector(pos, -26.6741, 6.4347, 58.8435);
		CreateSphereTrigger(pos, 1.07284, 'triggerFallOver');
		TriggerAddEntityClass(GetEntity('triggerFallOver'), COL_HUNTER);
				
		while (NOT InsideTrigger(GetEntity('triggerFallOver'), GetEntity('WackyRunner(hunter)'))) do sleep(1);
		
		AiSetEntityIdleOverride('WackyRunner(hunter)', TRUE, TRUE);		
		
		AiEntityPlayAnim('WackyRunner(hunter)', 'HIT_FRONT_KNOCKOUT_LAUNCH');
		sleep(633);
		AIEntityCancelAnim('WackyRunner(hunter)', 'HIT_FRONT_KNOCKOUT_LAUNCH');
		AiEntityPlayAnim('WackyRunner(hunter)', 'HIT_FRONT_KNOCKOUT_LAND');
		sleep(1666);
		
{		KilLEntity(GetEntity('WackyRunner(hunter)'));}
		KilLEntityWithoutANim(GetEntity('WackyRunner(hunter)'));
		SetPedDoNotDecay(GetEntity('WackyRunner(hunter)'), TRUE); 
		
    end;

	BeforeBeasts: begin
		WriteDebug('Skipping to state: BeforeBeasts');

		{ Set player angle }
		SetPlayerHeading(90.0);

		{ Set level goals }
    ClearAllLevelGoals;
    SetLevelGoal('GOAL10');

		{Get rid of entity so it doesn't stand in the T-pose}
		if GetEntity('DeadLiftGuy(hunter)') <> NIL then
			DestroyEntity(GetEntity('DeadLiftGuy(hunter)'));

		{LIGHTS}
		RunScript('player(player)', 'FlickeringLightsLeft');
		RunScript('player(player)', 'FlickeringLightsRight');
		RunScript('player(player)', 'FlickeringLightsHall');
		
    { Create triggers }
		SetVector(pos, 4.53198, 24.1012, -1.43828);
		SetVector(pos2, 5.17104, 27.4754, 2.40105);
		CreateBoxTrigger(pos, pos2, 'triggerEnteringCellArea');

		SetVector(pos, -10.1035, 18.0687, 13.0312);
		SetVector(pos2, -6.10354, 21.0687, 14.0312);
		CreateBoxTrigger(pos, pos2, 'triggerSavePoint3');

    SetVector(pos, 6.23484, 19.0737, 3.53492);
    SetVector(pos2, 31.2348, 22.0737, 10.5349);
    CreateBoxTrigger(pos, pos2, 'triggerBeastArea');

		{SetVector(pos, 15.0, 24.08, -1.23252);
		SetVector(pos2, 17.81, 27.5, 2.88717);
		CreateBoxTrigger(pos, pos2, 'triggerCellSounds');}

    SetVector(pos, -10.1718, 18.09, 11.38);
    SetVector(pos2, -6.12823, 20.59, 11.48);
    CreateBoxTrigger(pos, pos2, 'triggerSecuCam');
        
		SetVector(pos, 18.8352, 17.8278, 1.95659);
		SetVector(pos2, 24.2485, 20.4149, 11.914);
		CreateBoxTrigger(pos, pos2, 'triggerMadman');
		
		SetVector(pos, 13.3311, 17.8278, 1.95659);
		SetVector(pos2, 18.7445, 20.4149, 11.914);
		CreateBoxTrigger(pos, pos2, 'triggerMadmanOtherSide');
			
		SetEntityScriptsFromEntity('triggerMadman', 'triggerMadmanOtherSide');

		{ Boxtrigger triggerDoor1Bugfix }
		SetVector(pos, 6.62871, 17.8278, 1.75262);
		SetVector(pos2, 8.2053, 20.4149, 2.09262);
		CreateBoxTrigger(pos, pos2, 'triggerDoor1Bugfix');
		
		{ Boxtrigger triggerDoor2Bugfix }
		SetVector(pos, 12.6451, 17.8278, 1.7842);
		SetVector(pos2, 14.2217, 20.4149, 2.1242);
		CreateBoxTrigger(pos, pos2, 'triggerDoor2Bugfix');
		
		{ Boxtrigger triggerDoor3Bugfix }
		SetVector(pos, 18.632, 17.8278, 1.8851);
		SetVector(pos2, 20.2086, 20.4149, 2.2251);
		CreateBoxTrigger(pos, pos2, 'triggerDoor3Bugfix');
		
		{ Boxtrigger triggerDoor4Bugfix }
		SetVector(pos, 24.5805, 17.8278, 1.81771);
		SetVector(pos2, 26.1571, 20.4149, 2.15771);
		CreateBoxTrigger(pos, pos2, 'triggerDoor4Bugfix');
		
		SetVector(pos, 5.51342, 18.5139, 3.09539);
		SetVector(pos2, 27.217, 19.7435, 11.4264);
		CreateBoxTrigger(pos, pos2, 'triggerStartLightning');
		
		SetVector(pos, 6.86906, 17.9561, 1.30207);
		SetVector(pos2, 8.09926, 18.7576, 3.13897);
		CreateBoxTrigger(pos, pos2, 'triggerLightReveal');
		
		SetVector(pos, -7.00874, 25.6269, -6.44073);
		CreateSphereTrigger(pos, 1.47317, 'triggerBarBeating');
		RunScript('triggerBarBeating', 'OnEnterTrigger');
		
{		HideEntity(GetEntity('BarCrazy01(hunter)'));}

    { Initialize hunters }
	IF GetEntity('Beast1(hunter)') <> NIL then
	begin
    	AIAddEntity('Beast1(hunter)');
    	RunScript('Beast1(hunter)', 'Init');
    end;
    IF GetEntity('Beast2(hunter)') <> NIL then
   	begin
    	AIAddEntity('Beast2(hunter)');
    	RunScript('Beast2(hunter)', 'Init');
    end;
    
    AIAddEntity('DeadIntroNurse(hunter)');
    KillEntity(GetEntity('DeadIntroNurse(hunter)'));
    
    If GetEntity('LiftLoonie01(hunter)') <> NIL then
    begin
    	AIAddEntity('LiftLoonie01(hunter)');
    	AIAddHunterToLeaderSubpack('leader2(leader)', 'subBeast1', 'LiftLoonie01(hunter)');
    	AISetHunterIdlePatrol('LiftLoonie01(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_HIGHPRIORITY, 2000, 5000,'pathLoonie1');
			SetVector(pos, -15, 24.09, 24);
			MoveEntity(GetEntity('LiftLoonie01(hunter)'), pos, 0);
		end;
    
    {Close the elevator doors (fixes bug)}

		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUR_(SD)');
		SetEntityScriptsFromEntity('Upt_Elevator_DoorIL_(SD)', 'Upt_Elevator_DoorUL_(SD)');
    RunScript('Upt_Elevator_DoorIL_(SD)', 'Close');
		RunScript('Upt_Elevator_DoorIR_(SD)', 'Close');
		RunScript('buttonInsideElev(S)', 'StopWorking');
		EnableUseable(GetEntity('buttonInsideElev(S)'), FALSE);
{		RunScript('Upt_Elevator_DoorUL_(SD)', 'Close');
		RunScript('Upt_Elevator_DoorUR_(SD)', 'Close');}

        { Turn on red lights }
{    	RunScript('buttonBeastCell_(S)', 'BlinkRed');}
    end;

	SpottedByCamera: begin
		WriteDebug('Skipping to state: SpottedByCamera');

		{ Set player angle }
		SetPlayerHeading(0.0);

		{ Set level goals }
		ClearAllLevelGoals;
		SetLevelGoal('GOAL10');
		
		{LIGHTS}
		RunScript('player(player)', 'FlickeringLightsLeft');
		RunScript('player(player)', 'FlickeringLightsRight');
		RunScript('player(player)', 'FlickeringLightsHall');
		
		{ Open doors }
		UnFreezeEntity(GetEntity('asylum_doorA_SL(D)'));
		UnLockEntity(GetEntity('asylum_doorA_SL(D)'));
		GraphModifyConnections(GetEntity('asylum_doorA_SL(D)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);

		UnFreezeEntity(GetEntity('asylum_doorA_SR(D)'));
		UnLockEntity(GetEntity('asylum_doorA_SR(D)'));
		GraphModifyConnections(GetEntity('asylum_doorA_SR(D)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);

		checkOpenerNeeded := FALSE;

		{ Create triggers }
		SetVector(pos, 1.13528, 22.4246, 46.9438);
		SetVector(pos2, 4.90758, 24.9246, 47.3238);
		CreateBoxTrigger(pos, pos2, 'triggerNearingTVArea');

		SetVector(pos, 13.9248, 15.4573, 46.5688);
		SetVector(pos2, 17.7029, 18.4573, 50.2312);
		CreateBoxTrigger(pos, pos2, 'triggerCarparkStart');
		
		{SetVector(pos, -4.62544, 17.9702, 17.0601);
		SetVector(pos2, -2.27975, 18.9197, 18.3229);
		CreateBoxTrigger(pos, pos2, 'triggerRadioChatter');}

		AIAddEntity('RadioHunter(hunter)');
		AIAddHunterToLeaderSubpack('leader(leader)', 'subRadioMan', 'RadioHunter(hunter)');
		AISetIdleHomeNode('RadioHunter(hunter)', 'RADIOHOME');
		AISetHunterIdleActionMinMax('RadioHunter(hunter)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_LOWPRIORITY, 2000, 5000);

		AIAddEntity('RadioHunterTwo(hunter)');
		AIAddHunterToLeaderSubpack('leader(leader)', 'subRadioMan', 'RadioHunterTwo(hunter)');
		AISetHunterIdleActionMinMax('RadioHunterTwo(hunter)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_LOWPRIORITY, 2000, 5000);
				
		{ Initialize hunters }
		SetEntityScriptsFromEntity('TVLoonie(hunter)', 'TVLoonie01(hunter)');
		SetEntityScriptsFromEntity('TVLoonie(hunter)', 'TVLoonie02(hunter)');
  	
		UnLockEntity(GetEntity('gate02(SD)'));
		GraphModifyConnections(GetEntity('gate02(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);
		SetDoorState(GetEntity('gate02(SD)'),DOOR_CLOSING);
	
		UnLockEntity(GetEntity('gate01(SD)'));
		GraphModifyConnections(GetEntity('gate01(SD)'), AISCRIPT_GRAPHLINK_ALLOW_NOTHING);
		SetDoorState(GetEntity('gate01(SD)'),DOOR_CLOSING);
		
  end;

	InCarPark: begin
		WriteDebug('Skipping to state: InCarPark');

		{ Set player angle }
		SetPlayerHeading(180.0);
		
		killedATruckman := FALSE;

		{ Set level goals }
		ClearAllLevelGoals;
{		SetLevelGoal('GOAL15');}
		{SetLevelGoal('GOAL13');}
		{SetLevelGoal('GOAL12');}

        { Destroy unnecessary hunters }
		if GetEntity('Beast1(hunter)') <> NIL then DestroyEntity(GetEntity('Beast1(hunter)'));
		if GetEntity('Beast2(hunter)') <> NIL then DestroyEntity(GetEntity('Beast2(hunter)'));

		{ [gupi] Added script copying. Hunter scripts were not copied, so the reload didn't work. (B*17490) }
    { Initialize carpark hunters }
		SetEntityScriptsFromEntity('TruckGuard(hunter)', 'TruckGuard1(hunter)');
		RunScript('TruckGuard1(hunter)', 'Init');
		SetEntityScriptsFromEntity('TruckGuard(hunter)', 'TruckGuard2(hunter)');
		RunScript('TruckGuard2(hunter)', 'Init');
			
		AIAddEntity('DriverEndScene(hunter)');
		AIAddHunterToLeaderSubpack('leader(leader)', 'subMeleeTut', 'DriverEndScene(hunter)');
			
{		RunScript('player(player)', 'CarParkSpotlight');}
{		RunScript('player(player)', 'CarParkPuzzle');}
		
		SetVector(pos, 30.2101, 17.1234, 37.434);
		CreateSphereTrigger(pos, 0.8, 'triggerEnteringTruck');
		
		SetVector(pos, 25.4899, 16.0642, 35.5);
		SetVector(pos2, 48.7999, 19.0033, 51.0277);
		CreateBoxTrigger(pos, pos2, 'triggerDeadNearTruck');
		
		if GetEntity('DriverEndScene(hunter)') <> NIL then
			HideEntity(GetEntity('DriverEndScene(hunter)'));
		
		SetLevelGoal('GOAL15');
		
		RadarPositionSetEntity(GetEntity('triggerEnteringTruck'), MAP_COLOR_BLUE);
		
		HideEntity(GetEntity('PFXRainWindow'));
		HideEntity(GetEntity('PFXRainWindow01'));
		HideEntity(GetEntity('PFXRainWindow02'));
		HideEntity(GetEntity('PFXRainWindow03'));
		HideEntity(GetEntity('PFXRainWindow04'));

		{EntityPlayAnim(GetEntity('PFX_rainco'), 'PAT_rainco', true);
		EntityPlayAnim(GetEntity('PFX_rainpa'), 'PAT_rainpa', true);}
		
		{SetVector(pos, 31.1342, 18.2325, 37.5213);
		CreateSphereTrigger(pos, 1.53063, 'triggerTruckSee');}
		
    end;
    
    

	end; {case of}
	
	{DOORS}
	UnLockEntity(GetEntity('cell1_(SD)'));
	SetSlideDoorAjarDistance(GetEntity('cell1_(SD)'), 1.1);
	SetDoorState(GetEntity('cell1_(SD)'), DOOR_OPENING);
	GraphModifyConnections(GetEntity('cell1_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
	RunScript('ButtonCell1_(S)', 'BlinkGreen');
	SetCurrentLod(GetEntity('Door_Indicator1'), 1);
	
	UnLockEntity(GetEntity('cell4_(SD)'));
	SetSlideDoorAjarDistance(GetEntity('cell4_(SD)'), 1.1);
	SetDoorState(GetEntity('cell4_(SD)'), DOOR_OPENING);
	GraphModifyConnections(GetEntity('cell4_(SD)'), AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
	RunScript('ButtonCell4_(S)', 'BlinkGreen');
	SetCurrentLod(GetEntity('Door_Indicator4'), 1);
	
	UnLockEntity(GetEntity('cell2_(SD)'));
	UnLockEntity(GetEntity('cell5_(SD)'));
	SetDoorState(GetEntity('cell2_(SD)'), DOOR_OPENING);
	SetDoorState(GetEntity('cell5_(SD)'), DOOR_OPENING);
	
	while GetDoorState(GetEntity('cell2_(SD)')) <> DOOR_OPEN do sleep(10);
	while GetDoorState(GetEntity('cell5_(SD)')) <> DOOR_OPEN do sleep(10);

	sleep(100);
		
	SetDoorState(GetEntity('cell2_(SD)'), DOOR_CLOSING);
	SetDoorState(GetEntity('cell5_(SD)'), DOOR_CLOSING);
	
	{UnLockEntity(GetEntity('cell5_(SD)'));
	SetSlideDoorAjarDistance(GetEntity('cell5_(SD)'), 1.1);
	SetDoorState(GetEntity('cell5_(SD)'), DOOR_CLOSING);
	RunScript('ButtonCell5_(S)', 'BlinkRed');
	SetCurrentLod(GetEntity('Door_Indicator5'), 0);}
	
end;

script SkipMe;
begin
end;

procedure InitAI;
var
	pos : Vec3D;

begin
	{ Set up player for AI }
	AIAddPlayer('player(player)');
	{ Set up leaders }
	AIAddEntity('leader(leader)');

	AISetHunterOnRadar('leader(leader)', FALSE);
	AISetEntityAsLeader('leader(leader)');
	AISetLeaderInvisible('leader(leader)');
	AIAddLeaderEnemy('leader(leader)', 'player(player)');
	AIEntityAlwaysEnabled('leader(leader)');

	AIAddEntity('leader2(leader)');

	AISetHunterOnRadar('leader2(leader)', FALSE);
	AISetEntityAsLeader('leader2(leader)');
	AISetLeaderInvisible('leader2(leader)');
	AIEntityAlwaysEnabled('leader2(leader)');
	AIAddLeaderEnemy('leader2(leader)', 'player(player)');
	

	{ Set up subpacks }
	AIAddSubPackForLeader('leader(leader)', 'subMeleeTut');
	AIAddSubPackForLeader('leader(leader)', 'subExecTut');
	{AIAddSubPackForLeader('leader(leader)', 'subJumpChase');
	AIAddSubPackForLeader('leader(leader)', 'subBeastVictims');}
	AIAddSubPackForLeader('leader2(leader)', 'subBeast2');
	AIAddSubPackForLeader('leader(leader)', 'subRunner');
	AIAddSubPackForLeader('leader(leader)', 'subJumper');
	{AIAddSubPackForLeader('leader2(leader)', 'subTVGuys');}
	AIAddSubPackForLeader('leader(leader)', 'subTVLoonies');
	{AIAddSubPackForLeader('leader(leader)', 'subTVFight');}

	AIAddSubPackForLeader('leader2(leader)', 'subBeast1');
	AIAddSubPackForLeader('leader(leader)', 'subTruckGuards');
	AIAddSubPackForLeader('leader2(leader)', 'subTVGuard');
	AIAddSubPackForLeader('leader(leader)', 'subManWoman');
	AIAddSubPackForLeader('leader(leader)', 'subStealthTut1');
	AIAddSubPackForLeader('leader(leader)', 'subStealthTut2');
	AIAddSubPackForLeader('leader(leader)', 'subStealthTut3');
	
	AIAddSubPackForLeader('leader(leader)', 'subRadioMan');
	
	AIAddSubpackForLeader('leader2(leader)', 'subMutters');

	{ Assign areas to subpacks }
	{AIAddAreaForSubPack('subMeleeTut', 'aipastelev');}
	{AIAddAreaForSubPack('subManWoman', 'aicell');}
{	AIAddAreaForSubPack('subExecTut', 'aielevator');}
	{AIAddAreaForSubPack('subJumpChase', 'aipastelev');}
	{AIAddAreaForSubPack('subBeastVictims', 'aimelee2');}
	{AIAddAreaForSubPack('subBeast2', 'aimelee2');}
	{AIAddAreaForSubPack('subRunner', 'aipastelev');
	AIAddAreaForSubPack('subJumper', 'aipastelev');}
	{AIAddAreaForSubPack('subTVGuys', 'airecreation');}
	{AIAddAreaForSubPack('subTVFight', 'airecreation');}
	{AIAddAreaForSubPack('subBeast1', 'aimelee2');}
	{AIAddAreaForSubPack('subTruckGuards', 'aicarpark');
	AIAddAreaForSubPack('subTVGuard', 'aipostbeasts');
	AIAddAreaForSubPack('subTVGuard', 'airecreation');}

{	AISubPackStayInTerritory('leader(leader)', 'subManWoman', true);
	AISetBoundaryIntercept('leader(leader)', 'subManWoman',0.1);}

	{AISubpackStayInTerritory('leader(leader)', 'subBeastVictims', TRUE);}
	{AISubpackStayInTerritory('leader2(leader)', 'subBeast1', TRUE);}
	{AISubpackStayInTerritory('leader2(leader)', 'subBeast2', TRUE);}

	{ Set up subpack combat types }
	AISetSubpackCombatType('leader2(leader)', 'subMutters', COMBATTYPEID_OPEN_MELEE);
	AISetSubpackCombatType('leader(leader)', 'subStealthTut1', COMBATTYPEID_COVER);
	AISetSubpackCombatType('leader(leader)', 'subStealthTut2', COMBATTYPEID_COVER);
	AISetSubpackCombatType('leader(leader)', 'subStealthTut3', COMBATTYPEID_COVER);
	AISetSubpackCombatType('leader(leader)', 'subExecTut', COMBATTYPEID_OPEN_MELEE);
	AISetSubpackCombatType('leader(leader)', 'subManWoman', COMBATTYPEID_OPEN_MELEE);
{	AISetSubpackCombatType('leader(leader)', 'subJumpChase', COMBATTYPEID_OPEN_MELEE);
	AISetSubpackCombatType('leader(leader)', 'subBeastVictims', COMBATTYPEID_OPEN_MELEE);}
	AISetSubpackCombatType('leader(leader)', 'subTVLoonies', COMBATTYPEID_OPEN_MELEE);
	{AISetSubpackCombatType('leader(leader)', 'subTVFight', COMBATTYPEID_OPEN_MELEE);}
	AISetSubpackCombatType('leader2(leader)', 'subBeast2', COMBATTYPEID_OPEN_MELEE);

	AISetSubpackCombatType('leader2(leader)', 'subBeast1', COMBATTYPEID_OPEN_MELEE);
	AISetSubpackCombatType('leader(leader)', 'subTruckGuards', COMBATTYPEID_OPEN_MELEE);
	AISetSubpackCombatType('leader2(leader)', 'subTVGuard', COMBATTYPEID_OPEN_MELEE);
	
	AISetSubPackCombatType('leader(leader)', 'subRadioMan', COMBATTYPEID_OPEN_MELEE);

	{ Set up player hunting }
	AIDefineGoalHuntEnemy('goalHuntPlayer', 'player(player)', true, 10);

	AIAddGoalForSubpack('leader2(leader)', 'subMutters', 'goalHuntPlayer');	

	AIAddGoalForSubpack('leader(leader)', 'subStealthTut1', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader(leader)', 'subStealthTut2', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader(leader)', 'subStealthTut3', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader(leader)', 'subExecTut', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader(leader)', 'subManWoman', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader2(leader)', 'subBeast1', 'goalHuntPlayer');
	{AIAddGoalForSubpack('leader(leader)', 'subTVFight', 'goalHuntPlayer');}

	AIAddGoalForSubpack('leader2(leader)', 'subTVGuard', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader(leader)', 'subTruckGuards', 'goalHuntPlayer');
	AIAddGoalForSubpack('leader(leader)', 'subRadioMan',  'goalHuntPlayer');

	writedebug('here');
	
	AIInited := TRUE;
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
'0c000000', //Offset in byte
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'5b010000', //aiaddplayer Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
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
'10160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'6d020000', //aisetleaderinvisible Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'00160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'6d020000', //aisetleaderinvisible Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'34160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'44160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'5c160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'68160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'74160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'90160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'ac160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'bc160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'cc160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'dc160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'ec160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'bc160000', //Offset in byte
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
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'cc160000', //Offset in byte
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
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'dc160000', //Offset in byte
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
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'44160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'ac160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'74160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'90160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'ec160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08170000', //Offset in byte
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
'00160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
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
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'bc160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'cc160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'dc160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'44160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'ac160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20160000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'90160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10160000', //Offset in byte
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
'ec160000', //Offset in byte
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
'08170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18170000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'98170000', //unknown
'01000000', //unknown
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'04000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'1c000000', //Offset in byte
//	SetColourRamp('FE_colramps', 1, 4.0);

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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'ab030000', //SetColourRamp Call

'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1d000000', //value 29
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4c030000', //SetNextLevelByName Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'60170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'64170000', //LevelVar lButtonTutRemoved
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'20170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'80170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'6c170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'94170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'ec170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'a0170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'a4170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'90170000', //unknown
'01000000', //unknown

'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'20170000', //Offset
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
'40160000', //Offset (line number 1424)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'58160000', //Offset (line number 1430)
'e7000000', //KillThisScript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'62030000', //EnableAction Call
'c3030000', //FakeHunterDestroyAll Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2d000000', //value 45
'10000000', //nested call return result
'01000000', //nested call return result
'59030000', //SetMaxScoreForLevel Call

#AIInited := FALSE;
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0

'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'98170000', //unknown
'01000000', //unknown

#InitAI;
'10000000', //unknown
'04000000', //unknown
'11000000', //unknown
'02000000', //unknown
'00000000', //unknown
'32000000', //unknown
'02000000', //unknown
'1c000000', //unknown
'10000000', //unknown
'02000000', //unknown
'39000000', //unknown
'00000000', //unknown

#SetMaxNumberOfRats(0);
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'a8020000', //setmaxnumberofrats Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'a4020000', //switchlitteron Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c842', //value 1120403456
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'ac030000', //setqtmbaseprobability Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //Offset in byte
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
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //Offset in byte
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
'98000000', //LockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98000000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac000000', //Offset in byte
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
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac000000', //Offset in byte
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
'98000000', //LockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac000000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'98000000', //LockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8000000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8000000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'e8000000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'e8000000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'e8000000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'34010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'e8000000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48010000', //Offset in byte
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
'58010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48010000', //Offset in byte
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
'68010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28170000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'04000000', //unknown
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'e0220000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'2c230000', //Offset (line number 2251)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28170000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'04000000', //unknown
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'c4230000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f4230000', //Offset (line number 2301)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'04000000', //unknown
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'8c240000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'bc240000', //Offset (line number 2351)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'04000000', //unknown
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'54250000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'84250000', //Offset (line number 2401)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28170000', //Offset
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
'dc250000', //Offset (line number 2423)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'70260000', //Offset (line number 2460)

'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'24170000', //LevelVar lLevelState
'01000000', //unknown


'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc010000', //Offset in byte
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
'e0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'3c000000', //statement (init statement start offset)
'cc260000', //Offset (line number 2483)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc010000', //Offset in byte
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
'f4010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08020000', //Offset in byte
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
'1c020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
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
'34000000', //reserve bytes
'09000000', //reserve bytes
'44000000', //Offset in byte
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'24170000', //Offset
'24000000', //unknown
'01000000', //unknown
'0b000000', //unknown
'3f000000', //statement (init start offset)
'78280000', //Offset (line number 2590)
'24000000', //unknown
'01000000', //unknown
'0a000000', //unknown
'3f000000', //statement (init start offset)
'84280000', //Offset (line number 2593)
'24000000', //unknown
'01000000', //unknown
'08000000', //unknown
'3f000000', //statement (init start offset)
'34370000', //Offset (line number 3533)
'24000000', //unknown
'01000000', //unknown
'07000000', //unknown
'3f000000', //statement (init start offset)
'6c410000', //Offset (line number 4187)
'24000000', //unknown
'01000000', //unknown
'06000000', //unknown
'3f000000', //statement (init start offset)
'38510000', //Offset (line number 5198)
'24000000', //unknown
'01000000', //unknown
'05000000', //unknown
'3f000000', //statement (init start offset)
'c8670000', //Offset (line number 6642)
'24000000', //unknown
'01000000', //unknown
'02000000', //unknown
'3f000000', //statement (init start offset)
'c06c0000', //Offset (line number 6960)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'08730000', //Offset (line number 7362)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'78780000', //Offset (line number 7710)
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'04020000', //unknown
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'500c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call


'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'30290000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'80290000', //Offset (line number 2656)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80a0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'080b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'f8290000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'482a0000', //Offset (line number 2706)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'080b0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'c02a0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'102b0000', //Offset (line number 2756)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280c0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'882b0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd82b0000', //Offset (line number 2806)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280c0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'502c0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a02c0000', //Offset (line number 2856)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700c0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'840c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'182d0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'682d0000', //Offset (line number 2906)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'840c0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'980c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'e02d0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'302e0000', //Offset (line number 2956)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'980c0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'a82e0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f82e0000', //Offset (line number 3006)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'702f0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c02f0000', //Offset (line number 3056)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0070000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b00c0000', //Offset in byte
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
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e80c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc0c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'e0170000', //unknown
'01000000', //unknown
'77030000', //GetAmbientAudioTrack Call
'16000000', //unknown
'04000000', //unknown
'2c170000', //unknown
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'100d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280d0000', //Offset in byte
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
'380d0000', //Offset in byte
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
'100d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'100d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'100d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
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
'82020000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49aef141', //value 1106357833
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b9fc8841', //value 1099496633
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6abc1542', //value 1108720746
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc4c3f', //value 1061997773
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'480d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'51ebcb41', //value 1103883089
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b838041', //value 1098941307
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000e42', //value 1108213760
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'19334342', //value 1111700249
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c2069841', //value 1100482242
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5d1c4c42', //value 1112284253
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'600d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0060000', //Offset in byte
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
'780d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'f5020000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e8340000', //Offset (line number 3386)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'b0340000', //Offset (line number 3372)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c0d0000', //Offset in byte
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
'a00d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a00d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c0d0000', //Offset in byte
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
'b80d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd00d0000', //Offset in byte
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'47030000', //unknown
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'480d0000', //Offset in byte
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
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //unknown
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'24000000', //value 36
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'580b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'e0370000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'30380000', //Offset (line number 3596)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'580b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'a8380000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f8380000', //Offset (line number 3646)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c0b0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'180b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'70390000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c0390000', //Offset (line number 3696)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'180b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'383a0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'883a0000', //Offset (line number 3746)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c0b0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'003b0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'503b0000', //Offset (line number 3796)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c0b0000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'84170000', //LevelVar checkOpenerNeeded
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a40b0000', //Offset in byte
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80b0000', //Offset in byte
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a40b0000', //Offset in byte
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
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80b0000', //Offset in byte
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
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'db50913f', //value 1066488027
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9565b341', //value 1102275989
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'74c63b42', //value 1111213684
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e50a9d40', //value 1084033765
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9565c741', //value 1103586709
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'924b3d42', //value 1111313298
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e40b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fbcb5e41', //value 1096731643
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1a517741', //value 1098338586
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'74463a42', //value 1111115380
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8a9f8d41', //value 1099800458
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8da89341', //value 1100195981
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c0ec4842', //value 1112075456
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c0c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'84170000', //LevelVar checkOpenerNeeded
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'21000000', //value 33
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'77030000', //GetAmbientAudioTrack Call
'16000000', //unknown
'04000000', //unknown
'2c170000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'180a0000', //Offset in byte
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'47030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c090000', //Offset in byte
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
'7c420000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'cc420000', //Offset (line number 4275)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c090000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
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
'44430000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'94430000', //Offset (line number 4325)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
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
'0c440000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'5c440000', //Offset (line number 4375)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fb059140', //value 1083246075
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'42cfc041', //value 1103155010
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8f19b83f', //value 1069029775
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2979a540', //value 1084586281
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9fcddb41', //value 1104924063
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ceaa1940', //value 1075423950
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f0a72141', //value 1092724720
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
'b38c9041', //value 1099992243
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cc7f5041', //value 1095794636
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3350c340', //value 1086541875
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
'b38ca841', //value 1101565107
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cc7f6041', //value 1096843212
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'440a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cf83c740', //value 1086817231
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f0969841', //value 1100519152
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'213c6240', //value 1080179745
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dfe0f941', //value 1106895071
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f096b041', //value 1102092016
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f38e2841', //value 1093177075
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'580a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b1bf2241', //value 1092796337
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
'52b89041', //value 1100003410
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b143641', //value 1094063227
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'761ac440', //value 1086593654
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
'52b8a441', //value 1101314130
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'14ae3741', //value 1094168084
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'641ed440', //value 1087643236
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'da55e03f', //value 1071666650
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e9480341', //value 1090734313
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7ced0540', //value 1074130300
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'54524a41', //value 1095389780
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'aa60e43f', //value 1071931562
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'158c6341', //value 1097042965
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e5f20740', //value 1074262757
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'900a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'560e9541', //value 1100287574
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f54af13f', //value 1072777973
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'36aba141', //value 1101114166
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a680e40', //value 1074685962
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a40a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dda4c441', //value 1103406301
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b9aae83f', //value 1072212665
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'be41d141', //value 1104232894
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ec170a40', //value 1074403308
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f06db040', //value 1085304304
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'781c9441', //value 1100225656
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'df1a4640', //value 1078336223
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6abcd941', //value 1104788586
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b0f29d41', //value 1100870320
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'89d23641', //value 1094111881
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'57cfdb40', //value 1088147287
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'18a68f41', //value 1099933208
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3baaa63f', //value 1067887163
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'92960141', //value 1090623122
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'910f9641', //value 1100353425
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e2e44840', //value 1078519010
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e40a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80a0000', //Offset in byte
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
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'080b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'080b0000', //Offset in byte
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
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'180b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'b1020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'180b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'80000000', //unknown
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'21000000', //value 33
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'e4510000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'34520000', //Offset (line number 5261)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c080000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'ac520000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'fc520000', //Offset (line number 5311)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00050000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'74530000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c4530000', //Offset (line number 5361)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44080000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'3c540000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'8c540000', //Offset (line number 5411)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c080000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'0f000000', //unknown
'04000000', //unknown
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'40000000', //statement (core)(operator un-equal)
'04550000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'54550000', //Offset (line number 5461)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14050000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0060000', //Offset in byte
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
'74080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0060000', //Offset in byte
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
'8c080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0060000', //Offset in byte
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
'a4080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2c010000', //value 300
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'7f020000', //togglehudflag Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'24030000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cec73740', //value 1077397454
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
'3cbdc741', //value 1103609148
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'bdc6cf40', //value 1087358653
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8fc2f53f', //value 1073070735
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'89c1cc41', //value 1103937929
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
'c74bc141', //value 1103186887
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'42a00742', //value 1107796034
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'89c1c841', //value 1103675785
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
'c74bd941', //value 1104759751
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'42a02342', //value 1109631042
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2e4a7441', //value 1098140206
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
'f98fc941', //value 1103728633
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fe431542', //value 1108689918
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c03f', //value 1069547520
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'73d74841', //value 1095292787
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
'7e8cc241', //value 1103268990
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e9770c42', //value 1108113385
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'66661841', //value 1092118118
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
'4b59d941', //value 1104763211
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a1960f42', //value 1108317857
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fc298441', //value 1099180540
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
'f98fc941', //value 1103728633
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fe431542', //value 1108689918
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c03f', //value 1069547520
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9947e040', //value 1088440217
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
'e403cd41', //value 1103954916
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'761ace40', //value 1087249014
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd690bc3f', //value 1069322454
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24090000', //Offset in byte
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
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50090000', //Offset in byte
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
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'5c090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c090000', //Offset in byte
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
'5e010000', //SetEntityInvulnerable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c090000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74090000', //Offset in byte
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
'90090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0090000', //Offset in byte
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
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'1e030000', //SetPedHurtOtherPeds Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'1e030000', //SetPedHurtOtherPeds Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50090000', //Offset in byte
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
'cc090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0090000', //Offset in byte
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
'e0090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38090000', //Offset in byte
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
'5e010000', //SetEntityInvulnerable Call
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'23000000', //value 35
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'9c170000', //unknown
'01000000', //unknown
'77030000', //GetAmbientAudioTrack Call
'16000000', //unknown
'04000000', //unknown
'2c170000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'64070000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78070000', //Offset in byte
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'47030000', //unknown
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98070000', //Offset in byte
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
'ac070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b150eb41', //value 1105940657
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
'ecc0bc40', //value 1086111980
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7e5d5542', //value 1112890750
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'bba7cd41', //value 1103996859
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
'31b1f340', //value 1089712433
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0be45642', //value 1112990731
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ac1cf641', //value 1106648236
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
'ecc0bc40', //value 1086111980
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e82a5d42', //value 1113402088
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c7a9f141', //value 1106356679
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
'31b1f340', //value 1089712433
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'427e6b42', //value 1114340930
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0070000', //Offset in byte
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
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'9c170000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1d000000', //value 29
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0060000', //Offset in byte
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
'00070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8020000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'6c170000', //Offset
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
'e46d0000', //Offset (line number 7033)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'00730000', //Offset (line number 7360)

'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18070000', //Offset in byte
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
'706e0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f06e0000', //Offset (line number 7100)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'28070000', //Offset in byte
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
'30070000', //Offset in byte
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
'e1020000', //RadarPositionClearEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'6c170000', //unknown
'01000000', //unknown

'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'24000000', //value 36
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'6c170000', //Offset
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
'94730000', //Offset (line number 7397)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'70780000', //Offset (line number 7708)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'6c170000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'21000000', //value 33
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'53965241', //value 1095931475
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5d6d6641', //value 1097231709
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a5c91d41', //value 1092471205
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
'00002040', //value 1075838976
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5d6d6e41', //value 1097755997
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'64020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'53965241', //value 1095931475
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2dc38c41', //value 1099744045
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a5c91d41', //value 1092471205
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
'00002040', //value 1075838976
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2dc39041', //value 1100006189
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'683f7c40', //value 1081884520
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
'cb2de13f', //value 1071721931
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'795dfb3f', //value 1073438073
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b2ba3340', //value 1077131954
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
'e5edaa40', //value 1084943845
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'96b2a140', //value 1084338838
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'47c9d940', //value 1088014663
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
'6afb6140', //value 1080163178
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'931c803e', //value 1048583315
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'47c9b940', //value 1085917511
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
'b5fdc040', //value 1086389685
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cbf8df3f', //value 1071642827
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a4020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'59178f41', //value 1099896665
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
'1c99c640', //value 1086757148
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0de00142', //value 1107419149
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4bc86741', //value 1097320523
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
'8e4c0b41', //value 1091259534
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0de00342', //value 1107550221
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02ab8c41', //value 1099737858
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
'e3dfba40', //value 1085988835
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6a590e41', //value 1091459434
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'22fd5f41', //value 1096809762
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
'd5780241', //value 1090681045
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a8291841', //value 1092102568
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b29d0642', //value 1107729842
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
'a8a9653c', //value 1013295528
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b9cd2242', //value 1109577145
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1d090442', //value 1107560733
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
'18091540', //value 1075120408
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b4c83042', //value 1110493364
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ea045541', //value 1096090858
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'daacf741', //value 1106750682
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08ac1b41', //value 1092332552
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
'64231440', //value 1075061604
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1895fc41', //value 1107072280
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5af53742', //value 1110963546
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
'c8693e3c', //value 1010723272
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
'30aab441', //value 1102359088
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5af52b42', //value 1110177114
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
'07990a40', //value 1074436359
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3945b741', //value 1102529849
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1b8d4f42', //value 1112509723
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6abc8041', //value 1098955882
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08ec2742', //value 1109912584
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
'5a2ab340', //value 1085483610
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f4eca041', //value 1101065460
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'28030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8fa45142', //value 1112646799
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5c8fc541', //value 1103466332
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b032a42', //value 1110049659
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
'5a2ab340', //value 1085483610
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4625e741', //value 1105667398
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8fa45142', //value 1112646799
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'67440642', //value 1107706983
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b032a42', //value 1110049659
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
'5a2ab340', //value 1085483610
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6b2b1742', //value 1108814699
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'61142b42', //value 1110119521
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'43ad2242', //value 1109568835
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4d552842', //value 1109939533
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
'7bf78840', //value 1082718075
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'35de3042', //value 1110498869
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3d2ca641', //value 1101409341
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'99fb1f42', //value 1109392281
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'abcfa241', //value 1101189035
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
'289b3240', //value 1077058344
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6a4d3542', //value 1110789482
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ea045541', //value 1096090858
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'352f0a42', //value 1107963701
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08ac1b41', //value 1092332552
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
'64231440', //value 1075061604
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'54a30c42', //value 1108124500
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6fb05442', //value 1112846447
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e7a9c040', //value 1086368231
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8f532442', //value 1109676943
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
'c572ab3f', //value 1068200645
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a8573a41', //value 1094342568
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'34000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'40000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8030000', //Offset in byte
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
'2d010000', //setcurrentlod Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0030000', //Offset in byte
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
'2d010000', //setcurrentlod Call


'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8030000', //Offset in byte
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
'da000000', //SwitchLightOff
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'2d010000', //setcurrentlod Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'34040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'34170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'38170000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'3c170000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48040000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54020000', //Offset in byte
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
'74040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'f5020000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'40900000', //Offset (line number 9232)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'08900000', //Offset (line number 9218)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'b1020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98040000', //Offset in byte
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'76030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98040000', //Offset in byte
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
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0040000', //Offset in byte
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
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc040000', //Offset in byte
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
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00050000', //Offset in byte
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
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'f5020000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'9c940000', //Offset (line number 9511)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'64940000', //Offset (line number 9497)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48050000', //Offset in byte
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
'83000000', //hideentity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dc050000', //value 1500
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c050000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68050000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78050000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94050000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'68050000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c050000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac050000', //Offset in byte
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
'b4050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'28000000', //value 40
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'5e020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'ac050000', //Offset in byte
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
'b4050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'28000000', //value 40
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'5e020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'68050000', //Offset in byte
'12000000', //parameter (Read String var)
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc050000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'ac050000', //Offset in byte
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
'b4050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'28000000', //value 40
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'5e020000', //unknown
'cf020000', //IsScriptAudioStreamCompleted Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'509a0000', //Offset (line number 9876)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'0c9a0000', //Offset (line number 9859)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
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
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'69030000', //frisbeespeechisfinished Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'789b0000', //Offset (line number 9950)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'089b0000', //Offset (line number 9922)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f4010000', //value 500
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
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
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'69030000', //frisbeespeechisfinished Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'589c0000', //Offset (line number 10006)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'e89b0000', //Offset (line number 9978)
'3c000000', //statement (init statement start offset)
'609c0000', //Offset (line number 10008)
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
'c89c0000', //Offset (line number 10034)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'909c0000', //Offset (line number 10020)
'04030000', //unknown
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'bc9d0000', //Offset (line number 10095)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e80d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'07010000', //IsGameTextDisplaying Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'549d0000', //Offset (line number 10069)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'1c9d0000', //Offset (line number 10055)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f00d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'07010000', //IsGameTextDisplaying Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'bc9d0000', //Offset (line number 10095)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'849d0000', //Offset (line number 10081)
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
'089e0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'549e0000', //Offset (line number 10133)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc0d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'3c000000', //statement (init statement start offset)
'849e0000', //Offset (line number 10145)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c0e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'07010000', //IsGameTextDisplaying Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec9e0000', //Offset (line number 10171)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'b49e0000', //Offset (line number 10157)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
'07010000', //IsGameTextDisplaying Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'549f0000', //Offset (line number 10197)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'1c9f0000', //Offset (line number 10183)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'60170000', //unknown
'01000000', //unknown
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
'dc9f0000', //Offset (line number 10231)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'a49f0000', //Offset (line number 10217)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c0e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //displaygametext Call
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
'34000000', //reserve bytes
'09000000', //reserve bytes
'18000000', //Offset in byte
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2c010000', //value 300
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'7f020000', //togglehudflag Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'2c170000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'75030000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'24170000', //Offset
'24000000', //unknown
'01000000', //unknown
'0a000000', //unknown
'3f000000', //statement (init start offset)
'fca00000', //Offset (line number 10303)
'24000000', //unknown
'01000000', //unknown
'08000000', //unknown
'3f000000', //statement (init start offset)
'60a90000', //Offset (line number 10840)
'24000000', //unknown
'01000000', //unknown
'07000000', //unknown
'3f000000', //statement (init start offset)
'04b40000', //Offset (line number 11521)
'24000000', //unknown
'01000000', //unknown
'05000000', //unknown
'3f000000', //statement (init start offset)
'c4cc0000', //Offset (line number 13105)
'3c000000', //statement (init statement start offset)
'80ea0000', //Offset (line number 15008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1d000000', //value 29
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003443', //value 1127481344
'10000000', //nested call return result
'01000000', //nested call return result
'80020000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'e0170000', //unknown
'01000000', //unknown
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98120000', //Offset in byte
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
'e0a10000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'30a20000', //Offset (line number 10380)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98120000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8120000', //Offset in byte
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
'a8a20000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f8a20000', //Offset (line number 10430)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8120000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c140000', //Offset in byte
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
'90140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c140000', //Offset in byte
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
'a8140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4130000', //Offset in byte
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
'd8140000', //Offset in byte
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
'c0140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'49aef141', //value 1106357833
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b9fc8841', //value 1099496633
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6abc1542', //value 1108720746
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
'cdcc4c3f', //value 1061997773
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'51ebcb41', //value 1103883089
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b838041', //value 1098941307
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000e42', //value 1108213760
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'19334342', //value 1111700249
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c2069841', //value 1100482242
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5d1c4c42', //value 1112284253
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
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
'5ca70000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'aca70000', //Offset (line number 10731)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8140000', //Offset in byte
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
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20150000', //Offset in byte
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
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'3c000000', //statement (init statement start offset)
'80ea0000', //Offset (line number 15008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'23000000', //value 35
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'80020000', //unknown
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'480e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24110000', //Offset in byte
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
'34110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24110000', //Offset in byte
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
'4c110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24110000', //Offset in byte
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
'64110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54130000', //Offset in byte
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
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54130000', //Offset in byte
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
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54130000', //Offset in byte
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68130000', //Offset in byte
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
'38010000', //UnFreezeEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68130000', //Offset in byte
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
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68130000', //Offset in byte
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'84170000', //LevelVar checkOpenerNeeded
'01000000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'db50913f', //value 1066488027
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9565b341', //value 1102275989
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'74c63b42', //value 1111213684
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e50a9d40', //value 1084033765
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9565c741', //value 1103586709
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'924b3d42', //value 1111313298
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fbcb5e41', //value 1096731643
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1a517741', //value 1098338586
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'74463a42', //value 1111115380
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8a9f8d41', //value 1099800458
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8da89341', //value 1100195981
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c0ec4842', //value 1112075456
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4130000', //Offset in byte
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
'd4130000', //Offset in byte
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
'ac130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'03000000', //value 3
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
'80010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4130000', //Offset in byte
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
'd4130000', //Offset in byte
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
'f0130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
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
'03000000', //value 3
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
'80010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'3c000000', //statement (init statement start offset)
'80ea0000', //Offset (line number 15008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'80020000', //unknown
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'480e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c110000', //Offset in byte
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
'fcb40000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'4cb50000', //Offset (line number 11603)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c110000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24110000', //Offset in byte
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
'34110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24110000', //Offset in byte
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
'4c110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24110000', //Offset in byte
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
'64110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fb059140', //value 1083246075
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'42cfc041', //value 1103155010
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8f19b83f', //value 1069029775
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2979a540', //value 1084586281
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9fcddb41', //value 1104924063
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ceaa1940', //value 1075423950
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f0a72141', //value 1092724720
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
'b38c9041', //value 1099992243
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cc7f5041', //value 1095794636
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3350c340', //value 1086541875
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
'b38ca841', //value 1101565107
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cc7f6041', //value 1096843212
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cf83c740', //value 1086817231
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f0969841', //value 1100519152
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'213c6240', //value 1080179745
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dfe0f941', //value 1106895071
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f096b041', //value 1102092016
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f38e2841', //value 1093177075
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b1bf2241', //value 1092796337
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
'52b89041', //value 1100003410
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7b143641', //value 1094063227
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'761ac440', //value 1086593654
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
'52b8a441', //value 1101314130
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'14ae3741', //value 1094168084
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7dae9641', //value 1100394109
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8b71fa3f', //value 1073377675
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'eefcc141', //value 1103232238
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'be9f3e41', //value 1094623166
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'304c5541', //value 1096109104
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8b71fa3f', //value 1073377675
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'bcf49541', //value 1100346556
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'be9f3e41', //value 1094623166
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0110000', //Offset in byte
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
'e0110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'641ed440', //value 1087643236
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'da55e03f', //value 1071666650
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e9480341', //value 1090734313
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7ced0540', //value 1074130300
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'54524a41', //value 1095389780
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'aa60e43f', //value 1071931562
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'158c6341', //value 1097042965
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e5f20740', //value 1074262757
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'560e9541', //value 1100287574
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f54af13f', //value 1072777973
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'36aba141', //value 1101114166
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a680e40', //value 1074685962
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dda4c441', //value 1103406301
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'569f8e41', //value 1099865942
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b9aae83f', //value 1072212665
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'be41d141', //value 1104232894
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b751a341', //value 1101222327
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ec170a40', //value 1074403308
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'34120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f06db040', //value 1085304304
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'781c9441', //value 1100225656
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'df1a4640', //value 1078336223
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6abcd941', //value 1104788586
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b0f29d41', //value 1100870320
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'89d23641', //value 1094111881
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'57cfdb40', //value 1088147287
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'18a68f41', //value 1099933208
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3baaa63f', //value 1067887163
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'92960141', //value 1090623122
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'910f9641', //value 1100353425
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e2e44840', //value 1078519010
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9947e040', //value 1088440217
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
'e403cd41', //value 1103954916
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'761ace40', //value 1087249014
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
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
'd690bc3f', //value 1069322454
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74120000', //Offset in byte
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
'74120000', //Offset in byte
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
'88120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98120000', //Offset in byte
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
'acc50000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'4cc60000', //Offset (line number 12691)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98120000', //Offset in byte
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
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8120000', //Offset in byte
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
'c4c60000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'64c70000', //Offset (line number 12761)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8120000', //Offset in byte
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
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'80000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50100000', //Offset in byte
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
'48c80000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a0ca0000', //Offset (line number 12968)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4120000', //Offset in byte
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
'50100000', //Offset in byte
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
'50100000', //Offset in byte
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
'f0120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call


'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte

'10000000', //nested call return result
'01000000', //nested call return result

'12000000', //unknown
'01000000', //unknown
'0f000000', //unknown
'2a000000', //unknown
'01000000', //unknown

'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //SetPedOrientation Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'52b8c041', //value 1103149138
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //SetPedOrientation Call
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50100000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c0f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc0e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08130000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'e5020000', //EnableUseable Call
'3c000000', //statement (init statement start offset)
'80ea0000', //Offset (line number 15008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'240e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'22000000', //value 34
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'80020000', //unknown
'00030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'480e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'500e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'580e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'41020000', //setlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'600e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'900e0000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'e5020000', //EnableUseable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'900e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac0e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc0e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc0e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b80e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c0f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c0f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'800f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'800f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'800f0000', //Offset in byte
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
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c0f0000', //Offset in byte
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
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c0f0000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'b1010000', //AIDefineGoalGotoNodeIdle Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00f0000', //LevelVar SpecialStart
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00f0000', //LevelVar SpecialStart
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00f0000', //LevelVar SpecialStart
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
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'1e030000', //SetPedHurtOtherPeds Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'1e030000', //SetPedHurtOtherPeds Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc0f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'800f0000', //Offset in byte
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
'cc0f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00f0000', //LevelVar SpecialStart
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
'e00f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'680f0000', //Offset in byte
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
'5e010000', //SetEntityInvulnerable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f40f0000', //Offset in byte
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
'08100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f40f0000', //Offset in byte
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
'e5020000', //EnableUseable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'38df0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a8df0000', //Offset (line number 14314)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b150eb41', //value 1105940657
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
'ecc0bc40', //value 1086111980
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7e5d5542', //value 1112890750
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'bba7cd41', //value 1103996859
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
'31b1f340', //value 1089712433
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0be45642', //value 1112990731
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'28100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ac1cf641', //value 1106648236
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
'ecc0bc40', //value 1086111980
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e82a5d42', //value 1113402088
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c7a9f141', //value 1106356679
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
'31b1f340', //value 1089712433
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'427e6b42', //value 1114340930
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50100000', //Offset in byte
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
'd40e0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00f0000', //LevelVar SpecialStart
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
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80100000', //Offset in byte
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
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'90100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
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
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9a99993f', //value 1067030938
'10000000', //nested call return result
'01000000', //nested call return result
'f1010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'68100000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'97020000', //SetPedLockonable Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'82020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a80f0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00f0000', //LevelVar SpecialStart
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
'80100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8f64d541', //value 1104503951
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
'10e9cd40', //value 1087236368
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'be5f6b42', //value 1114333118
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
'd252893f', //value 1065964242
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'68100000', //Offset in byte
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
'a5000000', //InsideTrigger Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50e80000', //Offset (line number 14868)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'9ce70000', //Offset (line number 14823)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
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
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b3010000', //AiEntityPlayAnim Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'79020000', //value 633
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'17020000', //AIEntityCancelAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b3010000', //AiEntityPlayAnim Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'82060000', //value 1666
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
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
'23030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68100000', //Offset in byte
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
'6b020000', //SetPedDoNotDecay Call
'3c000000', //statement (init statement start offset)
'80ea0000', //Offset (line number 15008)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'2d010000', //setcurrentlod Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc8c3f', //value 1066192077
'10000000', //nested call return result
'01000000', //nested call return result
'9b010000', //setslidedoorajardistance Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //runscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'2d010000', //setcurrentlod Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'99000000', //UnLockEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
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
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'96000000', //getdoorstate Call
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
'ccef0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'00f00000', //Offset (line number 15360)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'48ef0000', //Offset (line number 15314)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'96000000', //getdoorstate Call
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
'84f00000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b8f00000', //Offset (line number 15406)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'3c000000', //statement (init statement start offset)
'00f00000', //Offset (line number 15360)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4150000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'97000000', //SetDoorState Call
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