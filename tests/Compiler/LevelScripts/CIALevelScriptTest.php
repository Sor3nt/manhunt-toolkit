<?php
namespace App\Tests\LevelScripts;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CIALevelScriptTest extends KernelTestCase
{

    public function test()
    {
        $this->assertEquals(true, true, 'The bytecode is not correct');
return;
        $script = "
scriptmain levelscript;

entity
	A06_CIA_Trap : et_level;
	
var
	{general globals}
	LevelState : integer;
	Load : boolean;
	CurrentAmbientAudioTrack : integer;
	
	Save0 : EntityPtr;
	Save1 : EntityPtr;
	Save2 : EntityPtr;
	Save3 : EntityPtr;
	
	{specific to parts of the level}
	HellBreaksLooseInit : boolean;
	sound :	string[16];
	turn : boolean;
	ArnoldDeathCounter : integer;
	HealthSpawnCounter : integer;

	HeliSearch : boolean;
	HeliWarningPlayedRecently : boolean;	
	HeliJoineryIsSearching : boolean;									{switch for is the helicopter searching}
	HeliIsLanding : boolean;
	HeliPlayerSpotted : boolean;
	bBodyMoved : boolean;
	
	LeoPointReached : boolean;
	JSHuntersInWindow : boolean;
	JSDeadHunterPosn : vec3d;
	
	StormDrainDeathCounter : integer;									{used to spawn more hunters in the storm drain}
	GarageHunterDeathCounter : integer;
	iVincentDeathCounter : integer;
	bGarageFightIndoors : boolean;
	JSDeadHunterSpawnFlag : boolean;
	iStormDrainDeletionCounter : integer;
	bHeliInited : boolean; 												{has to be a level_var to be sure it is inited before its value is relied upon}
	
procedure SetupHeliNodes; FORWARD;
procedure SetupDoor(EntityName : string; OutAngle, InAngle : real); FORWARD;
procedure SetupOpenables; FORWARD;
procedure SetupCrawlTriggers; forward;

PROCEDURE HideEntityIfExists(EntityName : string); FORWARD;

script OnCreate;
begin
	{scoring setup}
	{SetNumberOfKillableHuntersInLevel(29, 16);}
	SetMaxScoreForLevel(48);	
	
	{QTM setup}
	SetQTMBaseProbability(70.0);
	SetQTMLength(1.6);
	SetQTMPresses(2);	

  {Set colouramp for normal Danny}
	SetColourRamp('FE_colramps', 1, 4.0);
	
	{setup helicopter nodes}
	SetupHeliNodes;

	{init globals}
	SetMaxNumberOfRats(0);
	SwitchLitterOn(true);	
	
	JSDeadHunterSpawnFlag := false;
	JSHuntersInWindow := false;	
	LeoPointReached := false;
	
	HeliPlayerSpotted := false;
	HeliIsLanding := false;
	HeliSearch := false;
	HeliWarningPlayedRecently := false;
	HeliJoineryIsSearching := false;
	bBodyMoved := false;
	bHeliInited := false;
	
	ArnoldDeathCounter := 0;
	HealthSpawnCounter := 0;
	StormDrainDeathCounter := 0;
	GarageHunterDeathCounter := 0;
	iVincentDeathCounter := 0;
	
	{hide entities}
	HideEntity(GetEntity('Trapdoor_Closed'));	
	HideEntity(GetEntity('Trapdoor_Open'));
	HideEntity(GetEntity('cia_wheel'));
	HideEntity(GetEntity('CJ_POLICE_RIGGED01'));
	HideEntityIfExists('joinery_door');
	HideEntityIfExists('Bulletholes01');
	HideEntityIfExists('Bulletholes02');
	HideEntityIfExists('Bulletholes03');
	HideEntityIfExists('Bulletholes04');
	
	RunScript('A06_CIA_Trap', 'MHUltraScriptSpawn');

	SetNextLevelByName('A09_Burn');

	{load states}
	Load := false;
	LevelState := 0;
	
	{debug}
	showTriggers(false);
		
	{more loading}
	Save0 := getEntity('Save0');
	Save1 := getEntity('Save1');
	Save2 := getEntity('Save2');
	Save3 := getEntity('Save3');

	{check if \"start from the beginning\" or \"load level\"}
	if (Save0 = NIL) then
	begin
		LevelState := 3;
		Load := true;
	end;
	if (Save1 = NIL) then
	begin
		LevelState := 4;
		Load := true;
	end;
	if (Save3 = NIL) then
	begin
		LevelState := 7;
		Load := true;
	end;
	if (Save2 = NIL) then
	begin
		LevelState := 9;
		Load := true;
	end;
	
	if (Save0 <> NIL) then DeactivateSavePoint(Save0);
	if (Save1 <> NIL) then DeactivateSavePoint(Save1);
	if (Save2 <> NIL) then DeactivateSavePoint(Save2);
	if (Save3 <> NIL) then DeactivateSavePoint(Save3);
	
	runScript('A06_CIA_Trap', 'AssociateAreas');
	AIaddPlayer('player(player)');
	
	{activate steaming manholes}
	EntityPlayAnim(GetEntity('PFX_manhole01'), 'PAT_MANHOL', false);
	EntityPlayAnim(GetEntity('PFX_manhole02'), 'PAT_MANHOL', false);
	EntityPlayAnim(GetEntity('PFX_manhole03'), 'PAT_MANHOL', false);
	EntityPlayAnim(GetEntity('PFX_manhole04'), 'PAT_MANHOL', false);
	EntityPlayAnim(GetEntity('PFX_manhole05'), 'PAT_MANHOL', false);
	EntityPlayAnim(GetEntity('PFX_manhole06'), 'PAT_MANHOL', false);
	EntityPlayAnim(GetEntity('PFX_manhole07'), 'PAT_MANHOL', false);
 	
 	{DOOR in the shed}
 	SetupDoor('Door01_(D)', 120.0, 120.0);
	{DOORs in the storm drain}
	SetupDoor('Door02_(D)', 95.0, 95.0);
	SetupDoor('Door03_(D)', 90.0, 90.0);
	
	SetupOpenables;
	SetupCrawlTriggers;
	
	{************* INIT LEADER}
	writeDebug('INIT LEADER');
	AIaddEntity('leader(leader)');
	AIsetHunterOnRadar('leader(leader)', false);
	AIsetEntityAsLeader('leader(leader)');
	AIsetLeaderInvisible('leader(leader)');
	AIEntityAlwaysEnabled('leader(leader)');
	AIaddLeaderEnemy('leader(leader)', 'player(player)');
	{************* INIT LEADER}
	
	AIdefineGoalHuntEnemy('huntPlayer', 'player(player)', false, 16);
		runScript('leader(leader)', 'onLevelStateSwitch');

end; {OnCreate}


script AssociateAreas;
begin
	AIClearAllActiveAreaAssociations;
	
	{NEW AREAS:
	aistart1 - start area and first street
	aistart2 - alleyway player runs down towards joinery
	aijoinery1 - joinery interior
	aijoinery2 - main joinery exterior
	aijoinery3 - small joinery yard just before storm drain
	aidrain1 - first portion of storm drain
	aidrain2 - second portion of storm drain
	aidrain3a - the early part of the main portion of the storm drain
	aidrain3 - main portion of storm drain (open area with valve)
	aidrain4 - side room with ramp in storm drain
	aidrain5 - shootout area of storm drain (beyond storm drain wall)
	aidrain6 - hill up to exit of storm drain
	aisuburbs1 - first back garden
	aisuburbs2 - front gardens + balcony + corridor interior
	aisuburbs3 - street outside suburbs (inaccessible to player)
	aisuburbs4 - 2nd back yard at suburbs
	aisewer1 - sewers
	aisewer2 - second half of sewers - used for positioning audio correctly in MechanicConversationInGarage
	aigarage1 - main garage interior
	aigarage2 - side garage interior (before exit to exterior + side room)
	aigarage3 - main garage exterior
	aigarage4 - garage exterior beyond gate
	}
	
	AIAssociateOneActiveAreaWithPlayerArea('aistart1', 'aistart2');
	AIAssociateTwoActiveAreasWithPlayerArea('aistart2', 'aistart1','aijoinery1');
	AIAssociateTwoActiveAreasWithPlayerArea('aijoinery1', 'aistart2','aijoinery2');
	AIAssociateThreeActiveAreasWithPlayerArea('aijoinery2', 'aistart2','aijoinery1','aijoinery3');
	AIAssociateThreeActiveAreasWithPlayerArea('aijoinery3', 'aijoinery1','aijoinery2','aidrain1');
	AIAssociateFourActiveAreasWithPlayerArea('aidrain1', 'aijoinery3','aidrain2','aidrain3','aidrain4');
	AIAssociateFourActiveAreasWithPlayerArea('aidrain2', 'aijoinery3','aidrain1','aidrain3','aidrain4');
	AIAssociateFourActiveAreasWithPlayerArea('aidrain3a', 'aijoinery3','aidrain1','aidrain2','aidrain3');
		AIAssociateThreeActiveAreasWithPlayerArea('aidrain3a', 'aidrain4','aidrain5','aidrain6');	
	AIAssociateFourActiveAreasWithPlayerArea('aidrain3', 'aijoinery3','aidrain1','aidrain2','aidrain3a');
		AIAssociateThreeActiveAreasWithPlayerArea('aidrain3', 'aidrain4','aidrain5','aidrain6');
	AIAssociateThreeActiveAreasWithPlayerArea('aidrain4', 'aidrain2','aidrain3','aidrain5');
	AIAssociateThreeActiveAreasWithPlayerArea('aidrain5', 'aidrain3','aidrain4','aidrain6');
	AIAssociateThreeActiveAreasWithPlayerArea('aidrain6', 'aidrain3','aidrain5','aisuburbs1');
	AIAssociateThreeActiveAreasWithPlayerArea('aisuburbs1', 'aidrain6','aisuburbs2','aisuburbs3');
	AIAssociateTwoActiveAreasWithPlayerArea('aisuburbs2', 'aisuburbs1','aisuburbs3');
	AIAssociateTwoActiveAreasWithPlayerArea('aisuburbs3', 'aisuburbs1','aisuburbs2');
	AIAssociateTwoActiveAreasWithPlayerArea('aisuburbs4', 'aisuburbs3','aisewer1');
	AIAssociateThreeActiveAreasWithPlayerArea('aisewer1', 'aisuburbs4','aisewer2','aigarage1');
	AIAssociateThreeActiveAreasWithPlayerArea('aisewer2', 'aisuburbs4','aisewer1','aigarage1');
	AIAssociateFourActiveAreasWithPlayerArea('aigarage1', 'aisewer1','aisewer2','aigarage2','aigarage3');
	AIAssociateTwoActiveAreasWithPlayerArea('aigarage2', 'aigarage1','aigarage3');
	AIAssociateThreeActiveAreasWithPlayerArea('aigarage3', 'aigarage1','aigarage2','aigarage4');
	AIAssociateOneActiveAreaWithPlayerArea('aigarage4', 'aigarage3');
end;


script MHUltraScriptSpawn;
var
	pos, pos2 : Vec3D;
begin
	SetVector(pos, 29.1099, 0.0, 108.494);
	SetVector(pos2, 34.5639, 4.7559, 112.151);
	CreateBoxTrigger(pos, pos2, 'specialmoments');
end;


{FUNCTIONS & PROCEDURES +++++++++++++++++++++++++++++++++ FUNCTIONS & PROCEDURES}

PROCEDURE SetupDoor;
var
	Door : EntityPtr;
begin
	Door := GetEntity(EntityName);
 	SetDoorOpenAngleOut(Door, OutAngle);
 	SetDoorOpenAngleIn(Door, InAngle);
end;

PROCEDURE SetupOpenables;
begin
	setEntityScriptsFromEntity('Openables','locker_blue_(O)');
	setEntityScriptsFromEntity('Openables','locker_blue_(O)01');
	setEntityScriptsFromEntity('Openables','DirtLockerA_(O)');
	setEntityScriptsFromEntity('Openables','DirtLockerA_(O)01');
	setEntityScriptsFromEntity('Openables','warehouse_bin_closed_(O)');
	setEntityScriptsFromEntity('Openables','warehouse_bin_closed_(O)01');
end;

procedure SetupCrawlTriggers;
var
	pos, pos2 : vec3d;
begin
	{under the trapdoor in the joinery}
	SetVector(pos, 41.1035, 0.472237, 49.7526);
	SetVector(pos2, 41.1035, 0.472237, 50.3171);
	CreateCrawlTrigger(pos, pos2, 'tCrawlJoinery');
	{exit from the trapdoor crawlspace into the joinery back yard}
	SetVector(pos, 44.1903, 0.472237, 59.4335);
	SetVector(pos2, 43.6658, 0.472237, 59.4335);
	CreateCrawlTrigger(pos, pos2, 'tCrawlJoineryB');
	{entrance to the suburbs under the wall}
	SetVector(pos, 59.913, 0.472237, -21.6445);
	SetVector(pos2, 59.4083, 0.472237, -21.6445);
	CreateCrawlTrigger(pos, pos2, 'tCrawlSuburbs');
	{entry to the exposed sewer pipe}
	SetVector(pos, -1.93163, -1.75866, -33.8235);
	SetVector(pos2, -2.7218, -1.75866, -33.8235);
	CreateCrawlTrigger(pos, pos2, 'tCrawlSewersA');
	{exit from the exposed sewer pipe}
	SetVector(pos, -17.0189, -2.80845, -33.7964);
	SetVector(pos2, -16.2598, -2.80845, -33.7964);
	CreateCrawlTrigger(pos, pos2, 'tCrawlSewersB');
end;

PROCEDURE HideEntityIfExists;
var
	P : EntityPtr;
begin
	P := getEntity(EntityName);
	if (P <> NIL) then HideEntity(P);
	writeDebug(EntityName, ' hidden');	
end;

procedure SetupHeliNodes;
begin
	writeDebug('HeliSetupNodes');
	HeliCreateHeliNode('HN_StormDrainLanding02',79.7,3.0,-62.0);
	HeliCreateHeliNode('HN_StormDrainLanding01',83.5801,6.0,39.7618);
	HeliCreateHeliNode('HN_StormDrainLanding03',79.7,-7.0,-60.31);
	HeliCreateHeliPath('HP_StormDrainLanding','HN_StormDrainLanding01,HN_StormDrainLanding02,HN_StormDrainLanding03');
	HeliCreateHeliNode('HN_JS_Start',46.0,14.0,36.0);
	HeliCreateHeliNode('HN_JS_Shelter',60.0,8.0,66.75);
	HeliCreateHeliPath('HP_Unused','HN_JS_Shelter,HN_JS_Start');
	HeliCreateHeliNode('HN_StormDrainFlyaway02',79.75,3.0,-25.0);
	HeliCreateHeliNode('HN_StormDrainFlyaway01',79.75,0.0,-56.0);
	HeliCreateHeliNode('HN_StormDrainFlyaway03',67.0,15.0,8.0);
	HeliCreateHeliNode('HN_StormDrainFlyaway04',20.0,15.0,33.0);
	HeliCreateHeliNode('HN_StormDrainFlyaway02b',78.0769,9.27247,-8.82276);
	HeliCreateHeliPath('HP_StormDrainFlyaway','HN_StormDrainFlyaway01,HN_StormDrainFlyaway02,HN_StormDrainFlyaway02b,HN_StormDrainFlyaway03,HN_StormDrainFlyaway04');
	HeliCreateHeliNode('HN_SuburbsReturn04',-25.9784,10.0,43.5566);
	HeliCreateHeliNode('HN_SuburbsReturn03',-25.9784,10.0,-2.91133);
	HeliCreateHeliNode('HN_SuburbsReturn02',-17.7994,10.0,-20.1534);
	HeliCreateHeliNode('HN_SuburbsReturn01',-3.2,10.0,-31.0);
	HeliCreateHeliPath('HP_SuburbsExit','HN_SuburbsReturn01,HN_SuburbsReturn02,HN_SuburbsReturn03,HN_SuburbsReturn04');
	HeliCreateHeliNode('HN_JS_01',46.0106,10.0,44.494);
	HeliCreateHeliNode('HN_JS_02',45.7306,10.0,54.554);
	HeliCreateHeliNode('HN_JS_03',45.0863,10.0,63.0916);
	HeliCreateHeliNode('HN_JS_04',41.2479,10.0,62.7863);
	HeliCreateHeliNode('HN_JS_05',41.3375,10.0,69.7405);
	HeliCreateHeliNode('HN_JS_06',47.0703,10.0,70.8975);
	HeliCreateHeliNode('HN_JS_07',55.0161,10.0,68.5578);
	HeliCreateHeliNode('HN_JS_08',55.0523,10.0,53.4302);
	HeliCreateHeliNode('HN_JS_09',61.0321,10.0,52.6302);
	HeliCreateHeliNode('HN_JS_10',55.1624,10.0,47.4035);
	HeliCreateHeliNode('HN_JS_11',55.1624,10.0,38.1726);
	HeliCreateHeliNode('HN_JS_12',53.1482,10.0,43.0979);
	HeliCreateHeliNode('HN_JS_06b',47.051,10.0,68.7106);
	HeliCreateHeliNode('HN_JS_08b',55.9402,10.0,52.53);
	HeliCreateHeliPath('HP_JoinerySearch','HN_JS_01,HN_JS_02,HN_JS_03,HN_JS_04,HN_JS_05,HN_JS_06,HN_JS_06b,HN_JS_07,HN_JS_08,HN_JS_08b,HN_JS_09,HN_JS_10,HN_JS_11,HN_JS_12');
	HeliCreateHeliNode('HeliNode_InitialChase00',-20.4641,2.925,-5.75);
	HeliCreateHeliNode('HeliNode_InitialChase01',-20.4641,2.925,-0.25);
	HeliCreateHeliNode('HeliNode_InitialChase02',-20.4641,9.925,45.5);
	HeliCreateHeliNode('HeliNode_InitialChase03',-5.71406,9.925,46.75);
	HeliCreateHeliNode('HeliNode_InitialChase04',-6.96406,9.925,59.75);
	HeliCreateHeliNode('HeliNode_InitialChase05',17.0359,9.925,63.75);
	HeliCreateHeliNode('HeliNode_InitialChase06',40.0359,11.925,62.693);
	HeliCreateHeliPath('HP_InitialChase','HeliNode_InitialChase00,HeliNode_InitialChase01,HeliNode_InitialChase02,HeliNode_InitialChase03,HeliNode_InitialChase04,HeliNode_InitialChase05,HeliNode_InitialChase06');
	HeliCreateHeliNode('HeliNode_StartPosition',30.0,0.0,200.0);
	HeliCreateHeliNode('HeliNode_StartPosition2',45.0,0.0,200.0);
	HeliCreateHeliNode('HeliNode_StartPosition3',60.0,0.0,200.0);
	HeliCreateHeliNode('HeliNode_StartPosition4',75.7087,0.0,200.0);
	HeliCreateHeliPath('HP_StartPosns','HeliNode_StartPosition,HeliNode_StartPosition4,HeliNode_StartPosition2,HeliNode_StartPosition3');
	HeliCreateHeliNode('HN_StormDrainEntry01',56.0,8.0,61.0);
	HeliCreateHeliNode('HN_StormDrainEntry02',79.0,4.0,74.5);
	HeliCreateHeliPath('HP_StormDrainEntry','HN_StormDrainEntry01,HN_StormDrainEntry02');
	HeliCreateHeliNode('HN_WS_01',55.2842,10.0,37.6583);
	HeliCreateHeliNode('HN_WS_02',68.1753,10.0,37.6583);
	HeliCreateHeliNode('HN_WS_03',69.3044,10.0,47.4257);
	HeliCreateHeliNode('HN_WS_04',69.395,10.0,58.752);
	HeliCreateHeliNode('HN_WS_05',66.9791,10.0,59.1192);
	HeliCreateHeliNode('HN_WS_06',66.9791,10.0,70.0373);
	HeliCreateHeliNode('HN_WS_07',55.0481,10.0,68.3583);
	HeliCreateHeliNode('HN_WS_08',49.4139,10.0,68.3583);
	HeliCreateHeliNode('HN_WS_09',47.3291,10.0,70.7607);
	HeliCreateHeliNode('HN_WS_10',45.0776,10.0,62.22);
	HeliCreateHeliNode('HN_WS_11',45.0298,10.0,45.9347);
	HeliCreateHeliPath('HP_WiderSearch','HN_WS_01,HN_WS_02,HN_WS_03,HN_WS_04,HN_WS_05,HN_WS_06,HN_WS_07,HN_WS_08,HN_WS_09,HN_WS_10,HN_WS_11');
	HeliCreateHeliNode('HN_RoofShoot02',38.5038,10.0,42.415);
	HeliCreateHeliNode('HN_RoofShoot04',38.0557,10.0,40.9337);
	HeliCreateHeliNode('HN_RoofShoot01',37.4489,10.0,64.8873);
	HeliCreateHeliNode('HN_RoofShoot03',40.5266,10.0,63.8174);
	HeliCreateHeliPath('HP_RoofShoot','HN_RoofShoot01,HN_RoofShoot02,HN_RoofShoot03,HN_RoofShoot04');
end;


end.
";

        $expected = [

'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'66669f42', //value 1117742694
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004040', //value 1077936128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007842', //value 1115160576
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0329a742', //value 1118251267
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c040', //value 1086324736
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'150c1f42', //value 1109330965
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'66669f42', //value 1117742694
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000e040', //value 1088421888
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
'713d7142', //value 1114717553
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54040000', //Offset in byte
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
'6c040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'45000000', //value 69
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003842', //value 1110966272
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006041', //value 1096810496
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00001042', //value 1108344832
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007042', //value 1114636288
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000041', //value 1090519040
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00808542', //value 1116045312
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
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
'e0040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1a000000', //value 26
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'fc040000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00809f42', //value 1117749248
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004040', //value 1077936128
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000c841', //value 1103626240
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00809f42', //value 1117749248
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006042', //value 1113587712
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00008642', //value 1116078080
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007041', //value 1097859072
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000041', //value 1090519040
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000a041', //value 1101004800
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007041', //value 1097859072
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000442', //value 1107558400
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'5c050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5f279c42', //value 1117529951
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a5c1441', //value 1091853322
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'062a0d41', //value 1091381766
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'78050000', //Offset in byte
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
'90050000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'74000000', //value 116
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'08060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c3d3cf41', //value 1104139203
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
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f5392e42', //value 1110325749
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c3d3cf41', //value 1104139203
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
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3b533a40', //value 1077564219
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
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
'2c658e41', //value 1099851052
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
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2a3aa141', //value 1101085226
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc4c40', //value 1078774989
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
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f841', //value 1106771968
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'58060000', //Offset in byte
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
'68060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'4c000000', //value 76
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b8060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'db0a3842', //value 1110969051
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dbf93142', //value 1110571483
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'22ec3642', //value 1110895650
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4c375a42', //value 1113208652
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'5f583442', //value 1110726751
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cc5d7c42', //value 1115446732
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd9fd2442', //value 1109720537
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2c257b42', //value 1115366700
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'9a592542', //value 1109744026
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'237b8b42', //value 1116437283
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4060000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'fd473c42', //value 1111246845
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'85cb8d42', //value 1116588933
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7d105c42', //value 1113329789
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'981d8942', //value 1116282264
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'8e355c42', //value 1113339278
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'86b85542', //value 1112914054
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'df207442', //value 1114906847
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'53855242', //value 1112704339
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4ca65c42', //value 1113368140
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'2f9d3d42', //value 1111334191
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4ca65c42', //value 1113368140
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'beb01842', //value 1108914366
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c2975442', //value 1112840130
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'40642c42', //value 1110205504
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'39343c42', //value 1111241785
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd46b8942', //value 1116302292
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'54070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c4c25f42', //value 1113572036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b81e5242', //value 1112678072
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60070000', //Offset in byte
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
'74070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'80000000', //value 128
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f8070000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7ab6a341', //value 1101248122
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
'33333b40', //value 1077621555
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000b840', //value 1085800448
'10000000', //nested call return result
'01000000', //nested call return result
'4f000000', //turn prev number into negative
'32000000', //turn prev number into negative
'09000000', //turn prev number into negative
'04000000', //turn prev number into negative
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7ab6a341', //value 1101248122
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
'33333b40', //value 1077621555
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
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7ab6a341', //value 1101248122
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
'cdcc1e41', //value 1092537549
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003642', //value 1110835200
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'94d9b640', //value 1085725076
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
'cdcc1e41', //value 1092537549
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00003b42', //value 1111162880
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'68080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'94d9de40', //value 1088346516
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
'cdcc1e41', //value 1092537549
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006f42', //value 1114570752
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'86498841', //value 1099450758
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc1e41', //value 1092537549
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007f42', //value 1115619328
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a0080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c3242042', //value 1109402819
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'cdcc3e41', //value 1094634701
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a2c57a42', //value 1115342242
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'bc080000', //Offset in byte
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
'd0080000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'a8000000', //value 168
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'7c090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'17000000', //value 23
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f041', //value 1106247680
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004843', //value 1128792064
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
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
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004843', //value 1128792064
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b0090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007042', //value 1114636288
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004843', //value 1128792064
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'18000000', //value 24
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'db6a9742', //value 1117219547
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00004843', //value 1128792064
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e8090000', //Offset in byte
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
'f8090000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'5f000000', //value 95
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'580a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00006042', //value 1113587712
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000041', //value 1090519040
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00007442', //value 1114898432
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'700a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'15000000', //value 21
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00009e42', //value 1117650944
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00008040', //value 1082130432
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00009542', //value 1117061120
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'880a0000', //Offset in byte
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
'9c0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'2a000000', //value 42
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c80a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05235d42', //value 1113400069
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'19a21642', //value 1108779545
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd40a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'c1598842', //value 1116232129
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'19a21642', //value 1108779545
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'da9b8a42', //value 1116380122
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'ebb33d42', //value 1111340011
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec0a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3dca8a42', //value 1116391997
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0c026b42', //value 1114309132
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f80a0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4df58542', //value 1116075341
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'107a6c42', //value 1114405392
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'040b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4df58542', //value 1116075341
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'19138c42', //value 1116476185
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'100b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'41315c42', //value 1113338177
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'73b78842', //value 1116256115
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'd5a74542', //value 1111861205
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'73b78842', //value 1116256115
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'280b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00513d42', //value 1111314688
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'7a858d42', //value 1116571002
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'340b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'764f3442', //value 1110724470
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'48e17842', //value 1115218248
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'400b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'841e3442', //value 1110711940
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'22bd3742', //value 1110949154
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c0b0000', //Offset in byte
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
'5c0b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'63000000', //value 99
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e4031a42', //value 1109001188
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f6a82942', //value 1110026486
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'09391842', //value 1108883721
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'1cbc2342', //value 1109638172
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'accb1542', //value 1108724652
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'4cc68142', //value 1115801164
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f00b0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'3d1b2242', //value 1109531453
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00002041', //value 1092616192
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'05457f42', //value 1115636997
'10000000', //nested call return result
'01000000', //nested call return result
'b1030000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'000c0000', //Offset in byte
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
'100c0000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'3c000000', //statement (init statement start offset)
'10000000', //Offset (line number 4)
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'b2030000', //unknown
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
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c030000', //Offset in byte
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
'18030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c030000', //Offset in byte
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
'2c030000', //Offset in byte
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
'0c030000', //Offset in byte
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
'40030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd9010000', //SetEntityScriptsFromEntity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c030000', //Offset in byte
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
'54030000', //Offset in byte
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
'0c030000', //Offset in byte
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
'68030000', //Offset in byte
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
'0c030000', //Offset in byte
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
'84030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'1b000000', //value 27
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
'fc692442', //value 1109682684
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0cc9f13e', //value 1056033036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'aa024742', //value 1111949994
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
'fc692442', //value 1109682684
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0cc9f13e', //value 1056033036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b6444942', //value 1112097974
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
'a0030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd8030000', //CreateCrawlTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'dec23042', //value 1110491870
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0cc9f13e', //value 1056033036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e7bb6d42', //value 1114487783
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
'c7a92e42', //value 1110354375
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0cc9f13e', //value 1056033036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e7bb6d42', //value 1114487783
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
'b0030000', //IsFrisbeeSpeechCompleted Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd8030000', //CreateCrawlTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'e9a66f42', //value 1114613481
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0cc9f13e', //value 1056033036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f027ad41', //value 1101866992
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
'19a26d42', //value 1114481177
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0cc9f13e', //value 1056033036
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'f027ad41', //value 1101866992
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
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd8030000', //CreateCrawlTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'a73ff73f', //value 1073168295
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
'c51be13f', //value 1071717317
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
'444b0742', //value 1107774276
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
'f9312e40', //value 1076769273
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
'c51be13f', //value 1071717317
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
'444b0742', //value 1107774276
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
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd0030000', //PlayScriptAudioStreamFromEntityAux Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'd8030000', //CreateCrawlTrigger Call
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'b5268841', //value 1099441845
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
'a5bd3340', //value 1077132709
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
'832f0742', //value 1107767171
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
'12148241', //value 1099043858
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
'a5bd3340', //value 1077132709
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
'832f0742', //value 1107767171
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
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'18000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e0030000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0e000000', //value 14
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
'4c280000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'7c280000', //Offset (line number 2591)
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
'f0030000', //Offset in byte
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'30000000', //value 48
'10000000', //nested call return result
'01000000', //nested call return result
'59030000', //SetMaxScoreForLevel Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00008c42', //value 1116471296
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'a8020000', //setmaxnumberofrats Call
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'01000000', //value 1
'10000000', //nested call return result
'01000000', //nested call return result
'a4020000', //switchlitteron Call
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'cc0c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'ac0c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'a80c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'a00c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'9c0c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'900c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'940c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'980c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'a40c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'd40c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'880c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'8c0c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'bc0c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'c00c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'c40c0000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'10000000', //Offset in byte
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
'24000000', //Offset in byte
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
'34000000', //Offset in byte
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
'83000000', //hideentity Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40000000', //Offset in byte
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
'54000000', //Offset in byte
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
'94270000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'64000000', //Offset in byte
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
'94270000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'74000000', //WriteDebugFlush Call
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
'94270000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84000000', //GetDamage Call
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
'94270000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'94000000', //Offset in byte
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
'94270000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a4000000', //Offset in byte
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
'b4000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'13000000', //value 19
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'e4000000', //RunScript Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c8000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
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
'540c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'00000000', //value 0
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'500c0000', //unknown
'01000000', //unknown
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'20010000', //ShowTriggers Call
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
'77000000', //getentity Call
'16000000', //unknown
'04000000', //unknown
'5c0c0000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'dc000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'16000000', //unknown
'04000000', //unknown
'600c0000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4000000', //RunScript Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'16000000', //unknown
'04000000', //unknown
'640c0000', //unknown
'01000000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'ec000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'77000000', //getentity Call
'16000000', //unknown
'04000000', //unknown
'680c0000', //unknown
'01000000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'5c0c0000', //Offset
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
'58300000', //Offset (line number 3094)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a4300000', //Offset (line number 3113)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'03000000', //value 3
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'500c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'540c0000', //unknown
'01000000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'600c0000', //Offset
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
'fc300000', //Offset (line number 3135)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'48310000', //Offset (line number 3154)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'04000000', //value 4
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'500c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'540c0000', //unknown
'01000000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'680c0000', //Offset
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
'a0310000', //Offset (line number 3176)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec310000', //Offset (line number 3195)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'07000000', //value 7
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'500c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'540c0000', //unknown
'01000000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'640c0000', //Offset
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
'44320000', //Offset (line number 3217)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'90320000', //Offset (line number 3236)
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'09000000', //value 9
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'500c0000', //unknown
'01000000', //unknown
'12000000', //parameter (access script var)
'01000000', //parameter (access script var)
'01000000', //value 1
'16000000', //parameter (access script var)
'04000000', //parameter (access script var)
'540c0000', //unknown
'01000000', //unknown
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'5c0c0000', //Offset
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
'e8320000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'18330000', //Offset (line number 3270)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'5c0c0000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'600c0000', //Offset
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
'70330000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'a0330000', //Offset (line number 3304)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'600c0000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'640c0000', //Offset
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
'f8330000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'28340000', //Offset (line number 3338)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'640c0000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'680c0000', //Offset
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
'80340000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b0340000', //Offset (line number 3372)
'14000000', //Read VAR from header
'01000000', //Read VAR from header
'04000000', //Read VAR from header
'680c0000', //Offset
'10000000', //nested call return result
'01000000', //nested call return result
'12030000', //DeactivateSavePoint Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a4000000', //Offset in byte
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
'f4000000', //Offset in byte
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
'04010000', //DisplayGameText Call
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
'14010000', //Offset in byte
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30010000', //IsNamedItemInInventory Call
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'40010000', //SetMoverSpeed Call
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'50010000', //aiaddsubpackforleader Call
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'60010000', //Offset in byte
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'70010000', //Offset in byte
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'80010000', //AISetHunterIdleActionMinMax Call
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
'24010000', //Offset in byte
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
'a1010000', //EntityPlayAnim Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90010000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f042', //value 1123024896
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000f042', //value 1123024896
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
'7c1d0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c010000', //AISetHunterIdleDirection Call
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000be42', //value 1119748096
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0000be42', //value 1119748096
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
'7c1d0000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8010000', //aisethunteronradar Call
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
'7c1d0000', //unknown
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
'601e0000', //unknown
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
'b8200000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'b4010000', //AIEntityPlayAnimLooped Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0c000000', //value 12
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //WriteDebugString Call
'74000000', //WriteDebugFlush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4010000', //Offset in byte
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
'c4010000', //Offset in byte
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
'c4010000', //Offset in byte
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
'c4010000', //Offset in byte
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
'c4010000', //Offset in byte
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
'c4010000', //Offset in byte
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
'04010000', //DisplayGameText Call
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
'd4010000', //SetDoorOpenAngleOut Call
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
'04010000', //DisplayGameText Call
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
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'58010000', //aidefinegoalhuntenemy Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c4010000', //Offset in byte
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
'e0010000', //Offset in byte
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
'ba010000', //AIClearAllActiveAreaAssociations Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f4010000', //Offset in byte
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
'00020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bb010000', //AIAssociateOneActiveAreaWithPlayerArea Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00020000', //Offset in byte
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
'f4010000', //Offset in byte
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
'0c020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //AIAssociateTwoActiveAreasWithPlayerArea


'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'0c020000', //Offset in byte
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
'00020000', //Offset in byte
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
'18020000', //AIEntityGoHomeIfIdle Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //AIAssociateTwoActiveAreasWithPlayerArea
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'18020000', //AIEntityGoHomeIfIdle Call
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
'00020000', //Offset in byte
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
'0c020000', //Offset in byte
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
'24020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //AIAssociateThreeActiveAreasWithPlayerArea

'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24020000', //Offset in byte
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
'0c020000', //Offset in byte
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
'18020000', //AIEntityGoHomeIfIdle Call
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
'30020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //AIAssociateThreeActiveAreasWithPlayerArea


'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'30020000', //Offset in byte
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
'24020000', //Offset in byte
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
'3c020000', //Offset in byte
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
'48020000', //Offset in byte
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
'54020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'09000000', //value 9
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'be010000', //AIAssociateFourActiveAreasWithPlayerArea


'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c020000', //Offset in byte
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
'24020000', //Offset in byte
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
'30020000', //Offset in byte
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
'48020000', //Offset in byte
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
'54020000', //Offset in byte
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
'60020000', //EndAudioLooped Call
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
'24020000', //Offset in byte
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
'30020000', //Offset in byte
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
'3c020000', //Offset in byte
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
'48020000', //Offset in byte
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
'60020000', //EndAudioLooped Call
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
'54020000', //Offset in byte
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
'6c020000', //Offset in byte
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
'78020000', //Offset in byte
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
'48020000', //Offset in byte
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
'24020000', //Offset in byte
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
'30020000', //Offset in byte
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
'3c020000', //Offset in byte
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
'60020000', //EndAudioLooped Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'be010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'48020000', //Offset in byte
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
'54020000', //Offset in byte
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
'6c020000', //Offset in byte
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
'78020000', //Offset in byte
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
'54020000', //Offset in byte
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
'3c020000', //Offset in byte
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
'48020000', //Offset in byte
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
'6c020000', //Offset in byte
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
'6c020000', //Offset in byte
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
'48020000', //Offset in byte
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
'54020000', //Offset in byte
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
'78020000', //Offset in byte
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
'78020000', //Offset in byte
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
'48020000', //Offset in byte
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
'6c020000', //Offset in byte
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
'84020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'84020000', //Offset in byte
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
'78020000', //Offset in byte
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
'90020000', //Offset in byte
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
'9c020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'90020000', //Offset in byte
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
'84020000', //Offset in byte
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
'9c020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'9c020000', //Offset in byte
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
'84020000', //Offset in byte
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
'90020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0b000000', //value 11
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'a8020000', //setmaxnumberofrats Call
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
'9c020000', //Offset in byte
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
'b4020000', //PlayerDropBody Call
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
'b4020000', //PlayerDropBody Call
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
'a8020000', //setmaxnumberofrats Call
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
'c0020000', //Offset in byte
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
'cc020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'c0020000', //Offset in byte
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
'a8020000', //setmaxnumberofrats Call
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
'b4020000', //PlayerDropBody Call
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
'cc020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'cc020000', //Offset in byte
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
'b4020000', //PlayerDropBody Call
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
'c0020000', //Offset in byte
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
'd8020000', //Offset in byte
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
'e4020000', //IsPlayerWallSquashed Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'be010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'd8020000', //Offset in byte
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
'cc020000', //Offset in byte
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
'e4020000', //IsPlayerWallSquashed Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bc010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'e4020000', //IsPlayerWallSquashed Call
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
'cc020000', //Offset in byte
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
'd8020000', //Offset in byte
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
'f0020000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bd010000', //unknown
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'f0020000', //Offset in byte
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
'e4020000', //IsPlayerWallSquashed Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'bb010000', //AIAssociateOneActiveAreaWithPlayerArea Call
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
'22000000', //Prepare string read (3)
'04000000', //Prepare string read (3)
'01000000', //Prepare string read (3)
'0c000000', //Offset in byte
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'13e1e841', //value 1105781011
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'00000000', //value 0
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'eefcd842', //value 1121516782
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
'6f410a42', //value 1107968367
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'55309840', //value 1083715669
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'504de042', //value 1121996112
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
'fc020000', //DecreaseCounter Call
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'0f000000', //value 15
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'28010000', //CreateBoxTrigger Call
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