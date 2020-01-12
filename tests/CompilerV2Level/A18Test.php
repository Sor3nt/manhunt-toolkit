<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class A18Test extends KernelTestCase
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile A18) ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";

        $resource = $resources->load('/Archive/Mls/Manhunt2/PC/A18_Manor.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $handler = $resource->getHandler();

        $mhls = $handler->unpack( $resource->getInput(), MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);

        $levelScriptCompiler = new \App\Service\CompilerV2\Compiler($mhls[0]['SRCE'], MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $levelScriptCompiler->compile();


        $gameScript = "
{ !! THIS IS A GLOBAL FILE FOR THE ENTIRE GAME - EDIT WITH CAUTION !! }
scriptmain gameScript;
entity manhunt : et_game;
var	{ Global game_var's }
	gsAcPlatformModifier, gsAcDifficultyModifier : real;
	{ Accuracy setting }
	gsHeliAccuracy, gsHeliRateOfFire : real;
	gsAcRangeNear_UZI, gsAcRangeMid_UZI, gsAcRangeFar_UZI, gsAcRadiusNear_UZI, gsAcRadiusMid_UZI, gsAcRadiusFar_UZI : real;
	gsAcRangeNear_COLT, gsAcRangeMid_COLT, gsAcRangeFar_COLT, gsAcRadiusNear_COLT, gsAcRadiusMid_COLT, gsAcRadiusFar_COLT : real;
	gsAcRangeNear_GLOCK, gsAcRangeMid_GLOCK, gsAcRangeFar_GLOCK, gsAcRadiusNear_GLOCK, gsAcRadiusMid_GLOCK, gsAcRadiusFar_GLOCK : real;
	gsAcRangeNear_DEAGLE, gsAcRangeMid_DEAGLE, gsAcRangeFar_DEAGLE, gsAcRadiusNear_DEAGLE, gsAcRadiusMid_DEAGLE, gsAcRadiusFar_DEAGLE : real;
	gsAcRangeNear_SNIPER, gsAcRangeMid_SNIPER, gsAcRangeFar_SNIPER, gsAcRadiusNear_SNIPER, gsAcRadiusMid_SNIPER, gsAcRadiusFar_SNIPER : real;
	gsAcRangeNear_SAWNOFF, gsAcRangeMid_SAWNOFF, gsAcRangeFar_SAWNOFF, gsAcRadiusNear_SAWNOFF, gsAcRadiusMid_SAWNOFF, gsAcRadiusFar_SAWNOFF : real;
	gsAcRangeNear_SHOTGUN, gsAcRangeMid_SHOTGUN, gsAcRangeFar_SHOTGUN, gsAcRadiusNear_SHOTGUN, gsAcRadiusMid_SHOTGUN, gsAcRadiusFar_SHOTGUN : real;
	gsAcRangeNear_REVOLVER, gsAcRangeMid_REVOLVER, gsAcRangeFar_REVOLVER, gsAcRadiusNear_REVOLVER, gsAcRadiusMid_REVOLVER, gsAcRadiusFar_REVOLVER : real;
	gsAcRangeNear_CROSSBOW, gsAcRangeMid_CROSSBOW, gsAcRangeFar_CROSSBOW, gsAcRadiusNear_CROSSBOW, gsAcRadiusMid_CROSSBOW, gsAcRadiusFar_CROSSBOW : real;
	gsAcRangeNear_FLAREGUN, gsAcRangeMid_FLAREGUN, gsAcRangeFar_FLAREGUN, gsAcRadiusNear_FLAREGUN, gsAcRadiusMid_FLAREGUN, gsAcRadiusFar_FLAREGUN : real;
	gsAcRangeNear_TRANQGUN, gsAcRangeMid_TRANQGUN, gsAcRangeFar_TRANQGUN, gsAcRadiusNear_TRANQGUN, gsAcRadiusMid_TRANQGUN, gsAcRadiusFar_TRANQGUN : real;

{ GameScript }
script OnCreate;
	begin
		WriteDebug('================= GameScript: OnCreate =================');
		{ Set default variables }
		gsAcPlatformModifier	:= 0;
		gsAcDifficultyModifier	:= 0;
		gsHeliAccuracy			:= 0.2;
		gsHeliRateOfFire		:= 5;
		{ =========================== GUN ACCURACY =========================== }
		{ Modify global accuracy for platform - use a positive number to reduce accuracy }
		if(GetPlatform = 'PS2') then gsAcPlatformModifier := 0.0;
		if(GetPlatform = 'PSP') then gsAcPlatformModifier := 0.2;
		{if(GetPlatform = 'WII') then gsAcPlatformModifier := 0.0;}
		{ Modify global accuracy for difficulty level - use a negative number to increase accuracy }
		{ NOTE: not working currently - we need an OnLevelLoad event to reload the data }
		if(GetDifficultyLevel = DIFFICULTY_EASY) then gsAcDifficultyModifier :=  0.0;
		if(GetDifficultyLevel = DIFFICULTY_HARD) then gsAcDifficultyModifier := -0.01;
		{ ============== WHITE: REVOLVER }
		gsAcRangeNear_REVOLVER	:=  5.0;
		gsAcRangeMid_REVOLVER	:= 15.0;
		gsAcRangeFar_REVOLVER	:= 40.0;
		gsAcRadiusNear_REVOLVER	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_REVOLVER	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_REVOLVER	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: GLOCK }
		gsAcRangeNear_GLOCK		:=  5.0;
		gsAcRangeMid_GLOCK		:= 15.0;
		gsAcRangeFar_GLOCK		:= 40.0;
		gsAcRadiusNear_GLOCK	:=  0.4 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_GLOCK		:=  0.8 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_GLOCK		:=  1.4 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: DEAGLE }
		gsAcRangeNear_DEAGLE	:=  5.0;
		gsAcRangeMid_DEAGLE		:= 15.0;
		gsAcRangeFar_DEAGLE		:= 40.0;
		gsAcRadiusNear_DEAGLE	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_DEAGLE	:=  0.9 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_DEAGLE	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: TRANQGUN }
		gsAcRangeNear_TRANQGUN	:=  5.0;
		gsAcRangeMid_TRANQGUN	:= 15.0;
		gsAcRangeFar_TRANQGUN	:= 40.0;
		gsAcRadiusNear_TRANQGUN	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_TRANQGUN	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_TRANQGUN	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: FLAREGUN }
		gsAcRangeNear_FLAREGUN	:=  5.0;
		gsAcRangeMid_FLAREGUN	:= 15.0;
		gsAcRangeFar_FLAREGUN	:= 40.0;
		gsAcRadiusNear_FLAREGUN	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_FLAREGUN	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_FLAREGUN	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ============== WHITE: UZI }
		gsAcRangeNear_UZI		:=  5.0;
		gsAcRangeMid_UZI		:= 15.0;
		gsAcRangeFar_UZI		:= 40.0;
		gsAcRadiusNear_UZI		:=  0.6 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_UZI		:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_UZI		:=  2.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: SAWNOFF }
		gsAcRangeNear_SAWNOFF	:=  5.0;
		gsAcRangeMid_SAWNOFF	:= 15.0;
		gsAcRangeFar_SAWNOFF	:= 40.0;
		gsAcRadiusNear_SAWNOFF	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_SAWNOFF	:=  1.8 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_SAWNOFF	:=  3.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: SHOTGUN }
		gsAcRangeNear_SHOTGUN	:=  5.0;
		gsAcRangeMid_SHOTGUN	:= 15.0;
		gsAcRangeFar_SHOTGUN	:= 40.0;
		gsAcRadiusNear_SHOTGUN	:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_SHOTGUN	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_SHOTGUN	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: COLT }
		gsAcRangeNear_COLT		:=  5.0;
		gsAcRangeMid_COLT		:= 15.0;
		gsAcRangeFar_COLT		:= 40.0;
		gsAcRadiusNear_COLT		:=  0.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_COLT		:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_COLT		:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: SNIPER }
		gsAcRangeNear_SNIPER	:=  5.0;
		gsAcRangeMid_SNIPER		:= 15.0;
		gsAcRangeFar_SNIPER		:= 40.0;
		gsAcRadiusNear_SNIPER	:=  0.7 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_SNIPER	:=  1.2 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_SNIPER	:=  2.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
		{ ================ RED: CROSSBOW }
		gsAcRangeNear_CROSSBOW	:=  5.0;
		gsAcRangeMid_CROSSBOW	:= 15.0;
		gsAcRangeFar_CROSSBOW	:= 40.0;
		gsAcRadiusNear_CROSSBOW	:=  0.8 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusMid_CROSSBOW	:=  1.5 + gsAcPlatformModifier + gsAcDifficultyModifier;
		gsAcRadiusFar_CROSSBOW	:=  3.0 + gsAcPlatformModifier + gsAcDifficultyModifier;
	end;

