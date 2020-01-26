<?php
namespace App\Tests\CompilerV2\Memory;

use App\MHT;
use PHPUnit\Framework\TestCase;

class StringMemoryTest extends TestCase
{

    public function test()
    {


        $script = "
scriptmain OpenablesScript;

entity
	Openables : et_name;
	
const
	cPickupHealth = 'G_First_Aid_(CT)';
	cPickupUziAmmo = 'CJ_MACHINEGUN_AMMO_(CT)';
	cPickupPistolAmmo = 'CJ_PISTOL_AMMO_(CT)';
	cPickupBottle = 'Can_(CT)';
	cPickupCan = 'Can_(CT)';
	cPickupNothing = '';

var
	me : string[32];                    { 196 got 184 == 12 missed }

	mySpawnPos : vec3d;

	LevelState : level_var integer;
	
script OnCreate;
begin
	me := GetEntityName(this);
	
	{temp until found final positions}
  GetDropPosForPlayerPickups(this,mySpawnPos);	

	{save and load}
	{if lLevelState = LevelStart then bOpenableUsed[myOpenablesIndex] := false;
	if bOpenableUsed[myOpenablesIndex] then UseableSetState(this, USEABLE_ON);}
end;

script OnUseableUsed;
var
	vSpawnPosOverride : vec3d;
	strSpawnItem : string[32];
	iProbability : integer;
begin
	{if bOpenableUsed[myOpenablesIndex] = false then
	begin}
		writeDebug(me, ': OnUseableUsed');
		
		{bOpenableUsed[myOpenablesIndex] := true;}
		iProbability := RandNum(10);

		{what to actually drop}
		{DEFAULTS}
		{in storm drain - 40% health; 40% pistol ammo; 10% bottle; 10% nothing}
		case iProbability of
			0: strSpawnItem := cPickupHealth;
			1: strSpawnItem := cPickupHealth;
			2: strSpawnItem := cPickupHealth;
			3: strSpawnItem := cPickupHealth;
			4: strSpawnItem := 'GenericAmmo';
			5: strSpawnItem := 'GenericAmmo';
			6: strSpawnItem := 'GenericAmmo';
			7: strSpawnItem := 'GenericAmmo';
			8: strSpawnItem := cPickupBottle;
			9: strSpawnItem := cPickupNothing;		
		end;
		
		{in final shootout - 50% health; 40% uzi ammo; 10% bottle}
		if LevelState = 9 then
		begin
			case iProbability of
				0: strSpawnItem := cPickupHealth;
				1: strSpawnItem := cPickupHealth;
				2: strSpawnItem := cPickupHealth;
				3: strSpawnItem := cPickupHealth;
				4: strSpawnItem := cPickupHealth;
				5: strSpawnItem := cPickupUziAmmo;
				6: strSpawnItem := cPickupUziAmmo;
				7: strSpawnItem := cPickupUziAmmo;
				8: strSpawnItem := cPickupUziAmmo;
				9: strSpawnItem := cPickupBottle;		
			end;			
		end;
		
		{OVERRIDES}
		{if the player needs health, give it to them}
		if GetDamage(GetPlayer) < 51 then
			strSpawnItem := cPickupHealth;
		{if the player needs ammo, give it to them - only in garage shootout}
		{overrides health, because regardless of the state you're in, you shouldn't bring a knife to a gunfight}
		if LevelState = 9 then
		begin
			if ReturnAmmoOfInventoryWeapon(GetPlayer, CT_UZI) < 61 then
				strSpawnItem := cPickupUziAmmo;
		end;
		
		{spawn the correct ammotype for the player's weapon}
		if strSpawnItem = 'GenericAmmo' then
		begin
			if IsNamedItemInInventory(GetPlayer, CT_UZI) > -1 then
				strSpawnItem := cPickupUziAmmo
			else
				strSpawnItem := cPickupPistolAmmo;
		end;		
		
		writeDebug(me, ': OnUseableUsed: Spawning item: ', strSpawnItem);
		
		{spawn the actual item}
		if strSpawnItem <> '' then 
			SpawnMovingEntity(strSpawnItem, mySpawnPos, 'ScriptCreateName');
	{end;}
end;

end.
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
            "c4000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "12000000",
            "01000000",
            "49000000",
            "10000000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "e8000000",
            "10000000",
            "01000000",
            "96030000",
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
            "34000000",
            "21000000",
            "04000000",
            "01000000",
            "c4000000",
            "12000000",
            "02000000",
            "20000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "21000000",
            "04000000",
            "01000000",
            "64000000",
            "12000000",
            "02000000",
            "10000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "12000000",
            "01000000",
            "0a000000",
            "10000000",
            "01000000",
            "69000000",
            "15000000",
            "04000000",
            "34000000",
            "01000000",
            "13000000",
            "01000000",
            "04000000",
            "34000000",
            "24000000",
            "01000000",
            "09000000",
            "3f000000",
            "34020000",
            "24000000",
            "01000000",
            "08000000",
            "3f000000",
            "98020000",
            "24000000",
            "01000000",
            "07000000",
            "3f000000",
            "fc020000",
            "24000000",
            "01000000",
            "06000000",
            "3f000000",
            "60030000",
            "24000000",
            "01000000",
            "05000000",
            "3f000000",
            "c4030000",
            "24000000",
            "01000000",
            "04000000",
            "3f000000",
            "28040000",
            "24000000",
            "01000000",
            "03000000",
            "3f000000",
            "8c040000",
            "24000000",
            "01000000",
            "02000000",
            "3f000000",
            "f0040000",
            "24000000",
            "01000000",
            "01000000",
            "3f000000",
            "54050000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "b8050000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "60000000",
            "12000000",
            "02000000",
            "01000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "48000000",
            "12000000",
            "02000000",
            "09000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "78000000",
            "12000000",
            "02000000",
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "1c060000",
            "1b000000",
            "f4000000",
            "04000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "09000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "3f000000",
            "74060000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "500b0000",
            "13000000",
            "01000000",
            "04000000",
            "34000000",
            "24000000",
            "01000000",
            "09000000",
            "3f000000",
            "68070000",
            "24000000",
            "01000000",
            "08000000",
            "3f000000",
            "cc070000",
            "24000000",
            "01000000",
            "07000000",
            "3f000000",
            "30080000",
            "24000000",
            "01000000",
            "06000000",
            "3f000000",
            "94080000",
            "24000000",
            "01000000",
            "05000000",
            "3f000000",
            "f8080000",
            "24000000",
            "01000000",
            "04000000",
            "3f000000",
            "5c090000",
            "24000000",
            "01000000",
            "03000000",
            "3f000000",
            "c0090000",
            "24000000",
            "01000000",
            "02000000",
            "3f000000",
            "240a0000",
            "24000000",
            "01000000",
            "01000000",
            "3f000000",
            "880a0000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "ec0a0000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "48000000",
            "12000000",
            "02000000",
            "09000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "500b0000",
            "8a000000",
            "10000000",
            "01000000",
            "84000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "33000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "3d000000",
            "a80b0000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "180c0000",
            "21000000",
            "04000000",
            "01000000",
            "00000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "1b000000",
            "f4000000",
            "04000000",
            "01000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "09000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "3f000000",
            "700c0000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "600d0000",
            "8a000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "2c000000",
            "10000000",
            "01000000",
            "df020000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "3d000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "3d000000",
            "f00c0000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "600d0000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "22000000",
            "04000000",
            "01000000",
            "2c000000",
            "12000000",
            "02000000",
            "20000000",
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
            "0c000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "49000000",
            "12000000",
            "01000000",
            "01000000",
            "3f000000",
            "dc0d0000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "380f0000",
            "8a000000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "2c000000",
            "10000000",
            "01000000",
            "30010000",
            "10000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "2a000000",
            "01000000",
            "0f000000",
            "04000000",
            "23000000",
            "04000000",
            "01000000",
            "12000000",
            "01000000",
            "01000000",
            "42000000",
            "640e0000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "dc0e0000",
            "21000000",
            "04000000",
            "01000000",
            "14000000",
            "12000000",
            "02000000",
            "18000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "3c000000",
            "380f0000",
            "21000000",
            "04000000",
            "01000000",
            "30000000",
            "12000000",
            "02000000",
            "14000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "22000000",
            "04000000",
            "04000000",
            "2c000000",
            "12000000",
            "03000000",
            "20000000",
            "10000000",
            "04000000",
            "10000000",
            "03000000",
            "48000000",
            "21000000",
            "04000000",
            "01000000",
            "c4000000",
            "12000000",
            "02000000",
            "20000000",
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
            "21000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "22000000",
            "04000000",
            "01000000",
            "2c000000",
            "12000000",
            "02000000",
            "20000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "73000000",
            "74000000",
            "22000000",
            "04000000",
            "01000000",
            "2c000000",
            "12000000",
            "02000000",
            "20000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "ac000000",
            "12000000",
            "02000000",
            "01000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "49000000",
            "12000000",
            "01000000",
            "01000000",
            "40000000",
            "48100000",
            "33000000",
            "01000000",
            "01000000",
            "24000000",
            "01000000",
            "00000000",
            "3f000000",
            "d0100000",
            "22000000",
            "04000000",
            "01000000",
            "2c000000",
            "12000000",
            "02000000",
            "20000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "21000000",
            "04000000",
            "01000000",
            "e8000000",
            "10000000",
            "01000000",
            "21000000",
            "04000000",
            "01000000",
            "b0000000",
            "12000000",
            "02000000",
            "11000000",
            "10000000",
            "01000000",
            "10000000",
            "02000000",
            "7a000000",
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
                    echo $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}