<?php
namespace App\Tests\CompilerV2\Assign\Header;

use App\MHT;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AssignArrayObjectTest extends KernelTestCase
{

    public function test()
    {


        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;

            type 	
                Searchable 	= record
                    Searched 		: Boolean;
                    StashHere 	: Boolean;
                    SpawnPos 		: Vec3d;
                end;
            
            VAR
        		Searchables : array [1..9] of Searchable;

            script OnCreate;
                var 
                    i : integer;

                Begin
                    for i := 1 to 9 do begin
                         Searchables[i].Searched 	:= false; 
                         Searchables[i].StashHere 	:= false; 				
                    end;
                End;

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

            //for i := 1 to 9 do begin
            //from 1
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //from 1

            '15000000', //unknown
            '04000000', //unknown
            '04000000', //unknown
            '01000000', //unknown

            // to 9
            '12000000', //unknown
            '01000000', //unknown
            '09000000', //to 9

            '13000000', //For statement
            '02000000', //For statement
            '04000000', //For statement
            '04000000', //index offset

            '23000000', //For statement
            '01000000', //For statement
            '02000000', //For statement
            '41000000', //For statement
            '74000000', //Line Offset first command

            '3c000000', //Jump To
            'b4010000', //Line Offset end of for

//
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '34000000', //Read array
            '01000000', //Read array
            '01000000', //Read array
            '12000000', //Read array
            '04000000', //Read array
            '14000000', //Read array offset
            '35000000', //Read array
            '04000000', //Read array
            '0f000000', //Read array
            '04000000', //Read array
            '31000000', //Read array
            '04000000', //Read array
            '01000000', //Read array
            '10000000', //Read array
            '04000000', //Read array

            //Searchables[i].Searched 	:= false;
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0

            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))

            '17000000', //write to searchables
            '04000000', //write to searchables
            '02000000', //write to searchables
            '01000000', //write to searchables





            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '10000000', //nested call return result
            '01000000', //nested call return result
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            '04000000', //Offset

            '34000000', //Read array
            '01000000', //Read array
            '01000000', //Read array
            '12000000', //Read array
            '04000000', //Read array
            '14000000', //Read array offset
            '35000000', //Read array
            '04000000', //Read array
            '0f000000', //Read array
            '04000000', //Read array
            '31000000', //Read array
            '04000000', //Read array
            '01000000', //Read array
            '10000000', //Read array
            '04000000', //Read array

            '0f000000', //Move Attribute Pointer
            '01000000', //Move Attribute Pointer
            '32000000', //Move Attribute Pointer
            '01000000', //Move Attribute Pointer
            '04000000', //Move Attribute Pointer offset


            '10000000', //nested call return result
            '01000000', //nested call return result



            //Searchables[i].StashHere 	:= false;
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0


            '0f000000', //parameter (function return (bool?))
            '02000000', //parameter (function return (bool?))

            '17000000', //write to
            '04000000', //write to
            '02000000', //write to
            '01000000', //write to




            '2f000000', //For statement
            '04000000', //For statement
            '00000000', //line offset

            '3c000000', //jump to
            '3c000000', //start offset

            '30000000', //For statement
            '04000000', //For statement
            '00000000', //line offset variable offset




            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //Script end block
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