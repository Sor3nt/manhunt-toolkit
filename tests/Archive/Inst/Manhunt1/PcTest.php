<?php
namespace App\Tests\Archive\Inst\Manhunt1;

use App\Service\Archive\Inst;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstTest extends KernelTestCase
{

    public function testPackUnpack()
    {

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $resource = $resources->load('/Archive/Inst/Manhunt1/PC/entity.inst');

        $content = $resource->getContent();

        $inst = new Inst();

        $compressed = $inst->pack($content);

        $this->assertEquals(md5($resource->getBinary()), md5($compressed));

        $uncompressed = $inst->unpack($compressed);

        $this->assertEquals($content, $uncompressed);

    }

}