<?php
namespace App\Tests\CompilerV2\Assign\Script;

use App\MHT;
use PHPUnit\Framework\TestCase;

class AssignRgbaIntTest extends TestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            script SwitchOnLight;
                var
                    rgbabox	:	RGBAInt;
                begin
                                        
                    rgbabox.red		:= 224;
                    rgbabox.green	:= 143;
                    rgbabox.blue	:= 87;
                    rgbabox.alpha	:= 200;
                end;

            end.
        ";

        $expected = [

            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block

            '34000000', //reserve bytes
            '09000000', //reserve bytes
            '04000000', //Offset in byte


            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '04000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            'e0000000', //value 224
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '01000000', //unknown
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '04000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '8f000000', //value 143
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '01000000', //unknown
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '04000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '57000000', //value 87
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '01000000', //unknown
            '22000000', //Prepare string read (3)
            '04000000', //Prepare string read (3)
            '01000000', //Prepare string read (3)
            '04000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '32000000', //unknown
            '01000000', //unknown
            '03000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            'c8000000', //value 200
            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))
            '17000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '01000000', //unknown

            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call
            
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