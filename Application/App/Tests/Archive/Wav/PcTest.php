<?php
namespace App\Tests\Archive\Wav;

use App\MHT;
use App\Tests\Archive\Archive;

class PcTest extends Archive
{

    public function test()
    {


        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Wav";
        $outputFolder = $testFolder . "/export";

        echo "\n* WAV: Testing Manhunt 2 PC (unpack/pack) ==> ";

        $this->call(
            'unpack',
            $testFolder . "/ambshot.wav",
            'wav file',
            MHT::GAME_MANHUNT_2,
            MHT::PLATFORM_PC
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/ambshot.wav")),
            "9b2f94cdf3f06757431f767598321a8e"
        );

        $this->assertEquals(
            md5(file_get_contents($outputFolder . "/ambshot.wav")),
            "5db204afff11c7583cca197969a3dcfb"
        );

        $this->rrmdir($outputFolder);


    }

}