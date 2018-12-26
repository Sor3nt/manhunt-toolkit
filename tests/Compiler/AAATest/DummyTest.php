<?php
namespace App\Tests\AAATest;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DummyTest extends KernelTestCase
{

    public function test()
    {

        echo "\n*** COMPILER: Start component tests ==>  ";

        $this->assertEquals(true, true);
    }

}