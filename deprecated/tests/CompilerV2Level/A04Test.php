<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class A04Test extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile A04) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PC/A04_SM_Nightclub.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
    }
}