<?php
namespace App\Tests\Archive\Glg\Manhunt2;

use App\Service\Archive\Glg;
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
        $resource = $resources->load('/Archive/Glg/Manhunt2/PC/resource1.glg');

        $content = $resource->getContent();

        //repack and unpack again
        $zlib = new ZLib();

        $compressed = $zlib->compress($content);

        $uncompressed2 = $zlib->uncompress($compressed);

        $this->assertEquals($content, $uncompressed2);


    }

}