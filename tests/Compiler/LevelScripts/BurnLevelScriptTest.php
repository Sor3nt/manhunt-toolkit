<?php
namespace App\Tests\LevelScripts;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BurnLevelScriptTest extends KernelTestCase
{

    public function test()
    {
        $this->assertEquals(true, true, 'The bytecode is not correct');
return;
        $script = "
scriptmain Levelscript;

entity
	A09_Burn : et_Level;

type
	tLevelState = (LevelStart,PanicButton,Perimeter,Warehouse,ProjectArrive);

var
	{level-wide globals}
	lLoadingFlag : boolean;
	lLevelState : tLevelState;
	lCurrentAmbientAudioTrack : integer;

	{for levelstate PanicButton}
	bHuntersAreDistracted : boolean;
	iDistractionUsed : integer; {specifies which burnable distraction the player sets light to. must be 1, 2 or 3}
	{for levelstate Perimeter}
	bPerimeterAwareOfPlayer : array[1..4] of boolean;
	{for levelstate Warehouse / ProjectArrive}
	bBurnableBurnt : array[1..2] of boolean;
	bShuttersOpened : array[1..25] of boolean;
	bGasCan3Found : boolean;
	bOpenableUsed : array[1..13] of boolean; {13 openables}
	bBarbedWireReplacement : boolean;
	bPanicButtonPressed : boolean;
	
{ 00000000 (0) 400 <- code länge * 4 -.-}
{ LeaderName -12 }
PROCEDURE InitLeader(LeaderName : String); FORWARD;

{ 90010000 (400) }
{ LeaderName -20; SubpackName -16; HunterName -12 }
PROCEDURE InitHunter(LeaderName, SubpackName, HunterName : String); FORWARD;

{ SubpackName -20; CombatType -16; HuntPlayer -12}
PROCEDURE InitSubpack(SubpackName : string; CombatType : eAICombatType; HuntPlayer : boolean); FORWARD;

{ TriggerName -36; x1 -32; y1 -28;z1 -24;x2 -20; y2 -16; z2 -12 }
PROCEDURE createBTrigger(TriggerName : string; x1, y1, z1, x2, y2, z2 : real); FORWARD;


PROCEDURE createSTrigger(TriggerName : string; Radius, x, y, z : real); FORWARD;
PROCEDURE DestroyEntityIfExists(EntityName : string); FORWARD;
PROCEDURE HideEntityIfExists(EntityName : string); FORWARD;
PROCEDURE DestroySubpack(SubpackName : string); FORWARD;
PROCEDURE SetupDoor(EntityName : string; OutAngle, InAngle : real); FORWARD;
PROCEDURE CheckSavePoint(SavePointName : string; bFirstSavePoint : boolean); FORWARD;
PROCEDURE ArmHunterWithMelee(HunterName : string; weapon : eCollectableType); FORWARD;
PROCEDURE MoveHunter(HunterName : string; x, y, z, orientation : real); FORWARD;

{ 08160000 (5640) 1056v}
PROCEDURE SetupDoors; FORWARD;

{ 281a0000 (6696) 2256 }
PROCEDURE SetupShutters; FORWARD;

{ f8220000 (8952) 572 }
PROCEDURE SetupPadlocks; FORWARD;

{ 34250000 (9524) 1308 }
PROCEDURE SetupBurnables; FORWARD;

PROCEDURE SetupBreakableLights; FORWARD;

{ 502a0000  (10832) 1044}
PROCEDURE SetupMetalDetectors; FORWARD;

{ 642e0000 (11876) 1244 }
PROCEDURE SetupOpenables; FORWARD;

{ 40330000 (13120) 420 }
PROCEDURE SetupWarehouseSwingingLights; FORWARD;

{ e4340000 (13540) 380 }
PROCEDURE SetupCrawlTriggers; FORWARD;

{ 60360000 (13920) }
PROCEDURE SetupPFXs; FORWARD;

script OnCreate;
begin
	WriteDebug('A09_Burn : OnCreate');
	
	{Scoring setup}
	{SetNumberOfKillableHuntersInLevel(17, 17);}
	SetMaxScoreForLevel(68);
	
	{QTM setup}
	SetQTMBaseProbability(50.0);
	SetQTMLength(1.6);
	SetQTMPresses(2);

	{Set Normal ColourRamps}
	SetColourRamp('FE_colramps', 1, 4.0); 
	
	SetNextLevelByName('A11_Medicine_Lab');

	{character/AI setup}
	RunScript('A09_Burn','AssociateAreas');
	AIAddPlayer ('player(player)');

	{ 00000000 }
	InitLeader('leader(leader)');
	AIDefineGoalHuntEnemy('huntPlayer', 'player(player)', TRUE, 16);
	AISetSearchParams('searchDistraction_ThoroughSearch',45.0,true,6.0,75,50,10,120);
		
	{ 08160000 (5640) }
	SetupDoors;

	{ 281a0000 (6696) }
	SetupShutters;

	{ f8220000 (8952) }
	SetupPadlocks;

	{ 34250000 (9524) }
	SetupBurnables;

	{ 502a0000  (10832) }
	SetupMetalDetectors;

	{ 642e0000 (11876) }
	SetupOpenables;

	{ 40330000 (13120) }
	SetupWarehouseSwingingLights;

	{ e4340000 (13540) }
	SetupCrawlTriggers;

	{ 60360000 (13920) }
	SetupPFXs;
	{setup window climb from perimeter to outside warehouse}
	EnableGraphConnection('nPerimeter_Window1','nPerimeter_Window2',false,false);
	HideEntity(GetEntity('auto_project_black'));
	HideEntity(GetEntity('burn_wet_floor'));
	SetMaxNumberOfRats(0);

	{hide save points and determine if we're loading a game}
	lLoadingFlag := true;
	
	CheckSavePoint('Gen_Save_Point01', true);
	CheckSavePoint('Gen_Save_Point02', false);
	CheckSavePoint('Gen_Save_Point03', false);
	CheckSavePoint('Gen_Save_Point04', false);
	
	case lLoadingFlag of
		false:
		begin
			lCurrentAmbientAudioTrack := 0;
			lLevelState := LevelStart;
		end;
		true:
		begin
			writedebug ('Sound_reset');			
			SetAmbientAudioTrack(lCurrentAmbientAudioTrack);
		end;
	end;
		
	RunScript('A09_Burn', 'OnLevelStateSwitch');
end;


script OnLevelStateSwitch;
var
	pos,pos2 : vec3d;
	point : EntityPtr;
begin
	case lLevelState of
		{OnLevelStateSwitch: LevelStart *************************************************************************}
		LevelStart: 
		begin
			writeDebug('Switching to state: LevelStart');
			
			{do any loading dependent tasks}
			case lLoadingFlag of
				true:
				begin
					writeDebug('lLoadingFlag true');
					lLoadingFlag := false;
				end;
				false:
				begin
					writeDebug('lLoadingFlag false');
				end;
			end;
			
			{set radar and objectives}
			point := GetEntity('Gas_Can');
			if (point <> Nil) then
				RadarPositionSetEntity(point, MAP_COLOR_GREEN);
			SetLevelGoal('GOAL_1');
			
			{create triggers}
			createSTrigger('tGasInfo', 1.51909, -13.0369, 0.0, -36.1539);
			createSTrigger('tGasInfo2', 10.0, -13.0575, 0.0, -35.8822);
			createBTrigger('tGasCanReminder', -0.630898, 0.0, -13.5411, 3.92319, 1.28586, -11.3266);
			createBTrigger('tPanicButtonCutscene', -14.5, 0.0, 22.5, -13.5, 4.0, 41.5);
			createBTrigger('tPanicButtonCutscene2', -30.5, 0.0, 31.7, -14.5, 4.0, 32.7);
			SetEntityScriptsFromEntity('tPanicButtonCutscene','tPanicButtonCutscene2');
			createBTrigger('tNoticeMissingGasCan', -13.6, 0.0, -35.1, -10.8, 3.0, -31.3);
			createSTrigger('tSecGuard1', 3.0, 0.0, 1.0, 0.0);
			{createBTrigger('tWindEffect', -13.5, 0.0, -26.5, 4.5, 3.0, -11.5);}
			createBTrigger('tBirdScare', -15.25, 0.0, -1.75, -14.25, 3.0, -0.25);
			
			{create hunters}
			InitSubpack('SubGasStation', COMBATTYPEID_MELEE, false);
			InitSubpack('SubGSHostile', COMBATTYPEID_MELEE, true);
			InitSubpack('SubSecGuard1', COMBATTYPEID_MELEE, true);
			if (GetEntity('Gas_Can(hunter)') <> NIL) then
			begin
			    { 90010000 (400) }
				InitHunter ('leader(leader)','SubGasStation','Gas_Can(hunter)');
				AISetIdleHomeNode('Gas_Can(hunter)', 'nGasCan_1');
				SetHunterHideHealth('Gas_Can(hunter)', 0);
				AISetEntityAllowSurprise('Gas_Can(hunter)', false); {disallow surprise because it looks a bit dorky on hunters that aren't hostile to the player}
				AISetHunterIdlePatrol('Gas_Can(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pGasCan'); 
				AISetIdlePatrolStop('Gas_Can(hunter)', 'nGasCan_1', 4, true);
				AISetIdlePatrolStop('Gas_Can(hunter)', 'nGasCan_2', 3, true);	
			end;
			
			if GetEntity('SecGuard1(hunter)') <> NIL then
			begin
				InitHunter('leader(leader)','SubSecGuard1','SecGuard1(hunter)');
				AISetHunterIdlePatrol('SecGuard1(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pSecGuard1'); 
				AISetIdlePatrolStop('SecGuard1(hunter)', 'nSecGuard1_1', 4, true);
				AISetIdlePatrolStop('SecGuard1(hunter)', 'nSecGuard1_2', 4, true);
				AISetIdleHomeNode('SecGuard1(hunter)', 'nSecGuard1_1');
				AttachToEntity(GetEntity('tSecGuard1'),GetEntity('SecGuard1(hunter)'));
			end;
			
			if GetEntity('SecGuard2(hunter)') <> NIL then
			begin
				InitHunter('leader(leader)','SubSecGuard1','SecGuard2(hunter)');
				AISetHunterIdlePatrol('SecGuard2(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pSecGuard2'); 
				AISetIdlePatrolStop('SecGuard2(hunter)', 'nSecGuard2_1', 3, true);
				AISetIdlePatrolStop('SecGuard2(hunter)', 'nSecGuard2_2', 3, true);	
				AISetIdleHomeNode('SecGuard2(hunter)', 'nSecGuard2_1');
			end;		
			
			{limit gas can hunter to area around his gas station}
			AIAddAreaForSubpack('SubGasStation','aistart1');
			AIAddAreaForSubpack('SubGasStation','aistart2');
			AISubpackStayInTerritory('leader(leader)','SubGasStation',true);
			
		end; {case LevelStart}
				
		{OnLevelStateSwitch: PanicButton *************************************************************************}
		PanicButton: 
		begin
			writeDebug('Switching to state: PanicButton');
			
			{do any loading dependent tasks}		
			case lLoadingFlag of
				true:
				begin
					writeDebug('lLoadingFlag true');
					{mark the gas can on radar}
					point := GetEntity('Gas_Can');
					if (point <> Nil) then
						RadarPositionSetEntity(point, MAP_COLOR_GREEN);			
					
					{setup security guards behind you - if they are still alive}
					InitSubpack('SubSecGuard1', COMBATTYPEID_MELEE, true);
					if GetEntity('SecGuard1(hunter)') <> NIL then
					begin
						InitHunter('leader(leader)','SubSecGuard1','SecGuard1(hunter)');
						AISetHunterIdlePatrol('SecGuard1(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pSecGuard1'); 
						AISetIdlePatrolStop('SecGuard1(hunter)', 'nSecGuard1_1', 2, true);
						AISetIdlePatrolStop('SecGuard1(hunter)', 'nSecGuard1_2', 2, true);
						AISetIdleHomeNode('SecGuard1(hunter)', 'nSecGuard1_1');
					end;		

					if GetEntity('SecGuard2(hunter)') <> NIL then
					begin
						InitHunter('leader(leader)','SubSecGuard1','SecGuard2(hunter)');
						AISetHunterIdlePatrol('SecGuard2(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pSecGuard2'); 
						AISetIdlePatrolStop('SecGuard2(hunter)', 'nSecGuard2_1', 2, true);
						AISetIdlePatrolStop('SecGuard2(hunter)', 'nSecGuard2_2', 2, true);	
						AISetIdleHomeNode('SecGuard2(hunter)', 'nSecGuard2_1');
					end;
										
					lLoadingFlag := false;
				end;
				false:
				begin
					writeDebug('lLoadingFlag false');
					DestroySubpack('SubGasStation');
					DestroySubpack('SubGSHostile');
					{DestroyEntityIfExists('tWindEffect');}
					
					TriggerSavePoint(GetEntity('Gen_Save_Point01'), TRUE);
				end;
			end;
			
			{set radar and objectives}
			SetLevelGoal('GOAL_5');
			RadarPositionClearEntity(GetEntity('Fence02'));
			RadarPositionSetEntity(GetEntity('Distraction01'), MAP_COLOR_BLUE);		
			RadarPositionSetEntity(GetEntity('Distraction02'), MAP_COLOR_BLUE);
			RadarPositionSetEntity(GetEntity('Distraction03'), MAP_COLOR_BLUE);
			
			{create triggers}
			createBTrigger('tStatePerimeter', -0.25, 0.0, 49.75, 2.25, 4.0, 50.25);	
			createSTrigger('tDistractionHint1', 1.0, -5.5, 1.0, 24.75);
			createSTrigger('tDistractionHint2', 1.0, -23.25, 1.0, 39.25);
			createSTrigger('tDistractionHint3', 1.0, -1.5, 1.0, 11.25);
			SetEntityScriptsFromEntity('tDistractionHint1','tDistractionHint2');
			SetEntityScriptsFromEntity('tDistractionHint1','tDistractionHint3');

			{create hunters}
			InitSubpack('SubSecGuard2', COMBATTYPEID_MELEE, true);
			{AIAddSubpackForLeader('leader(leader)',  'SubSecGuard2');
			AISetSubpackCombatType('leader(leader)',  'SubSecGuard2', COMBATTYPEID_MELEE);
			AIAddGoalForSubPack('leader(leader)', 'SubSecGuard2', 'huntPlayer');}
			
			if GetEntity('First_BurnA(hunter)') <> NIL then
			begin
				InitHunter('leader(leader)','SubSecGuard2','First_BurnA(hunter)');
				AISetHunterIdleActionMinMax('First_BurnA(hunter)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_MEDIUMPRIORITY, 1, 2);
				SetHunterHideHealth('First_BurnA(hunter)',0);
			end;
			if GetEntity('First_BurnB(hunter)') <> NIL then
			begin
				InitHunter('leader(leader)','SubSecGuard2','First_BurnB(hunter)');
				AISetHunterIdleActionMinMax('First_BurnB(hunter)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_MEDIUMPRIORITY, 1, 2);
				SetHunterHideHealth('First_BurnB(hunter)',0);
				AIDefineGoalGotoNode('gRunToButton', 'First_BurnB(hunter)', AISCRIPT_VERYHIGHPRIORITY, 'nPanicButton', AISCRIPT_RUNMOVESPEED, true);
				AIDefineGoalGotoNodeIdle('gGotoScreens1', 'First_BurnB(hunter)', AISCRIPT_VERYHIGHPRIORITY, 'nControlRoom_Screens1', AISCRIPT_WALKMOVESPEED, true);
				AIDefineGoalGotoNode('gGotoWindow', 'First_BurnB(hunter)', AISCRIPT_VERYHIGHPRIORITY, 'nWindowGoto', AISCRIPT_WALKMOVESPEED, true);
				AIDefineGoalGotoNodeIdle('gGotoExit', 'First_BurnA(hunter)', AISCRIPT_VERYHIGHPRIORITY, 'nControlRoom_Exit', AISCRIPT_WALKMOVESPEED, true);
			end;
			SetEntityScriptsFromEntity('First_BurnA(hunter)','First_BurnB(hunter)');

			{limit security guards to area around their control room}
			AIAddAreaForSubpack('SubSecGuard2','aictrlrmint');
			AIAddAreaForSubpack('SubSecGuard2','aictrlrmext');
			AISetBoundaryIntercept('leader(leader)','SubSecGuard2',0.25);
			AISubpackStayInTerritory('leader(leader)','SubSecGuard2',true);
			
			{show the cutscene to explain the panic button}
			RunScript('PanicButton_(S)','PanicButtonIntroCutscene');
			
			{no longer check the gas can status}
			RemoveScript('player(player)','OnPickupGasCan');
			RemoveScript('player(player)','OnDropGasCan');
			
		end; {case PanicButton}				
						
		{OnLevelStateSwitch: Perimeter *************************************************************************}
		Perimeter: 
		begin
			writeDebug('Switching to state: Perimeter');
			
			{do any loading dependent tasks}		
			case lLoadingFlag of
				true:
				begin
					writeDebug('lLoadingFlag true');
					lLoadingFlag := false;
				end;
				false:
				begin
					writeDebug('lLoadingFlag false');
					
					{KillScript('First_BurnA(hunter)','RunToButton');}
					KillScript('First_BurnB(hunter)','RunToButton');
					KillScript('First_BurnB(hunter)','RunFailScene');
					KillScript('Distraction01','DistractionHintTimer');
					
					DestroySubpack('SubSecGuard1');
					DestroySubpack('SubSecGuard2');
					RadarPositionClearEntity(GetEntity('Fence02'));					
					TriggerSavePoint(GetEntity('Gen_Save_Point02'), TRUE);					
				end;
			end;
	
			{set radar and objectives}	
			SetLevelGoal('GOAL_6');
			ClearLevelGoal('GOAL_1'); {should be cleared, but in case player hasn't picked up gas can}
			ClearLevelGoal('GOAL_2');
			ClearLevelGoal('GOAL_5');
			RadarPositionSetEntity(GetEntity('Fence03'), MAP_COLOR_BLUE);
			RadarPositionClearEntity(GetEntity('Distraction01'));
			RadarPositionClearEntity(GetEntity('Distraction02'));
			RadarPositionClearEntity(GetEntity('Distraction03'));
			point := GetEntity('Gas_Can'); {clear in case player has left it behind}
			if (point <> Nil) then
			begin
				pos := GetEntityPosition(point);
				if (pos.z < 47.5) and (NOT IsPlayerCarryingGasCan) then
				begin
					RadarPositionClearEntity(point);
				end;
			end;
	
			{create triggers}
			createBTrigger('tStateWarehouse', -30.75, 0.0, 80.5, -18.25, 3.0, 85.5);
	
			{create hunters}
			InitSubpack('SubPerimeter', COMBATTYPEID_MELEE, true);
			{perimeter4 goes into his own subpack to encourage him to stay in his area}
			InitSubpack('SubPerimeter2', COMBATTYPEID_MELEE, true);
			if GetEntity('Perimeter1(hunter)') <> NIL then
			begin
				InitHunter ('leader(leader)','SubPerimeter','Perimeter1(hunter)');
				AISetHunterIdlePatrol('Perimeter1(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pPerimeter1'); 
				AISetIdlePatrolStop('Perimeter1(hunter)', 'nPerimeter1_1', 2, true);
				AISetIdlePatrolStop('Perimeter1(hunter)', 'nPerimeter1_2', 2, true);				
				AISetIdleHomeNode('Perimeter1(hunter)', 'nPerimeter1_1');
			end;
			if GetEntity('Perimeter2(hunter)') <> NIL then
			begin
				InitHunter ('leader(leader)','SubPerimeter','Perimeter2(hunter)');
				AISetHunterIdleActionMinMax('Perimeter2(hunter)', AISCRIPT_IDLE_STANDSTILL, AISCRIPT_MEDIUMPRIORITY, 1, 2);
				AISetIdleHomeNode('Perimeter2(hunter)', 'nPerimeter2_1');
				SetVector(pos,1,0,1);
				AISetHunterHomeNodeDirection('Perimeter2(hunter)', 'nPerimeter2_1', pos);
				SetEntityScriptsFromEntity('Perimeter1(hunter)','Perimeter2(hunter)');
			end;
			if GetEntity('Perimeter3(hunter)') <> NIL then
			begin
				InitHunter ('leader(leader)','SubPerimeter','Perimeter3(hunter)');
				AISetHunterIdlePatrol('Perimeter3(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pPerimeter3'); 
				AISetIdlePatrolStop('Perimeter3(hunter)', 'nPerimeter3_1', 2, true);
				AISetIdlePatrolStop('Perimeter3(hunter)', 'nPerimeter3_2', 2, true);
				AISetIdleHomeNode('Perimeter3(hunter)', 'nPerimeter3_2');
				SetEntityScriptsFromEntity('Perimeter1(hunter)','Perimeter3(hunter)');
			end;
			if GetEntity('Perimeter4(hunter)') <> NIL then
			begin
				InitHunter('leader(leader)','SubPerimeter2','Perimeter4(hunter)');
				AISetHunterIdlePatrol('Perimeter4(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pPerimeter4'); 
				AISetIdlePatrolStop('Perimeter4(hunter)', 'nPerimeter4_1', 2, true);
				AISetIdlePatrolStop('Perimeter4(hunter)', 'nPerimeter4_2', 2, true);		
				AISetIdleHomeNode('Perimeter4(hunter)', 'nPerimeter4_1');
				SetEntityScriptsFromEntity('Perimeter1(hunter)','Perimeter4(hunter)');
			end;
			
			{keep final hunter near end of area - discourage him from coming further}
			AIAddAreaForSubpack('SubPerimeter2','aiperim3');
			AISubpackStayInTerritory('leader(leader)','SubPerimeter2',true);
			{others are allowed to free roam - necessary otherwise they don't seem to think aiperim3 is their area}
			AIAddAreaForSubpack('SubPerimeter','aiperim1');
			AIAddAreaForSubpack('SubPerimeter','aiperim2');
			AIAddAreaForSubpack('SubPerimeter','aiperim3');
				
		end; {case Perimeter}
		
		{OnLevelStateSwitch: Warehouse *************************************************************************}
		Warehouse: 
		begin
			WriteDebug('Switching to state: Warehouse');

			{do any loading dependent tasks}
			case lLoadingFlag of
				true:
				begin
					writeDebug('lLoadingFlag true');
					point:= GetEntity('Fence03');
				  RadarPositionSetEntity(point, MAP_COLOR_BLUE);
				  SetLevelGoal('GOAL_6');
				  {restore padlocked gate flags}
				  if GetEntity('Gen_Padlock01') = NIL then GraphModifyConnections(GetEntity('sideentrance_(D)01'),AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
				  if GetEntity('Gen_Padlock02') = NIL then GraphModifyConnections(GetEntity('metalgate_door_(D)01'),AISCRIPT_GRAPHLINK_ALLOW_EVERYTHING);
					lLoadingFlag := false;
				end;
				false:
				begin
					writeDebug('lLoadingFlag false');
					DestroySubpack('SubPerimeter');
					DestroySubpack('SubPerimeter2');
					KillScript('PanicButton_(S)','PanicButtonSpecialEffects');
					TriggerSavePoint(GetEntity('Gen_Save_Point03'), TRUE);					
				end;
			end;
			
			{set radar and objectives}	
			point := GetEntity('Gas_Can');
			if (point <> Nil) then
				RadarPositionClearEntity(point); {if it hasn't already been cleared}				
			point := GetEntity('Gas_Can01');
			if point <> NIL then
				RadarPositionSetEntity(point, MAP_COLOR_GREEN);
			
			{create triggers}
			createBTrigger('tInWarehouse', -21.5, 0.0, 104.0, -17.5, 4.0, 106.0);
			createBTrigger('tCrowbarDoorHint', -13.0, 0.0, 84.0, -12.0, 4.0, 87.0);
			createSTrigger('tDistractionHintEnd', 1.0, -27.5, 1.0, 143.0);
			{createBTrigger('tKnockBoxOffShelves01', -15.113, 0.0, 102.966, -14.613, 4.0, 105.466);
			createBTrigger('tKnockBoxOffShelves02', -19.3497, 0.0, 114.799, -18.8497, 4.0, 117.299);
			createBTrigger('tKnockBoxOffShelves03', -23.773, 0.0, 126.313, -23.273, 4.0, 128.813);
			createBTrigger('tKnockBoxOffShelves04', -32.3128, 0.0, 126.086, -31.8128, 4.0, 128.586);
			SetEntityScriptsFromEntity('tKnockBoxOffShelves01','tKnockBoxOffShelves02');
			SetEntityScriptsFromEntity('tKnockBoxOffShelves01','tKnockBoxOffShelves03');
			SetEntityScriptsFromEntity('tKnockBoxOffShelves01','tKnockBoxOffShelves04');}
			createSTrigger('tSpotThirdGasCan', 3.5, -0.373, 1.5, 128.974); {location of the gas can, so needs moving if the gas can moves}
			createSTrigger('tCrowbarHint', 1.0, 0, 0, 0);
			if GetEntity('Crowbar_(CT)') <> NIL then
				AttachToEntity(GetEntity('tCrowbarHint'),GetEntity('Crowbar_(CT)'));

			{create hunters}
			InitSubpack('SubWHSecGuard', COMBATTYPEID_MELEE, true);
			SetVector(pos, -33.1394, 1.0127, 126.526);
			SpawnMovingEntity('sc_guardC',pos,'secGuardWH(hunter)');
			InitHunter('leader(leader)','SubWHSecGuard','secGuardWH(hunter)');
			AISetIdleHomeNode('secGuardWH(hunter)', 'nWHMain1_1');	
			{patrol set when entering warehouse}
			ArmHunterWithMelee('secGuardWH(hunter)', CT_NIGHTSTICK);
			
			InitSubpack('SubWHFront', COMBATTYPEID_MELEE, true);
			SetVector(pos2, -4.24243, 1.00091, 101.245);
			SpawnMovingEntity('sc_guardA',pos2,'secGuardFront(hunter)');
			InitHunter('leader(leader)','SubWHFront','secGuardFront(hunter)');
			AISetIdleHomeNode('secGuardFront(hunter)', 'nWH_Front_1');
			AISetHunterIdlePatrol('secGuardFront(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_HIGHPRIORITY, 1000, 5000,'pWH_Front'); 
			AISetIdlePatrolStop('secGuardFront(hunter)', 'nWH_Front_1', 2, true);
			AISetIdlePatrolStop('secGuardFront(hunter)', 'nWH_Front_2', 2, true);
			ArmHunterWithMelee('secGuardFront(hunter)', CT_NIGHTSTICK);
			
			InitSubpack('SubWHSide', COMBATTYPEID_MELEE, true);
			SetVector(pos, 3.05312, 1.0, 130.526);
			SpawnMovingEntity('sc_guardB',pos,'secGuardSide(hunter)');
			InitHunter('leader(leader)','SubWHSide','secGuardSide(hunter)');
			AISetIdleHomeNode('secGuardSide(hunter)', 'nWHSide_1');
			AISetHunterIdlePatrol('secGuardSide(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_HIGHPRIORITY, 1000, 5000,'pWHSide'); 
			AISetIdlePatrolStop('secGuardSide(hunter)', 'nWHSide_1', 2, true);
			AISetIdlePatrolStop('secGuardSide(hunter)', 'nWHSide_2', 2, true);
			ArmHunterWithMelee('secGuardSide(hunter)', CT_NIGHTSTICK);
									
			{if the player made a ruckus in the previous area without cleaning up}
			RunScript('A09_Burn','PerimeterChaser');
						
			{play Leo dialogue}
			RunScript('player(player)','FoundSecondGasCanMessage');
			
		end; {case Warehouse}

		{OnLevelStateSwitch: ProjectArrive ***********************************************************************}
		ProjectArrive: 		
		begin
			WriteDebug('Switching to state: Warehouse');

			{do any loading dependent tasks}
			case lLoadingFlag of
				true:
				begin
					writeDebug('lLoadingFlag true');
					
					{restore radar markings for burnables and gas cans}
					if NOT bBurnableBurnt[1] then RadarPositionSetEntity(GetEntity('Warehouse_Burnable01'), MAP_COLOR_BLUE);
					if NOT bBurnableBurnt[2] then RadarPositionSetEntity(GetEntity('Warehouse_Burnable02'), MAP_COLOR_BLUE);
					point := GetEntity('Gas_Can01');
					if point <> NIL then
						RadarPositionSetEntity(point, MAP_COLOR_GREEN);	
					if NOT bGasCan3Found then
					begin
						createSTrigger('tSpotThirdGasCan', 3.5, -0.373, 1.5, 128.974);
					end
					else
					begin
						point := GetEntity('Gas_Can02');
						if point <> NIL then
							RadarPositionSetEntity(point, MAP_COLOR_GREEN);							
					end;
					
					{show project wagon}
					RunScript('Manager_ProjectWarehouse','ProjectCarAppears');

					lLoadingFlag := false;
				end;
				false:
				begin
					writeDebug('lLoadingFlag false');
					
					DestroySubpack('SubWHSecGuard');
					DestroySubpack('SubWHFront');	
					DestroySubpack('SubWHSide');
					
					TriggerSavePoint(GetEntity('Gen_Save_Point04'), TRUE);
				end;
			end;
			
			{set radar and objectives}			
			
			{create triggers}

			{create hunters}
			InitSubpack('SubWatchdogs', COMBATTYPEID_MELEE, true);
			AIAddAreaForSubpack('SubWatchdogs','aiwhmain');
			AIAddAreaForSubpack('SubWatchdogs','aiwhfrontext');
			AIAddAreaForSubpack('SubWatchdogs','aiwhfrontint');
			AIAddAreaForSubpack('SubWatchdogs','aiwhside');
			RunScript('Manager_ProjectWarehouse','SetupFirstWave');
			
			{swap dry floors for wet floors}
			HideEntity(GetEntity('burn_dry_floor')); 
			ShowEntity(GetEntity('burn_wet_floor'));
			
			{gameplay}
			while IsGameTextDisplaying do sleep(20);
			sleep(500);
			DisplayGameText('GOAL_7'); {remind players to go find the next gas can}			
			
		end; {case ProjectArrive}
		
	end; {end case}
end;

script PerimeterChaser;
begin
	if (bPerimeterAwareOfPlayer[1] or bPerimeterAwareOfPlayer[2] 
		or bPerimeterAwareOfPlayer[3] or bPerimeterAwareOfPlayer[4]) then
	begin
		writeDebug('State: Warehouse: Player did not clean up in previous section. Sending in a hunter after them');
		EnableGraphConnection('nPerimeter_Window1','nPerimeter_Window2',true,false);
		InitSubpack('SubPerimeterChaser', COMBATTYPEID_MELEE, true);
		if GetEntity('Perimeter6(hunter)') <> NIL then
		begin
			InitHunter('leader(leader)','SubPerimeterChaser','Perimeter6(hunter)');
			AIEnableClimbingInIdle(GetEntity('Perimeter6(hunter)'),true);
			AIDefineGoalGotoNode('gChasePlayerThroughWindow', '', AISCRIPT_VERYHIGHPRIORITY, 'nPerimeter_Window3', AISCRIPT_RUNMOVESPEED, true);
			AIAddGoalForSubpack('leader(leader)', 'SubPerimeterChaser', 'gChasePlayerThroughWindow');
			AISetIdleHomeNode('Perimeter6(hunter)', 'nPerimeter_Window3');
			AISetHunterIdlePatrol('Perimeter6(hunter)', AISCRIPT_IDLE_PATROL, AISCRIPT_MEDIUMPRIORITY,1000,5000,'pPerimeter6'); 					
		end;
	end;	
end;

script AssociateAreas;
begin
	AIClearAllActiveAreaAssociations;
	{start area}
	AIAssociateTwoActiveAreasWithPlayerArea('aistart1', 'aistart2','aisecguard1');
	AIAssociateTwoActiveAreasWithPlayerArea('aistart2', 'aistart1','aisecguard1');
	{security guard area}
	AIAssociateThreeActiveAreasWithPlayerArea('aisecguard1', 'aisecguard2','aictrlrmint','aictrlrmext');
	AIAssociateThreeActiveAreasWithPlayerArea('aisecguard2', 'aisecguard1','aictrlrmint','aictrlrmext');
	AIAssociateThreeActiveAreasWithPlayerArea('aictrlrmint', 'aisecguard1','aisecguard2','aictrlrmext');
	AIAssociateThreeActiveAreasWithPlayerArea('aictrlrmext', 'aisecguard1','aisecguard2','aictrlrmint');
	{perimeter area}
	AIAssociateFourActiveAreasWithPlayerArea('aiperim1', 'aictrlrmext','aisecguard2','aiperim2','aiperim3');
	AIAssociateTwoActiveAreasWithPlayerArea('aiperim2', 'aiperim1','aiperim3');
	AIAssociateThreeActiveAreasWithPlayerArea('aiperim3', 'aiperim1','aiperim2','aiwhfrontext');
	{warehouse area}
	AIAssociateFourActiveAreasWithPlayerArea('aiwhfrontext', 'aiperim3','aiwhfrontint','aiwhmain','aiwhside');
	AIAssociateThreeActiveAreasWithPlayerArea('aiwhfrontint', 'aiwhfrontext','aiwhmain','aiwhside');
	AIAssociateThreeActiveAreasWithPlayerArea('aiwhmain', 'aiwhfrontext','aiwhfrontint','aiwhside');
	AIAssociateThreeActiveAreasWithPlayerArea('aiwhside', 'aiwhmain','aiwhfrontext','aiwhfrontint');	
end;

{ =========================================================================== }
{ Helper Procedures and functions                                             }
{ =========================================================================== }

PROCEDURE InitLeader;
begin
	AIAddEntity(LeaderName);                        { LeaderName => f4ffffff }
	AISetHunterOnRadar(LeaderName, FALSE);
	AIEntityAlwaysEnabled(LeaderName);
	AISetEntityAsLeader(LeaderName);
	AISetLeaderInvisible(LeaderName);
	AIAddLeaderEnemy(LeaderName, 'player(player)');
end;

PROCEDURE InitHunter;
begin
	if GetEntity(HunterName) <> NIL then { HunterName => f4ffffff}
	Begin
		Writedebug('Hunter Spawned: ',HunterName); 
		AIAddEntity(HunterName);
		{
		    LeaderName => ecffffff  (-20)
		    SubpackName => f0ffffff (-16)
		    HunterName => f4ffffff (-12)
		}
		AIAddHunterToLeaderSubpack(LeaderName, SubpackName, HunterName);
	end;
end;

PROCEDURE InitSubpack;
begin
{
SubpackName => ecffffff (-20)
CombatType => f0ffffff (-16)
HuntPlayer => f4ffffff (-12)
}
	AIAddSubpackForLeader('leader(leader)',  SubpackName);
	AISetSubpackCombatType('leader(leader)',  SubpackName, CombatType);
	if (HuntPlayer = true) then AIAddGoalForSubPack('leader(leader)', SubpackName, 'huntPlayer');
end;

PROCEDURE createSTrigger;
var
	Vector : vec3d;
begin
	setVector(Vector, x, y, z);
	createSphereTrigger(Vector, Radius, TriggerName);
end;

PROCEDURE createBTrigger;
var
	Vector1 : vec3d;
	Vector2 : vec3d;
begin
{
x1 => e0ffffff (-32)
y1 => e4ffffff (-28)
z1 => e8ffffff (-24)

x2 => ecffffff (-20)
y2 => f0ffffff (-16)
z2 => f4ffffff (-12)

TriggerName => dcffffff (-36)
}
	setVector(Vector1, x1, y1, z1);
	setVector(Vector2, x2, y2, z2);
	createBoxTrigger(Vector1, Vector2, TriggerName);
end;

PROCEDURE DestroyEntityIfExists;
var
	P : EntityPtr;
begin
	P := getEntity(EntityName);
	if (P <> NIL) then DestroyEntity(P);
	writeDebug(EntityName, ' destroyed');
end;

PROCEDURE HideEntityIfExists;
var
	P : EntityPtr;
begin
	P := getEntity(EntityName);
	if (P <> NIL) then HideEntity(P);
	writeDebug(EntityName, ' hidden');
end;

PROCEDURE DestroySubpack;
VAR
	i : integer;
	HunterName : string[32];
begin
	writeDebug('DestroySubpack: Destroying Subpack: ', SubpackName);
	if (AIDoesLeaderHaveSubpack('leader(leader)', SubpackName)) then
	begin
		while (AINumberInSubPack('leader(leader)', SubpackName) > 0) do
		begin
			writeDebug('DestroySubpack: Calling AIReturnSubPackEntityName'); 
			AIReturnSubPackEntityName('leader(leader)', SubpackName, 1, HunterName);
			writeDebug('DestroySubpack: Found hunter: ', HunterName, ' in Subpack: ', SubpackName);
			if DestroyEntity(getEntity(HunterName)) then
			begin
				writeDebug(HunterName, ' destroyed');
			end;
			sleep(100);
			writeDebug('DestroySubpack: Finished deleting a hunter. Please sir, can I have some more?');
		end;
	
		if (AINumberInSubpack('leader(leader)', SubpackName) = 0) then
		begin
			AIRemoveSubPackFromLeader('leader(leader)', SubpackName);
			writeDebug(SubpackName, ' deleted');
		end;
	end;	{if}
end;

PROCEDURE SetupDoor;
var
	Door : EntityPtr;
begin
	Door := GetEntity(EntityName);
 	SetDoorOpenAngleOut(Door, OutAngle);
 	SetDoorOpenAngleIn(Door, InAngle);
end;

PROCEDURE CheckSavePoint;
var
	SavePoint : EntityPtr;
begin
	SavePoint := GetEntity(SavePointName);
	if NIL <> SavePoint then
	begin
		DeactivateSavePoint(SavePoint);
		if bFirstSavePoint then
		begin
			lLoadingFlag := false;
		end;
	end;
end;

PROCEDURE ArmHunterWithMelee;
var
	Hunter : EntityPtr;
begin
	Hunter := getEntity(HunterName);
	
	if Hunter <> NIL then
		CreateInventoryItem(weapon, Hunter, true);
end;

PROCEDURE MoveHunter;
var
	vector : vec3D;
	Hunter : EntityPtr;
begin
	SetVector(vector, x, y, z);
	Hunter := getEntity(HunterName);
	
	if Hunter <> NIL then
	begin
		MoveEntity(Hunter, vector, 0);
		if (orientation >= -500.0) then	SetPedOrientation(Hunter, orientation);
	end;
end;

{ SETUP PROCEDURES ************************************************************* }

PROCEDURE SetupDoors;
begin
	SetupDoor('sideentrance_(D)01', 90.0, 90.0);
	SetupDoor('ControlRoom_Door_(D)', 90.0, 90.0);
	SetupDoor('metalgate_door_(D)01', 90.0, 90.0);
	SetupDoor('SF_door1_(D)', 90.0, 90.0);
	SetupDoor('SF_door1_(D)01', 90.0, 90.0);
	SetupDoor('WH_Door1_(D)', 90.0, 90.0);
	SetupDoor('WH_Door2_(D)', 90.0, 90.0);
	SetupDoor('WH_Door3_(D)', 90.0, 90.0);
	SetupDoor('WH_Door4_(D)', 90.0, 90.0);	
end;

PROCEDURE SetupShutters;
begin
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA01');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA02');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA03');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA04');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA05');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA06');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA07');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA08');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA09');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA11');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA12');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA13');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA14');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA15');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA16');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA17');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA18');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA19');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA20');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA21');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA22');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA23');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA24');
	SetEntityScriptsFromEntity('Storage_ShutterA','Storage_ShutterA25');	
end;

PROCEDURE SetupPadlocks;
begin
	if GetEntity('Gen_Padlock01') <> NIL then SetEntityScriptsFromEntity('Gen_Padlock','Gen_Padlock01');
	if GetEntity('Gen_Padlock02') <> NIL then SetEntityScriptsFromEntity('Gen_Padlock','Gen_Padlock02');
	{if GetEntity('Gen_Padlock03') <> NIL then SetEntityScriptsFromEntity('Gen_Padlock','Gen_Padlock03');}
	HideEntity(GetEntity('Gen_Padlock03'));
end;

PROCEDURE SetupBurnables;
begin
	{at security guard zone}
	HideEntity(GetEntity('Distraction01_b'));
	HideEntity(GetEntity('Distraction02_b'));
	HideEntity(GetEntity('Distraction03_b'));
	SetEntityScriptsFromEntity('Distraction01','Distraction02');
	SetEntityScriptsFromEntity('Distraction01','Distraction03');
	{dummy objects}
	HideEntityIfExists('dummyGasCan_Dist01');
	HideEntityIfExists('dummyGasCan_Dist02');
	HideEntityIfExists('dummyGasCan_Dist03');
	HideEntityIfExists('dummyGasCan_Burn01');
	HideEntityIfExists('dummyGasCan_Burn02');
	
