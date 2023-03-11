<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Tests\ValidateMls;

class A02PSPTest extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PSP (compile A02) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PSP/A02_TH.MLS', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PSP);
    }
}