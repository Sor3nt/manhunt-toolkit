<?php
namespace App\Tests\Archive\Mls\Extract\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class WiiTest extends Archive
{

    public function testLevelScript()
    {

        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Mls/Manhunt2/Wii";

        $outputFolder = $testFolder . "/export";

        echo "\n* MLS: Testing Manhunt 2 Wii (unpack) ==> ";


        $this->call(
            'unpack',
            $testFolder . "/A01_Escape_Asylum.mls",
            'levelscript',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_WII
        );

    }

}