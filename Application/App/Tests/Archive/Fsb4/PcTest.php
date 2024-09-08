<?php
namespace App\Tests\Archive\Fsb4;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {


        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Fsb4";
        $outputFolder = $testFolder . "/export";

        echo "\n* FSB4: Testing Manhunt 2 PC (unpack/pack) ==> ";

        $this->unPackPack(
            $testFolder . "/A01_Esca_Weapons.fsb",
            $outputFolder . "/A01_Esca_Weapons#fsb",
            'audio file (fsb4)',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );
        $this->assertEquals(
            md5(file_get_contents($testFolder . "/A01_Esca_Weapons.fsb")),
            md5(file_get_contents($outputFolder . "/A01_Esca_Weapons.fsb"))
        );

        $this->rrmdir($outputFolder);


    }

}