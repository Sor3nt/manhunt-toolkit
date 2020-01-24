<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class A07Test extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile A07) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PC/A07_Tolerance_Zone.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
    }
}