<?php
namespace App\Tests\Special;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForwardTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
    
                entity
                    A01_Escape_Asylum : et_level;
    
                VAR
                    alreadyDone : boolean;
    
                procedure InitAI; FORWARD;
    
                script OnCreate;
                    begin
                        alreadyDone := FALSE;
                    end;
                    
                    
                
                procedure InitAI;
                    begin
                            alreadyDone := TRUE;
                    end;

            end.
            
        ";

        $expected = [
            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', // init parameter
            '01000000', // init parameter
            '01000000', // value int 1
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign


            // procedure end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '04000000',


            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', // init parameter
            '01000000', // init parameter
            '00000000', // value int 0
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

        ];
        $compiler = new Compiler();
        $compiled = $compiler->parse($script);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }

    public function testMulti()
    {

        $script = "
            scriptmain LevelScript;
    
            entity
                A01_Escape_Asylum : et_level;

            VAR
                alreadyDone : boolean;

            procedure InitAI; FORWARD;
            procedure InitAI2; FORWARD;

            script OnCreate;
                begin
                    alreadyDone := FALSE;
                end;



            procedure InitAI;
            begin
                    alreadyDone := TRUE;
            end;

            procedure InitAI2;
            begin
                    alreadyDone := FALSE;
            end;

            end.

        ";

        $expected = [

            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', // init parameter
            '01000000', // init parameter
            '01000000', // value int 1
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign


            // procedure end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '04000000',


            // procedure start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

            '12000000', // init parameter
            '01000000', // init parameter
            '00000000', // value int 1
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign

            // procedure end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3a000000',
            '04000000',

            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',


            '12000000', // init parameter
            '01000000', // init parameter
            '00000000', // value int 0
            '16000000', // assign to script var
            '04000000', // assign to script var
            '00000000', // save into alreadyDone
            '01000000', // assign


            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000',

        ];

        $compiler = new Compiler();
        $compiled = $compiler->parse($script);

        if ($compiled['CODE'] != $expected){
            foreach ($compiled['CODE'] as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $compiled['CODE'][$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($compiled['CODE'], $expected, 'The bytecode is not correct');
    }

}