<?php
namespace App\Tests\CompilerV2\LevelScripts;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use PHPUnit\Framework\TestCase;

class A16Test extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 2 PC (compile A16) ==> ";
        $this->process('/Archive/Mls/Manhunt2/PC/A16_TV_Studio.mls', MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
    }
}