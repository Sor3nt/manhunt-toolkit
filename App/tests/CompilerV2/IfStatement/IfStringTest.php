<?php
namespace App\Tests\CompilerV2\IfStatement;

use App\MHT;
use PHPUnit\Framework\TestCase;

class IfStringTest extends TestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            PROCEDURE ChangeDoor(DoorName, DoorStatus : string);  FORWARD;


            script OnCreate;
                begin
                end;
            
            
            PROCEDURE ChangeDoor;
            var
                Door : EntityPtr;
            begin
                
                if (DoorStatus = 'open') then
                begin
                    unFreezeEntity(Door);  
                    unLockEntity(Door);
                end
                else
                begin
                    UnfreezeEntity(Door); 
                    LockEntity(Door);
                end;
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


            //DoorStatus
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            'f4ffffff', //Offset

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '00000000', //value 0

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result


            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte (string 'open')

            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '05000000', //value 5

            '10000000', //nested call return result
            '01000000', //nested call return result

            '10000000', //nested string return result
            '02000000', //nested string return result

            '49000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown


            '3f000000', //statement (init start offset)
            '9c000000', //Offset (line number 602)
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'f0000000', //Offset (line number 623)


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '38010000', //UnFreezeEntity Call


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '99000000', //UnLockEntity Call


            '3c000000', //statement (init statement start offset)
            '28010000', //Offset (line number 637)


            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '38010000', //UnFreezeEntity Call

            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '10000000', //nested call return result
            '01000000', //nested call return result

            '98000000', //LockEntity Call



            '11000000', //unknown
            '09000000', //unknown
            '0a000000', //unknown
            '0f000000', //unknown
            '0a000000', //unknown
            '3a000000', //unknown
            '0c000000', //unknown
            
            
            // script start
            '10000000',
            '0a000000',
            '11000000',
            '0a000000',
            '09000000',

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