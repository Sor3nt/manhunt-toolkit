<?php
namespace App\Tests\Archive\Txd\Extract\Manhunt2;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dxt;
use App\Service\Archive\Dxt1;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Dxt1Test extends KernelTestCase
{

    public function testDxt1()
    {
        echo "\n* DX1: Testing DXT1 Texture ==> ";

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $content = $resources->load('/Archive/Dxt/dxt.dxt1');

        $content = $content->getContent();

        $dxtHandler = new Dxt1();
        $bmpRgba =$dxtHandler->decode($content, 128, 128, 'abgr');

        $bmpHandler = new Bmp();
        $bmpImage = $bmpHandler->encode($bmpRgba, 128, 128);

        $this->assertEquals('aa62051473a63a14cba17a27d1bc0ac9', md5($bmpImage));

    }

}