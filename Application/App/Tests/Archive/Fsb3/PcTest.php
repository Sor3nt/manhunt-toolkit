<?php
namespace App\Tests\Archive\Fsb3;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {


        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Fsb3";
        $outputFolder = $testFolder . "/export";

        echo "\n* FSB3: Testing Manhunt 2 PC (unpack/pack) ==> ";

        $this->unPackPack(
            $testFolder . "/Scripted.fsb",
            $outputFolder . "/Scripted#fsb",
            'audio file (fsb3)',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/Scripted.fsb")),
            md5(file_get_contents($outputFolder . "/Scripted.fsb"))
        );

        $this->rrmdir($outputFolder);


    }

}