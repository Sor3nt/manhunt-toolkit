<?php
namespace App\Tests\CompilerByType\Header\Boolean\Assign;

use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OfCustomTest extends KernelTestCase
{

    public function test()
    {

//        return true;

        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;

            
            type 	
                Searchable 	= record
                    Searched 	: Boolean;
                    StashHere 	: Boolean;
                    SpawnPos 	: Vec3d;
                end;

            
            var 	
                Searchables : array [1..9] of Searchable;
            
            
            script OnCreate;
                var 
                    i : integer;

                begin
                    for i := 1 to 9 do begin
                        Searchables[i].Searched 	:= false;
                        Searchables[i].StashHere 	:= false;				
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

            "34000000",
            "09000000",
            "04000000",

            //T_FOR
            '12000000', //unknown
            '01000000', //unknown

            '01000000', //start offset

            '15000000', //T_FOR
            '04000000', //T_FOR
            '04000000', //$incrementVarMapped
            '01000000', //T_FOR

            '12000000', //not inside t_for?
            '01000000', //not inside t_for?
            '09000000', //not inside t_for?

            '13000000', //T_FOR
            '02000000', //T_FOR
            '04000000', //T_FOR
            '04000000', //$incrementVarMapped

            '23000000', //T_FOR
            '01000000', //T_FOR
            '02000000', //T_FOR
            '41000000', //T_FOR

            '74000000', //T_FOR $startLineNumber * 4

            '3c000000', //statement (init statement start offset)
            'b4010000', //Offset (line number 102)





            //Searchables[i].Searched 	:= false;

            //Searchables
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset Searchables
            '10000000', //nested call return result
            '01000000', //nested call return result

            //read [i]
            '13000000', //read from type object
            '01000000', //read from type object
            '04000000', //read from type object
            '04000000', //read from type object

            //access [i] ?
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '14000000', //size of ofVar
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown

            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0

            '0f000000', //T_ASSIGN: toObject
            '02000000', //T_ASSIGN: toObject
            '17000000', //T_ASSIGN: toObject
            '04000000', //T_ASSIGN: toObject
            '02000000', //T_ASSIGN: toObject
            '01000000', //T_ASSIGN: toObject










            //Searchables[i].StashHere 	:= false;


            //Searchables
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset Searchables
            '10000000', //nested call return result
            '01000000', //nested call return result

            //read [i]
            '13000000', //read from script var
            '01000000', //read from script var
            '04000000', //read from script var
            // i index
            '04000000', //Offset

            //access array index
            '34000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '04000000', //unknown
            '14000000', //offset
            '35000000', //unknown
            '04000000', //unknown
            '0f000000', //unknown
            '04000000', //unknown
            '31000000', //unknown
            '04000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '04000000', //unknown


            '0f000000', //T_ASSIGN: fromObjectAttribute
            '01000000', //T_ASSIGN: fromObjectAttribute
            '32000000', //T_ASSIGN: fromObjectAttribute
            '01000000', //T_ASSIGN: fromObjectAttribute
            '04000000', //OFFSET
            '10000000', //T_ASSIGN: fromObjectAttribute
            '01000000', //T_ASSIGN: fromObjectAttribute

            //Assign
            '12000000', //parameter (function return (bool?))
            '01000000', //parameter (function return (bool?))
            '00000000', //value 0

            '0f000000', //T_ASSIGN: toObject
            '02000000', //T_ASSIGN: toObject
            '17000000', //T_ASSIGN: toObject
            '04000000', //T_ASSIGN: toObject
            '02000000', //T_ASSIGN: toObject
            '01000000', //T_ASSIGN: toObject





            '2f000000', //T_FOR
            '04000000', //T_FOR

            '00000000', //T_FOR not a function call
            '3c000000', //T_FOR
            '3c000000', //T_FOR start offset

            '30000000', //T_FOR
            '04000000', //T_FOR

            '00000000', //T_FOR not a function call
            
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