<?php
namespace App\Tests\Archive\Mls\Extract\Manhunt2;

use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PSPTest extends KernelTestCase
{

    public function testLevel1()
    {

        $resources = new Resources();
        $resources->workDirectory = explode("/tests/", __DIR__)[0] . "/tests/Resources";
        $mlsContent = $resources->load('/Archive/Mls/Manhunt2/PSP/A01_ES.MLS');

        $mhls = $mlsContent->getContent();

        //93 scripts inside level 1 (only for psp)
        $this->assertEquals(93, count($mhls));

        $this->assertEquals(true, isset($mhls[0]['SCPT']));
        $this->assertEquals(true, isset($mhls[0]['NAME']));
        $this->assertEquals(true, isset($mhls[0]['ENTT']));
        $this->assertEquals(true, isset($mhls[0]['CODE']));
        $this->assertEquals(true, isset($mhls[0]['DATA']));
        $this->assertEquals(true, isset($mhls[0]['SMEM']));
        $this->assertEquals(true, isset($mhls[0]['STAB']));

        $this->assertEquals('levelscript', $mhls[0]['NAME']);
        $this->assertEquals('newmeleetut2', $mhls[1]['NAME']);
        $this->assertEquals('objectscript', $mhls[92]['NAME']);

    }
}