end.
 
        ";


        $gameScriptCompiler = new \App\Service\CompilerV2\Compiler($gameScript, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $gameScriptCompiler->compile();




        for($i = 1; $i < count($mhls) ; $i ++){

            $testScript = $mhls[$i];

            var_dump("\nScript: "  . $testScript['NAME']['name'] . " / Index: " . $i);

            $compiler = new \App\Service\CompilerV2\Compiler($testScript['SRCE'], MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
            $compiler->gameScript = $gameScriptCompiler;
            $compiler->levelScript = $levelScriptCompiler;
            $compiler->compile();


            //compile a other script based on the levelscript
//            $compiled = $compiler->parse($testScript['SRCE'], $levelScriptCompiled, 'mh2');
//
//            if ($testScript['CODE'] != $compiled['CODE']){
//                foreach ($testScript['CODE'] as $index => $item) {
//                    if ($compiled['CODE'][$index] == $item){
//                        echo ($index + 1) . '->' . $item . " " . $compiled['CODE'][$index]->debug . "\n";
//                    }else{
//                        echo "MISMATCH need |" . $item . "| got |" . $compiled['CODE'][$index] . " " . $compiled['CODE'][$index]->debug . "|\n";
//                    }
//                }
//                exit;
//            }


            $expected = $testScript['CODE'];

            if ($compiler->validateCode($expected) === false){
                $code = $compiler->codes;

                foreach ($code as $index => $newCode) {

                    if ($expected[$index] == $newCode['code']){
                        echo $index . " " . $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                    }else{
                        echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                    }
                }

                exit;
            }else{
                $this->assertEquals(true,true);
            }


//            foreach ($compiled as $index => $section) {
//
//                //only used inside the compiler
//                if ($index == "extra") continue;
//
//                //memory is not correct but works...
//                if ($index == "DMEM") continue;
//                if ($index == "SMEM") continue;
//
//                //we do not generate the LINE (debug stuff)
//                if ($index == "LINE") continue;
//                if ($index == "STAB" && count($section) == 0) continue;
//
//                if ($index == "DATA"){
//
//                    if (!isset($testScript[$index])){
//
//                        if (
//                            count($section['const']) == 0 &&
//                            count($section['strings']) == 0
//                        ){
//                            continue;
//                        }
//                    }
//
//                    if ($testScript[$index] != $section){
//                        unset($testScript[$index]['byteReserved']);
//
//                    }
//                }
//
//                if ($index == "STAB"){
//                    foreach ($testScript[$index] as &$mhl) {
//                        unset($mhl['nameGarbage']);
//                    }
//                }
//
//
//                $this->assertEquals(
//                    $testScript[$index],
//                    $section,
//                    $index . " Mismatch " . $testScript['ENTT']['name']
//                );
//            }

        }
    }
}