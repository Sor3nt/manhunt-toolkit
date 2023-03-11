<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use PHPUnit\Framework\TestCase;

class A01PSP_Test extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PSP_ (compile A02) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PSP/A02_TH_.MLS', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PSP);
    }
}