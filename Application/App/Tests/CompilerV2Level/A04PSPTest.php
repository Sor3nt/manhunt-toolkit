<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use PHPUnit\Framework\TestCase;

class A04PSPTest extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PSP (compile A04) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PSP/A04_SM.MLS', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PSP);
    }
}