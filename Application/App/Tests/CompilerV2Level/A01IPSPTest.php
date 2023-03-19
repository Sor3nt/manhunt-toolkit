<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use PHPUnit\Framework\TestCase;

class A01IPSPTest extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PSP (compile A01 i) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PSP/A01_I.MLS', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PSP_001);
    }
}