<?php
namespace App\Tests\CompilerV2\WriteDebug;

use App\MHT;
use PHPUnit\Framework\TestCase;

class WritedebugFunctionStringFloatTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            script OnCreate;
                var
                    pos : Vec3D;
                    ent : entityptr;
                begin
                    WriteDebug(GetEntityName(ent), ' ', pos.x);
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

            '34000000',
            '09000000',
            '10000000',


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '10000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '86000000', //GetEntityName Call

            '73000000', //writedebugstringarray Call

            '12000000', //read simple value
            '01000000', //read simple value
            '20000000', //20 == space ?
            '71000000', //write debug


            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '0c000000', //Offset in byte

            '10000000', //nested call return result
            '01000000', //nested call return result

            '0f000000', //unknown
            '02000000', //unknown

            '18000000', //unknown
            '01000000', //unknown

            '04000000', //unknown
            '02000000', //unknown

            '6f000000', //WriteDebugReal Call

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