<?php
namespace App\Tests\Archive\Txd\Extract\Manhunt2;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dxt5;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Dxt5Test extends KernelTestCase
{

    public function testDxt5()
    {

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $content = $resources->load('/Archive/Dxt/dxt.dxt5');

        $content = $content->getContent();

        $dxtHandler = new Dxt5();
        $bmpRgba =$dxtHandler->decode($content, 128, 128, 'abgr');

        $bmpHandler = new Bmp();
        $bmpImage = $bmpHandler->encode($bmpRgba, 128, 128);

        $this->assertEquals('192de14bdee1a89a6127545d0648aa1e', md5($bmpImage));

    }

}