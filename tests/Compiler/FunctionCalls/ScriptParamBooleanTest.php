<?php
namespace App\Tests\FunctionCalls;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScriptParamBooleanTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            script OnCreate;
                VAR
                    animLength : boolean;
                    
                begin
                	sleep(animLength);
                end;

            end.
        ";


        $compiler = new Compiler();

        try{
            list($sectionCode, $sectionDATA) = $compiler->parse($script);

            $this->fail('Except an error');
        }catch(\Exception $e){
            $this->assertEquals('You can not assign a boolena variable to a function!', $e->getMessage(), 'Other error orccured');

        }
    }

}