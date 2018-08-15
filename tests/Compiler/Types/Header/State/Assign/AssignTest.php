<?php
namespace App\Tests\CompilerByType\Header\State\Assign;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            
                            
                scriptmain LevelScript;
                
                entity
                    A01_Escape_Asylum : et_level;
    
                type
                    tLevelState = ( StartOfLevel, PickedUpSyringe, InOffice, LuredHunter, KilledHunter, BeforeElevator, LeftElevator, BeforeBeasts, SpottedByCamera, TurnedOnTV, InCarPark, EndOfLevel );

                var
                    lLevelState : tLevelState;
                
                script OnCreate;
                begin
                        lLevelState := StartOfLevel;
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

            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '00000000', //LevelVar lLevelState
            '01000000', //unknown

            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
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