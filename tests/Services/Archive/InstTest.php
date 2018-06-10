<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstTest extends KernelTestCase
{

    public function testPackUnpackMh2()
    {

        $content = file_get_contents(__DIR__ . '/../../Resources/entity_pc.inst');

        $this->assertEquals('81027a46c078ae7de832e58591fa6e30', md5($content));

        $inst = new Inst();

        $uncompressed = $inst->unpack($content);

        $this->assertEquals('3dc193095233006f92bed746061cade6', md5(serialize($uncompressed)));

        $compressed = $inst->pack($uncompressed);
        $uncompressed2 = $inst->unpack($compressed);

        $this->assertEquals('3dc193095233006f92bed746061cade6', md5(serialize($uncompressed2)));
        $this->assertEquals($uncompressed, $uncompressed2);


    }

}