<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use PHPUnit\Framework\TestCase;

class A07_2Test extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile A07) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PC/A07_2Tolerance_Zone.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
    }
}