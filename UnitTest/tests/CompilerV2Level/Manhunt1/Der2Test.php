<?php
namespace App\Tests\CompilerV2\LevelScripts\Manhunt1;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use PHPUnit\Framework\TestCase;

class Der2Test extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 1 PC (compile Der2) ==> ";
        $this->process('/Archive/Mls/Manhunt1/PC/der2.mls', MHT::GAME_MANHUNT, MHT::PLATFORM_PC);
    }
}