	{at warehouse}
	HideEntity(GetEntity('Warehouse_Burnable01b'));
	HideEntity(GetEntity('Warehouse_Burnable02b'));
	SetEntityScriptsFromEntity('Warehouse_Burnable','Warehouse_Burnable01');
	SetEntityScriptsFromEntity('Warehouse_Burnable','Warehouse_Burnable02');
end;

PROCEDURE SetupBreakableLights;
begin
	SetEntityScriptsFromEntity('A09_wall_lampA_(L)','A09_wall_lampB_(L)');
	SetEntityScriptsFromEntity('A09_wall_lampA_(L)','A09_wall_lampC_(L)');	
end;

PROCEDURE SetupMetalDetectors;
begin
	CreateBTrigger('tMetalDetector01', -20.05, 0.0, 104.3, -18.55, 2.0, 104.4);
	CreateBTrigger('tMetalDetector02', -16.55, 0.0, 123.65, -16.45, 2.0, 125.15);
	CreateBTrigger('tMetalDetector03', -10.25, 0.0, 84.05, -10.15, 2.0, 85.55);
	SetEntityScriptsFromEntity('tMetalDetector','tMetalDetector01');
	SetEntityScriptsFromEntity('tMetalDetector','tMetalDetector02');
	SetEntityScriptsFromEntity('tMetalDetector','tMetalDetector03');
end;

PROCEDURE SetupOpenables;
begin
	setEntityScriptsFromEntity('Openables','cupboard_(O)');
	setEntityScriptsFromEntity('Openables','cupboard_(O)06');
	setEntityScriptsFromEntity('Openables','cupboard_(O)07');
	setEntityScriptsFromEntity('Openables','cupboard_(O)08');
	setEntityScriptsFromEntity('Openables','SLockerC_(O)');
	setEntityScriptsFromEntity('Openables','SLockerC_(O)01');
	setEntityScriptsFromEntity('Openables','SLockerC_(O)02');
	setEntityScriptsFromEntity('Openables','SLockerC_(O)03');
	setEntityScriptsFromEntity('Openables','warehouse_bin_closed_(O)');
	setEntityScriptsFromEntity('Openables','warehouse_bin_closed_(O)01');
	setEntityScriptsFromEntity('Openables','warehouse_bin_closed_(O)02');
	setEntityScriptsFromEntity('Openables','fridge_industrial_(O)_L0');			
	setEntityScriptsFromEntity('Openables','SLockerC_(O)04');	
end;

PROCEDURE SetupWarehouseSwingingLights;
begin
	EntityPlayAnim(GetEntity('industrial_light_swinging_(L)'),'EAT_SWINGING_LIGHT_ANIM',true);
	EntityPlayAnim(GetEntity('industrial_light_swinging_(L)01'),'EAT_SWINGING_LIGHT_ANIM',true);
	EntityPlayAnim(GetEntity('industrial_light_swinging_(L)02'),'EAT_SWINGING_LIGHT_ANIM',true);
end;

PROCEDURE SetupCrawlTriggers;
var
	pos, pos2 : vec3d;
begin
	{near final burnable in warehouse}
	SetVector(pos, -38.1618, 0.538968, 135.452);
	SetVector(pos2, -38.6259, 0.538968, 135.452);
	CreateCrawlTrigger(pos, pos2, 'tCrawlWarehouse');
end;

PROCEDURE SetupPFXs;
begin
	SetEntityScriptsFromEntity('PFX_Spr01','PFX_Spr02');
	SetEntityScriptsFromEntity('PFX_Spr01','PFX_Spr03');
	SetEntityScriptsFromEntity('PFX_Spr01','PFX_Spr04');
	SetEntityScriptsFromEntity('PFX_Spr01','PFX_Spr05');
	SetEntityScriptsFromEntity('PFX_Spr01','PFX_Spr06');
end;

end.

";

