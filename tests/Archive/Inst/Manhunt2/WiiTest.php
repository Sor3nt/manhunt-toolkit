<?php
namespace App\Tests\Archive\Inst\Manhunt2;

use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WiiTest extends KernelTestCase
{

    public function testPackUnpackMh2()
    {

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Inst/Manhunt2/Wii/entity_wii.inst');

        $content = $resource->getContent();

        $inst = new Inst();

        $compressed = $inst->pack($content, true);

        $this->assertEquals(md5($resource->getBinary()), md5($compressed));

        $uncompressed = $inst->unpack($compressed);

        $this->assertEquals($content, $uncompressed);


    }


}