<?php
namespace App\Tests\CompilerV2\Functions;

use App\MHT;
use App\Tests\CompilerV2\LevelScripts\A01\LevelScriptA01Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

require_once __DIR__. '/../LevelScripits/A01/LevelScriptA01Test.php';

class FunctionParamLevelVarStringTest extends LevelScriptA01Test
{

    public function test()
    {

        $levelscript = $this->testGetLevel();

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            var
                lCurrentLoonieName : level_var string[30];
            
                me : string[30];
                gLoonieName : string[30];

            script OnCreate;

                begin
                    me := GetEntityName(this);
                
                    StringCopy(gLoonieName,	lCurrentLoonieName);
                        
                    WriteDebug(me, ' : OnCreate - assigned to ', gLoonieName);
                end;

            end.
        ";

        $expected = [
            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73

            '10000000', //nested call return result
            '01000000', //nested call return result

            '86000000', //GetEntityName Call

            '21000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '3c000000', //Offset in byte

            '12000000', //write to me
            '03000000', //write to me
            '1e000000', //value 30
            '10000000', //write to me
            '04000000', //write to me
            '10000000', //write to me
            '03000000', //write to me
            '48000000', //write to me

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '5c000000', //Offset glooniename

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)

            '1e000000', //value 30

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '1c000000', //string from LevelVar
            '01000000', //string from LevelVar
            '40170000', //Read value lcurrentlooniename

            '1e000000', //unknown
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1e000000', //value 30
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '6d000000', //stringcopy Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '3c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1e000000', //value 30
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //nil Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1b000000', //value 27
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '5c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '1e000000', //value 30
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '74000000', //WriteDebugFlush Call
            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call

        ];

        $compiler = new \App\Service\CompilerV2\Compiler($script, MHT::GAME_MANHUNT_2, MHT::PLATFORM_PC);
        $compiler->levelScript = $levelscript;
        $compiler->debug = true;
        $compiled = $compiler->compile();

        if ($compiler->validateCode($expected) === false){

            foreach ($compiled['CODE'] as $index => $newCode) {

                if ($expected[$index] == $newCode['code']){
                    echo $newCode['code'] . ' -> ' . $newCode['msg'] . "\n";

                }else{
                    echo "MISMATCH: Need: " . $expected[$index] . ' Got: ' . $newCode['code'] . ' -> ' . $newCode['msg']. "\n";

                }
            }
        }else{
            $this->assertEquals(true,true);
        }
    }

}