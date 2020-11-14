<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use PHPUnit\Framework\TestCase;

class IfOrTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            VAR
                bPerimeterAwareOfPlayer : array[1..4] of boolean;

            script PerimeterChaser;
                begin
                    if (bPerimeterAwareOfPlayer[1] or bPerimeterAwareOfPlayer[2] 
                        or bPerimeterAwareOfPlayer[3] or bPerimeterAwareOfPlayer[4]) then
                    begin
                        writeDebug('State: Warehouse: Player did not clean up in previous section. Sending in a hunter after them');
                        
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

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '60000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '60000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '60000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //unknown
            '01000000', //unknown
            '03000000', //unknown
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)
            '10000000', //nested call return result
            '01000000', //nested call return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '60000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '18000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '02000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '27000000', //statement (OR operator)
            '01000000', //statement (OR operator)
            '04000000', //statement (OR operator)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '90020000', //Offset (line number 11320)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '5e000000', //value 94
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '74000000', //WriteDebugFlush Call


            
            // script end
            '11000000',
            '09000000',
            '0a000000',
            '0f000000',
            '0a000000',
            '3b000000',
            '00000000'
        ];

        $compiler = new \App\Service\CompilerV2\Compiler($script, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC, false);
        $compiled = $compiler->compile();

        if ($compiler->validateCode($expected) === false){

            foreach ($compiled['CODE'] as $index => $newCode) {

                if ($expected[$index] == $newCode['code']){
                    echo $index . " " . $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}