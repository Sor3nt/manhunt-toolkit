<?php
namespace App\Tests\Archive\Mdl\Manhunt2;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {

        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Mdl/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* Mdl: Testing Manhunt 2 PC (unpack/pack) ==> ";


        $this->unPackPack(
            $testFolder . "/modelspc.mdl",
            $outputFolder . "/modelspc#mdl",
            'model file',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/modelspc.mdl")),
            md5(file_get_contents($outputFolder . "/modelspc.mdl"))
        );

        $this->rrmdir($outputFolder);


    }

}