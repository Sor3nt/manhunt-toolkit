<?php
namespace App\Tests\Archive\Mdl\Manhunt2;

use App\MHT;
use App\Service\NBinary;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {

        $testFolder = explode("/Tests/", __DIR__)[0] . "/Tests/Resources/Archive/Mdl/Manhunt2/PC";
        $outputFolder = $testFolder . "/export";

        echo "\n* Mdl: Testing Manhunt 2 PC (unpack/pack) ==> ";


        /*
         * Why the double unpack/pack?
         *
         * The Manhunt MDL BoneName has some memory leaked data
         */
        $this->unPackPack(
            $testFolder . "/modelspc.mdl",
            $outputFolder . "/modelspc#mdl",
            'model file',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->unPackPack(
            $outputFolder . "/modelspc.mdl",
            $outputFolder . "/export/modelspc#mdl",
            'model file',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );


        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/modelspc.mdl")),
            md5(file_get_contents($outputFolder . "/export/modelspc.mdl"))
        );

        $this->rrmdir($outputFolder);



    }

}