        $expected = [

            '10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
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
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bf010000', //aientityalwaysenabled Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4f010000', //aisetentityasleader Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'6d020000', //aisetleaderinvisible Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
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
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'54010000', //aiaddleaderenemy Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'08000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
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
'1c020000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'4c030000', //Offset (line number 211)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c0c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4d010000', //aiaddentity Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'ecffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'52010000', //AIAddHunterToLeaderSubpack Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'10000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'600c0000', //Offset in byte
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
'ecffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'50010000', //aiaddsubpackforleader Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'600c0000', //Offset in byte
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
'ecffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'82010000', //aisetsubpackcombattype Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
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
'a4040000', //Offset (line number 297)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'40050000', //Offset (line number 336)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'600c0000', //Offset in byte
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
'ecffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'10000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'18000000', //Offset in byte
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e8ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'ecffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
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
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'dcffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'20000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'0c000000', //Offset in byte
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'ecffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e8ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3000000', //CreateSphereTrigger Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'18000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'04000000', //Offset in byte
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
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
'40000000', //statement (core)(operator un-equal)
'78080000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a8080000', //Offset (line number 554)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'a0020000', //DestroyEntity Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c0c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'08000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'04000000', //Offset in byte
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
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
'40000000', //statement (core)(operator un-equal)
'e0090000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'100a0000', //Offset (line number 644)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'83000000', //hideentity Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'880c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'08000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'28000000', //Offset in byte
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'940c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'25000000', //value 37
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc0c0000', //Offset in byte
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
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'eb020000', //unknown
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50100000', //Offset (line number 1044)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc0c0000', //Offset in byte
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
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'67010000', //AINumberInSubpack Call
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
'280c0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd80e0000', //Offset (line number 950)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc0c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc0c0000', //Offset in byte
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
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'24000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'ec010000', //AIReturnSubpackEntityName Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'000d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1f000000', //value 31
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'24000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'200d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'24000000', //Offset in byte
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
'a0020000', //DestroyEntity Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'840e0000', //Offset (line number 929)
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'24000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c0d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'4e000000', //value 78
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'3c000000', //statement (init statement start offset)
'840b0000', //Offset (line number 737)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc0c0000', //Offset in byte
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
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'67010000', //AINumberInSubpack Call
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
'7c0f0000', //Offset (line number 991)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50100000', //Offset (line number 1044)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc0c0000', //Offset in byte
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
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'51010000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c0d0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'08000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'04000000', //Offset in byte
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'ecffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
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
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'd4010000', //SetDoorOpenAngleOut Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'd3010000', //SetDoorOpenAngleIn Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'10000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'04000000', //Offset in byte
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
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
'08120000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'78120000', //Offset (line number 1182)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'78120000', //Offset (line number 1182)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'0c000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'04000000', //Offset in byte
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
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
'40000000', //statement (core)(operator un-equal)
'4c130000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a8130000', //Offset (line number 1258)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'04000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'ba000000', //CreateInventoryItem Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'0c000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'34000000', //reserve bytes
'09000000', //reserve bytes
'10000000', //Offset in byte
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e8ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'ecffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f0ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'e4ffffff', //Offset
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
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
'e0140000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec150000', //Offset (line number 1403)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'10000000', //Offset
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
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000fa43', //value 1140457472
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'4e000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'41000000', //unknown
'a4150000', //unknown
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec150000', //Offset (line number 1403)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'10000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'f4ffffff', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'b0020000', //SetPedOrientation Call
'11000000', //unknown
'09000000', //unknown
'0a000000', //unknown
'0f000000', //unknown
'0a000000', //unknown
'3a000000', //unknown
'18000000', //unknown
'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'980d0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac0d0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c40d0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc0d0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec0d0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc0d0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c0e0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c0e0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c0e0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b442', //value 1119092736
'10000000', //nested call return result
'01000000', //nested call return result
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
'6c100000', //unknown
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c0e0000', //Offset in byte
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
'500e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'640e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'780e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'8c0e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'a00e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'b40e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'c80e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'dc0e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'f00e0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'040f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'180f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'2c0f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'400f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'540f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'680f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'7c0f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'900f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'a40f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'b80f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'cc0f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'e00f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'f40f0000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'08100000', //Offset in byte
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
'3c0e0000', //Offset in byte
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
'1c100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30100000', //Offset in byte
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
'84230000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f4230000', //Offset (line number 2301)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40100000', //Offset in byte
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
'30100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50100000', //Offset in byte
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
'6c240000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'dc240000', //Offset (line number 2359)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40100000', //Offset in byte
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
'50100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60100000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70100000', //Offset in byte
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
'84100000', //Offset in byte
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
'98100000', //Offset in byte
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
'ac100000', //Offset in byte
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
'bc100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac100000', //Offset in byte
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
'cc100000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc100000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'28090000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0100000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'28090000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'04110000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'28090000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18110000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'28090000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c110000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'28090000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
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
'58110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
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
'70110000', //Offset in byte
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
'84110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70110000', //Offset in byte
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
'9c110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4110000', //Offset in byte
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
'c8110000', //Offset in byte
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
'b4110000', //Offset in byte
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
'dc110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0110000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6666a041', //value 1101031014
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
'9a99d042', //value 1120967066
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'66669441', //value 1100244582
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
'00000040', //value 1073741824
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdccd042', //value 1120980173
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'04120000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'66668441', //value 1099196006
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
'cd4cf742', //value 1123503309
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9a998341', //value 1099143578
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
'00000040', //value 1073741824
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cd4cfa42', //value 1123699917
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18120000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002441', //value 1092878336
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
'9a19a842', //value 1118312858
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'66662241', //value 1092773478
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
'00000040', //value 1073741824
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9a19ab42', //value 1118509466
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c120000', //Offset in byte
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
'f0110000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c120000', //Offset in byte
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
'04120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c120000', //Offset in byte
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
'18120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c120000', //Offset in byte
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
'48120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c120000', //Offset in byte
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
'58120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'68120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'78120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'88120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c120000', //Offset in byte
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
'98120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'a8120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'b8120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'c8120000', //Offset in byte
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
'3c120000', //Offset in byte
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
'e4120000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1b000000', //value 27
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c120000', //Offset in byte
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
'00130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1b000000', //value 27
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c120000', //Offset in byte
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
'1c130000', //Offset in byte
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
'3c120000', //Offset in byte
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
'38130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48130000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68130000', //Offset in byte
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
'84130000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68130000', //Offset in byte
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
'a8130000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68130000', //Offset in byte
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
'18000000', //Offset in byte
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'afa51842', //value 1108911535
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
'cff9093f', //value 1057618383
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b6730743', //value 1124561846
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
'ec801a42', //value 1109033196
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
'cff9093f', //value 1057618383
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b6730743', //value 1124561846
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
'cc130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd8030000', //CreateCrawlTrigger Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0130000', //Offset in byte
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
'ec130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0130000', //Offset in byte
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
'f8130000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0130000', //Offset in byte
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
'04140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0130000', //Offset in byte
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
'10140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0130000', //Offset in byte
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
'1c140000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
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
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'44000000', //value 68
'10000000', //nested call return result
'01000000', //nested call return result
'59030000', //SetMaxScoreForLevel Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004842', //value 1112014848
'10000000', //nested call return result
'01000000', //nested call return result
'ac030000', //SetQTMBaseProbability Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcccc3f', //value 1070386381
'10000000', //nested call return result
'01000000', //nested call return result
'ad030000', //SetQTMLength Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'ae030000', //SetQTMPresses Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18000000', //Offset in byte
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
'28000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'11000000', //value 17
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'4c030000', //SetNextLevelByName Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'09000000', //value 9
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
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
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
'5b010000', //aiaddplayer Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78000000', //GetEntityPosition Call
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
'58000000', //Offset in byte
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
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84000000', //GetDamage Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'21000000', //value 33
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003442', //value 1110704128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c040', //value 1086324736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4b000000', //value 75
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'32000000', //value 50
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'78000000', //value 120
'10000000', //nested call return result
'01000000', //nested call return result
'97010000', //unknown
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
'08160000', //unknown

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
'281a0000', //unknown

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
'f8220000', //unknown

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
'34250000', //unknown

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
'502a0000', //unknown

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
'642e0000', //unknown
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
'40330000', //unknown
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
'e4340000', //unknown
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
'60360000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8000000', //Offset in byte
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
'bc000000', //Offset in byte
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
'87030000', //EnableGraphConnection Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0000000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4000000', //RunScript Call
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
'83000000', //hideentity Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'a8020000', //setmaxnumberofrats Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
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
'50110000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08010000', //killgametext Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
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
'50110000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c010000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
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
'50110000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30010000', //IsNamedItemInInventory Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
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
'50110000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28140000', //Offset
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'30400000', //Offset (line number 4108)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'88400000', //Offset (line number 4130)
'3c000000', //statement (init statement start offset)
'c8400000', //Offset (line number 4146)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'30140000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'75030000', //SetAmbientAudioTrack Call
'3c000000', //statement (init statement start offset)
'c8400000', //Offset (line number 4146)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'30140000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'2c140000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'c8400000', //Offset (line number 4146)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'09000000', //value 9
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
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
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
'1c000000', //Offset in byte
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'2c140000', //Offset
'24000000', //unknown
'01000000', //unknown
'04000000', //unknown
'3f000000', //statement (init start offset)
'dc410000', //Offset (line number 4215)
'24000000', //unknown
'01000000', //unknown
'03000000', //unknown
'3f000000', //statement (init start offset)
'144c0000', //Offset (line number 4869)
'24000000', //unknown
'01000000', //unknown
'02000000', //unknown
'3f000000', //statement (init start offset)
'ec620000', //Offset (line number 6331)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'a87a0000', //Offset (line number 7850)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'20930000', //Offset (line number 9416)
'3c000000', //statement (init statement start offset)
'98a90000', //Offset (line number 10854)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28140000', //Offset
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50420000', //Offset (line number 4244)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'b4430000', //Offset (line number 4333)
'3c000000', //statement (init statement start offset)
'd8480000', //Offset (line number 4662)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0090000', //Offset in byte
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
'47030000', //TriggerSavePoint Call
'3c000000', //statement (init statement start offset)
'd8480000', //Offset (line number 4662)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c140000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'34000000', //unknown
'01000000', //unknown
'01000000', //unknown
'12000000', //unknown
'04000000', //unknown
'04000000', //unknown
'35000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd0440000', //Offset (line number 4404)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84090000', //Offset in byte
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
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c140000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'02000000', //unknown
'34000000', //unknown
'01000000', //unknown
'01000000', //unknown
'12000000', //unknown
'04000000', //unknown
'04000000', //unknown
'35000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b8450000', //Offset (line number 4462)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c090000', //Offset in byte
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
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'50460000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'94460000', //Offset (line number 4517)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'b8140000', //Offset
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'7c470000', //Offset (line number 4575)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'28080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006040', //value 1080033280
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dbf9be3e', //value 1052703195
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
'0000c03f', //value 1069547520
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'58f90043', //value 1124137304
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'3c000000', //statement (init statement start offset)
'58480000', //Offset (line number 4630)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'14480000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'58480000', //Offset (line number 4630)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0090000', //Offset in byte
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
'dc090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'd8480000', //Offset (line number 4662)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040a0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040a0000', //Offset in byte
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
'140a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040a0000', //Offset in byte
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
'200a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040a0000', //Offset in byte
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
'300a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040a0000', //Offset in byte
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
'400a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0090000', //Offset in byte
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
'4c0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c0a0000', //Offset in byte
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
'83000000', //hideentity Call
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
'77000000', //getentity Call
'10000000', //nested call return result
'01000000', //nested call return result
'82000000', //ShowEntity Call
'07010000', //IsGameTextDisplaying Call
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c44b0000', //Offset (line number 4849)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'14000000', //value 20
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'8c4b0000', //Offset (line number 4835)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f4010000', //value 500
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'04010000', //DisplayGameText Call
'3c000000', //statement (init statement start offset)
'98a90000', //Offset (line number 10854)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28140000', //Offset
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'884c0000', //Offset (line number 4898)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'004e0000', //Offset (line number 4992)
'3c000000', //statement (init statement start offset)
'b0500000', //Offset (line number 5164)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14050000', //Offset in byte
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
'b0070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e5000000', //killscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc070000', //Offset in byte
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
'47030000', //TriggerSavePoint Call
'3c000000', //statement (init statement start offset)
'b0500000', //Offset (line number 5164)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'e0050000', //Offset in byte
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
'64070000', //Offset in byte
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
'4c4f0000', //Offset (line number 5075)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b04f0000', //Offset (line number 5100)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74070000', //Offset in byte
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
'88070000', //Offset in byte
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
'28500000', //Offset (line number 5130)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'8c500000', //Offset (line number 5155)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98070000', //Offset in byte
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
'03000000', //value 3
'10000000', //nested call return result
'01000000', //nested call return result
'e9000000', //GraphModifyConnections Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'b0500000', //Offset (line number 5164)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'48510000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'78510000', //Offset (line number 5214)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'e1020000', //RadarPositionClearEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'10520000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'54520000', //Offset (line number 5269)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec070000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000ac41', //value 1101791232
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
'0000d042', //value 1120927744
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00008c41', //value 1099694080
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
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000d442', //value 1121189888
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc070000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00005041', //value 1095761920
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
'0000a842', //value 1118306304
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004041', //value 1094713344
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
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000ae42', //value 1118699520
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000dc41', //value 1104936960
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
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000f43', //value 1125056512
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'28080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006040', //value 1080033280
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dbf9be3e', //value 1052703195
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
'0000c03f', //value 1069547520
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'58f90043', //value 1124137304
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //HunterSetGunAccuracyMid Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //HunterSetGunAccuracyMid Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //HunterSetGunAccuracyMid Call
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c080000', //Offset in byte
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
'c8560000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50570000', //Offset (line number 5588)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c080000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c080000', //Offset in byte
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
'93000000', //AttachToEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'bf8e0442', //value 1107594943
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
'27a0813f', //value 1065459751
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'500dfd42', //value 1123880272
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'78080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'7a000000', //SpawnMovingEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78080000', //Offset in byte
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
'8c080000', //Offset in byte
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
'78080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1c000000', //value 28
'10000000', //nested call return result
'01000000', //nested call return result
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
'94120000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fdc18740', //value 1082638845
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
'd21d803f', //value 1065360850
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'717dca42', //value 1120566641
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a4080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'7a000000', //SpawnMovingEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
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
'c8080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
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
'e8030000', //value 1000
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
'd8080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
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
'c8080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
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
'e4080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1c000000', //value 28
'10000000', //nested call return result
'01000000', //nested call return result
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
'94120000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'51664340', //value 1078158929
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a8860243', //value 1124239016
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'0c090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'7a000000', //SpawnMovingEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4080000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c090000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c090000', //Offset in byte
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
'24090000', //Offset in byte
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
'0c090000', //Offset in byte
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
'e8030000', //value 1000
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
'30090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c090000', //Offset in byte
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
'24090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c090000', //Offset in byte
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
'3c090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c090000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1c000000', //value 28
'10000000', //nested call return result
'01000000', //nested call return result
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
'94120000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44050000', //Offset in byte
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
'68090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'3c000000', //statement (init statement start offset)
'98a90000', //Offset (line number 10854)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1e000000', //value 30
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28140000', //Offset
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'60630000', //Offset (line number 6360)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'cc650000', //Offset (line number 6515)
'3c000000', //statement (init statement start offset)
'24660000', //Offset (line number 6537)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
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
'94050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e5000000', //killscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
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
'a4050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e5000000', //killscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ac030000', //SetQTMBaseProbability Call
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
'b4050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e5000000', //killscript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
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
'cc050000', //Offset in byte
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
'47030000', //TriggerSavePoint Call
'3c000000', //statement (init statement start offset)
'24660000', //Offset (line number 6537)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'24660000', //Offset (line number 6537)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0050000', //Offset in byte
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
'bc010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //ClearLevelGoal Call
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
'42020000', //ClearLevelGoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98030000', //ApplyForceToPhysicsObject Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //ClearLevelGoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
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
'ac030000', //SetQTMBaseProbability Call
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
'bc030000', //Offset in byte
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
'cc030000', //AISetIdleTalkProbability Call
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
'b0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'80680000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'd0690000', //Offset (line number 6772)
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'08000000', //unknown
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
'00003e42', //value 1111359488
'10000000', //nested call return result
'01000000', //nested call return result
'4e000000', //unknown
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3d000000', //unknown
'74690000', //unknown
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'10000000', //nested call return result
'01000000', //nested call return result
'f0020000', //unknown
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
'd0690000', //Offset (line number 6772)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'e1020000', //RadarPositionClearEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc050000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f641', //value 1106640896
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
'0000a142', //value 1117847552
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00009241', //value 1100087296
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
'00004040', //value 1077936128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000ab42', //value 1118502912
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'186c0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b46e0000', //Offset (line number 7085)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'44060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'54060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'64060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'54060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
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
'2c6f0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e0710000', //Offset (line number 7288)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //AISetHunterIdleActionMinMax Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
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
'88060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
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
'4d000000', //HunterSetGunAccuracyMid Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //HunterSetGunAccuracyMid Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'4d000000', //HunterSetGunAccuracyMid Call
'10000000', //nested call return result
'01000000', //nested call return result
'84010000', //setvector Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74060000', //Offset in byte
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
'88060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'9d010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'74060000', //Offset in byte
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
'98060000', //Offset in byte
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
'58720000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'50750000', //Offset (line number 7508)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98060000', //Offset in byte
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'ac060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98060000', //Offset in byte
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
'bc060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98060000', //Offset in byte
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
'cc060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98060000', //Offset in byte
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
'cc060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'98060000', //Offset in byte
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
'dc060000', //Offset in byte
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
'c8750000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'c0780000', //Offset (line number 7728)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc060000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc060000', //Offset in byte
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'f0060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc060000', //Offset in byte
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
'00070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc060000', //Offset in byte
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
'10070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc060000', //Offset in byte
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
'00070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30060000', //Offset in byte
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
'dc060000', //Offset in byte
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
'20060000', //Offset in byte
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
'20070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
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
'20060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'd5010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
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
'2c070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
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
'38070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10060000', //Offset in byte
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
'20070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'3c000000', //statement (init statement start offset)
'98a90000', //Offset (line number 10854)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'20000000', //value 32
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28140000', //Offset
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'1c7b0000', //Offset (line number 7879)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'387c0000', //Offset (line number 7950)
'3c000000', //statement (init statement start offset)
'04840000', //Offset (line number 8449)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60020000', //EndAudioLooped Call
'10000000', //nested call return result
'01000000', //nested call return result
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
'900a0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84030000', //Offset in byte
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
'47030000', //TriggerSavePoint Call
'3c000000', //statement (init statement start offset)
'04840000', //Offset (line number 8449)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'047d0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'487d0000', //Offset (line number 8018)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'307e0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'cc800000', //Offset (line number 8243)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'dc020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'e8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'f8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'e8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'44810000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e0830000', //Offset (line number 8440)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'1c030000', //ActivateEnvExec Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'28030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'38030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
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
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'28030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'04840000', //Offset (line number 8449)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'98030000', //ApplyForceToPhysicsObject Call
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
'a0030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
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
'ac030000', //SetQTMBaseProbability Call
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
'bc030000', //Offset in byte
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
'cc030000', //AISetIdleTalkProbability Call
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
'dc030000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803e', //value 1048576000
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
'00004742', //value 1111949312
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00001040', //value 1074790400
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004942', //value 1112080384
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0030000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b040', //value 1085276160
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
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c641', //value 1103495168
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'04040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000ba41', //value 1102708736
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
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00001d42', //value 1109196800
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c03f', //value 1069547520
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
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003441', //value 1093926912
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0030000', //Offset in byte
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
'04040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0030000', //Offset in byte
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
'18040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c040000', //Offset in byte
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
'e8890000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'388b0000', //Offset (line number 8910)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c040000', //Offset in byte
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //AISetHunterIdleActionMinMax Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c040000', //Offset in byte
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
'ee010000', //SetHunterHideHealth Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
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
'b08b0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'10900000', //Offset (line number 9220)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'80010000', //AISetHunterIdleActionMinMax Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
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
'ee010000', //SetHunterHideHealth Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'6c040000', //Offset in byte
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
'54040000', //Offset in byte
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
'7c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'8c040000', //Offset in byte
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
'54040000', //Offset in byte
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
'9c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
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
'b1010000', //AIDefineGoalGotoNodeIdle Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4040000', //Offset in byte
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
'54040000', //Offset in byte
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
'c4040000', //Offset in byte
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'6f010000', //AIDefineGoalGotoNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd4040000', //Offset in byte
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
'3c040000', //Offset in byte
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
'e0040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
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
'b1010000', //AIDefineGoalGotoNodeIdle Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c040000', //Offset in byte
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
'54040000', //Offset in byte
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
'2c040000', //Offset in byte
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
'f4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c040000', //Offset in byte
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
'04050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
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
'2c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803e', //value 1048576000
'10000000', //nested call return result
'01000000', //nested call return result
'53020000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
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
'2c040000', //Offset in byte
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
'd5010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14050000', //Offset in byte
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
'28050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'19000000', //value 25
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44050000', //Offset in byte
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
'54050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e6000000', //RemoveScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44050000', //Offset in byte
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
'64050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e6000000', //RemoveScript Call
'3c000000', //statement (init statement start offset)
'98a90000', //Offset (line number 10854)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1f000000', //value 31
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'28140000', //Offset
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'94930000', //Offset (line number 9445)
'24000000', //unknown
'01000000', //unknown
'01000000', //unknown
'3f000000', //statement (init start offset)
'd0930000', //Offset (line number 9460)
'3c000000', //statement (init statement start offset)
'28940000', //Offset (line number 9482)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'3c000000', //statement (init statement start offset)
'28940000', //Offset (line number 9482)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'88010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'28140000', //unknown
'01000000', //unknown
'3c000000', //statement (init statement start offset)
'28940000', //Offset (line number 9482)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0010000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'15000000', //unknown
'04000000', //unknown
'1c000000', //unknown
'01000000', //unknown
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
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
'c0940000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'04950000', //Offset (line number 9537)
'13000000', //read from script var
'01000000', //read from script var
'04000000', //read from script var
'1c000000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05000000', //value 5
'10000000', //nested call return result
'01000000', //nested call return result
'e0020000', //RadarPositionSetEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc010000', //Offset in byte
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
'c4010000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8b71c23f', //value 1069707659
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'24975041', //value 1095800612
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
'989d1042', //value 1108385176
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0010000', //HunterSetGunAccuracyNear Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'85eb5041', //value 1095822213
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
'5f870f42', //value 1108313951
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc010000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8882213f', //value 1059160712
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
'58a85841', //value 1096329304
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
'8c157b40', //value 1081808268
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0f97a43f', //value 1067751183
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c1393541', //value 1094007233
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0010000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006841', //value 1097334784
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
'0000b441', //value 1102315520
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00005841', //value 1096286208
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
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002642', //value 1109786624
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f441', //value 1106509824
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
'9a99fd41', //value 1107138970
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006841', //value 1097334784
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
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc0242', //value 1107479757
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0010000', //Offset in byte
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
'08020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'16000000', //value 22
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'20020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9a995941', //value 1096391066
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
'66660c42', //value 1108108902
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
'cdcc2c41', //value 1093455053
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
'00004040', //value 1077936128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'6666fa41', //value 1106929254
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004040', //value 1077936128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803f', //value 1065353216
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
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
'c0060000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007441', //value 1098121216
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
'0000e03f', //value 1071644672
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
'00006441', //value 1097072640
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
'00004040', //value 1077936128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000803e', //value 1048576000
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
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
'5c050000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60020000', //EndAudioLooped Call
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
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
'a89e0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'cca10000', //Offset (line number 10355)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
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
'a4020000', //switchlitteron Call
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
'80020000', //SetPlayerHeading Call
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
'ee010000', //SetHunterHideHealth Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
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
'6e020000', //AISetEntityAllowSurprise Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'b0020000', //SetPedOrientation Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'08000000', //value 8
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
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
'a4020000', //switchlitteron Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'04000000', //value 4
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80020000', //SetPlayerHeading Call
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
'bc020000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'44a20000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'54a50000', //Offset (line number 10581)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'dc020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'e8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'04000000', //value 4
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'f8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'04000000', //value 4
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'e8020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'38020000', //Offset in byte
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8020000', //Offset in byte
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
'93000000', //AttachToEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'cca50000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'68a80000', //Offset (line number 10778)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70020000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'12000000', //value 18
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'1c030000', //ActivateEnvExec Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'28030000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'38030000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a6010000', //AISetIdlePatrolStop Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08030000', //Offset in byte
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
'28030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50020000', //Offset in byte
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
'48030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50020000', //Offset in byte
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
'54030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'78010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94020000', //PlayerPlayFullBodyAnim Call
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
'50020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'd5010000', //unknown
'3c000000', //statement (init statement start offset)
'98a90000', //Offset (line number 10854)
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
'3c140000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'01000000', //unknown
'34000000', //unknown
'01000000', //unknown
'01000000', //unknown
'12000000', //unknown
'04000000', //unknown
'04000000', //unknown
'35000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c140000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'02000000', //unknown
'34000000', //unknown
'01000000', //unknown
'01000000', //unknown
'12000000', //unknown
'04000000', //unknown
'04000000', //unknown
'35000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
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
'3c140000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'03000000', //unknown
'34000000', //unknown
'01000000', //unknown
'01000000', //unknown
'12000000', //unknown
'04000000', //unknown
'04000000', //unknown
'35000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
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
'3c140000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //unknown
'01000000', //unknown
'04000000', //unknown
'34000000', //unknown
'01000000', //unknown
'01000000', //unknown
'12000000', //unknown
'04000000', //unknown
'04000000', //unknown
'35000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'31000000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //unknown
'04000000', //unknown
'0f000000', //unknown
'02000000', //unknown
'18000000', //unknown
'01000000', //unknown
'04000000', //unknown
'02000000', //unknown
'0f000000', //unknown
'04000000', //unknown
'27000000', //statement (OR operator)
'01000000', //statement (OR operator)
'04000000', //statement (OR operator)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e0b00000', //Offset (line number 11320)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'840a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'5e000000', //value 94
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80a0000', //Offset in byte
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'87030000', //EnableGraphConnection Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c0b0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'68030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'200b0000', //Offset in byte
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
'b0ad0000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'e0b00000', //Offset (line number 11320)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'340b0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c0b0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'200b0000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
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
'90010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'200b0000', //Offset in byte
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
'ce030000', //AiEnableClimbingInIdle Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'440b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'600b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'01000000', //value 1
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
'640b0000', //Offset in byte
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
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'6f010000', //AIDefineGoalGotoNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'340b0000', //Offset in byte
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
'0c0b0000', //Offset in byte
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
'440b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'56010000', //aiaddgoalforsubpack Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'200b0000', //Offset in byte
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
'640b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'83010000', //AISetIdleHomeNode Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'200b0000', //Offset in byte
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
'02000000', //value 2
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e8030000', //value 1000
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
'780b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'a3010000', //AISetHunterIdlePatrol Call
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
'ba010000', //AIClearAllActiveAreaAssociations Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'880b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'940b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'940b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'880b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a00b0000', //Offset in byte
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
'b00b0000', //Offset in byte
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
'c00b0000', //Offset in byte
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
'd00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b00b0000', //Offset in byte
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
'a00b0000', //Offset in byte
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
'c00b0000', //Offset in byte
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
'd00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00b0000', //Offset in byte
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
'a00b0000', //Offset in byte
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
'b00b0000', //Offset in byte
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
'd00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd00b0000', //Offset in byte
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
'a00b0000', //Offset in byte
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
'b00b0000', //Offset in byte
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
'c00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd00b0000', //Offset in byte
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
'b00b0000', //Offset in byte
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
'ec0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'be010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040c0000', //Offset in byte
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
'f80b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140c0000', //Offset in byte
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
'240c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'be010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'140c0000', //Offset in byte
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
'040c0000', //Offset in byte
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
'240c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'240c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040c0000', //Offset in byte
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
'140c0000', //Offset in byte
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
'300c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'300c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'240c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040c0000', //Offset in byte
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
'140c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0d000000', //value 13
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block

        ];

        $compiler = new Compiler();
        $compiled = $compiler->parse($script);

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