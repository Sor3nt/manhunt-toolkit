<?php
namespace App\Tests\Archive\Ifp\Manhunt1;

use App\Service\Archive\Bin;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Resources;
use App\Tests\Archive\Archive;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class XboxTest extends Archive
{


    public function test()
    {
        $testFolder = explode("/tests/", __DIR__)[0] . "/tests/Resources/Archive/Ifp/Manhunt1/XBOX";
        $outputFolder = $testFolder . "/export";

        echo "\n* IFP: Testing Manhunt 1 XBOX (unpack/pack) ";
        $this->unPackPack(
            $testFolder . "/AllAnims.ifp",
            $outputFolder . "/AllAnims#ifp",
            'animations',
            'mh1'
        );

        $this->assertEquals(
            md5(file_get_contents($testFolder . "/AllAnims.ifp")),
            md5(file_get_contents($outputFolder . "/AllAnims.ifp"))
        );

        $this->rrmdir($outputFolder);

    }

}