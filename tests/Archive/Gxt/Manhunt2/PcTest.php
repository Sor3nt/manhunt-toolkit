<?php
namespace App\Tests\Archive\Gxt\Manhunt2;

use App\Service\Archive\Glg;
use App\Service\Archive\Gxt;
use App\Service\Archive\Mls;
use App\Service\Archive\ZLib;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PcTest extends KernelTestCase
{

    public function testPackUnpackMh2()
    {
        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Gxt/Manhunt2/PC/A01_Escape_Asylum.gxt');

        $content = $resource->getContent();

        $handler = new Gxt();
        $repacked1 = $handler->pack($content);

        /**
         * we cant compare against the original content because there are unused text elements
         */
        $unpacked = $handler->unpack($repacked1);
        $repacked1 = $handler->pack($unpacked);

        $this->assertEquals(md5($repacked1), md5($repacked1));


    }

}