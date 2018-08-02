<?php
namespace App\Tests\Compiler\Statements;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WhileShortSmallAnd extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            var
                Invul: boolean;

            script OnCreate;
                begin
                    
                    while Invul = TRUE AND IsEntityAlive('SobbingWoman(hunter)') do                     { offset: 30020000 (560) }
                    begin
                        if GetDamage(GetPlayer) < 30 then
                        begin
                            SetEntityInvulnerable(GetPlayer, TRUE);
                            Invul := FALSE;                                                             
                        end;
                    end;
                    
    
                end;

            end.
        ";

        $expected = [
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',



            #Invul
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '18000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            #TRUE
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result


            #'SobbingWoman(hunter)'
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '15000000', //value 21
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result

            #IsEntityAlive
            'aa010000', //IsEntityAlive Call


            '0f000000', //unknown
            '04000000', //unknown
            '25000000', //statement (AND operator)
            '01000000', //statement (AND operator)
            '04000000', //statement (AND operator)
            '0f000000', //unknown
            '04000000', //unknown


            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)

            '3f000000', //statement (T_IS_EQUAL)

            'b8000000', //Offset (line number 1652)

            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)



            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '80010000', //Offset (line number 1702)
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (temp int)
            '01000000', //parameter (temp int)
            '1e000000', //value 30
            '0f000000', //parameter (temp int)
            '04000000', //parameter (temp int)

            '23000000', //statement (core)
            '04000000', //statement (core)
            '01000000', //statement (core)
            '12000000', //statement (core)
            '01000000', //statement (core)
            '01000000', //statement (core)
            '3d000000', //statement (core)(operator smaller)
            '24010000', //statement (core)( Offset )
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '78010000', //Offset (line number 1700)
            '8a000000', //GetPlayer Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            '5e010000', //SetEntityInvulnerable Call
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '18000000', //unknown
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            '14000000', //Offset (line number 1611)


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
        list($sectionCode, $sectionDATA) = $compiler->parse($script);


        if ($sectionCode != $expected){
            foreach ($sectionCode as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $sectionCode[$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

}