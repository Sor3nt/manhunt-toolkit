<?php
namespace App\Tests\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DummyTest extends KernelTestCase
{

    public function test()
    {






        $script = "
                    if stealthOneLooper = TRUE then
                    begin
                        stealthOneLooper := 1;
                    end
                    else if (stealthOneLooper = TRUE)  then
                    begin
                        stealthOneLooper := 2;
                    end
                    else
                    begin
                        stealthOneLooper := 3;

                    end;
        ";
//


        $compiler = new Compiler();
        list($sectionCode, $sectionDATA) = $compiler->parse($script);

        var_dump($sectionCode);
    }

}