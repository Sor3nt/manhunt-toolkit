<?php
namespace App\Tests\CompilerV2\LevelScripts\Manhunt1;

use App\MHT;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use App\Tests\ValidateMls;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrainYardTest extends ValidateMls
{

    public function testLevelScript()
    {
        echo "\n* MLS: Testing Manhunt 1 PC (compile trainyard) ==> ";
        $this->process('/Archive/Mls/Manhunt1/PC/trainyard.mls', MHT::GAME_MANHUNT, MHT::PLATFORM_PC);
    }
}