<?php
namespace App\Tests\CompilerByType\FunctionCalls;


use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwitchTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            entity
                A01_Escape_Asylum : et_level;
                
            var
                var	self : string[32];
                
            script OnCreate;
                begin

                    case randnum(19) of
						 0: AISetIdleHomeNode(self, 'nBase01');
						 1: AISetIdleHomeNode(self, 'nBase02');
						 2: AISetIdleHomeNode(self, 'nBase03');
						 3: AISetIdleHomeNode(self, 'nBase04');
						 4: AISetIdleHomeNode(self, 'nBase05');
						 5: AISetIdleHomeNode(self, 'nBase06');
						 6: AISetIdleHomeNode(self, 'nBase07');
						 7: AISetIdleHomeNode(self, 'nBase08');
						 8: AISetIdleHomeNode(self, 'nBase09');
						 9: AISetIdleHomeNode(self, 'nBase10');
						10: AISetIdleHomeNode(self, 'nBase11');
						11: AISetIdleHomeNode(self, 'nBase12');
						12: AISetIdleHomeNode(self, 'nBase13');
						13: AISetIdleHomeNode(self, 'nBase14');
						14: AISetIdleHomeNode(self, 'nBase15');
						15: AISetIdleHomeNode(self, 'nBase16');
						16: AISetIdleHomeNode(self, 'nBase17');
						17: AISetIdleHomeNode(self, 'nBase18');
						18: AISetIdleHomeNode(self, 'nBase19');
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



            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '69000000', //randnum Call
            '24000000', //unknown
            '01000000', //unknown
            '12000000', //unknown
            '3f000000', //statement (init start offset)
            'b0010000', //Offset (line number 1246)
            '24000000', //unknown
            '01000000', //unknown
            '11000000', //unknown
            '3f000000', //statement (init start offset)
            '14020000', //Offset (line number 1271)
            '24000000', //unknown
            '01000000', //unknown
            '10000000', //unknown
            '3f000000', //statement (init start offset)
            '78020000', //Offset (line number 1296)
            '24000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '3f000000', //statement (init start offset)
            'dc020000', //Offset (line number 1321)
            '24000000', //unknown
            '01000000', //unknown
            '0e000000', //unknown
            '3f000000', //statement (init start offset)
            '40030000', //Offset (line number 1346)
            '24000000', //unknown
            '01000000', //unknown
            '0d000000', //unknown
            '3f000000', //statement (init start offset)
            'a4030000', //Offset (line number 1371)
            '24000000', //unknown
            '01000000', //unknown
            '0c000000', //unknown
            '3f000000', //statement (init start offset)
            '08040000', //Offset (line number 1396)
            '24000000', //unknown
            '01000000', //unknown
            '0b000000', //unknown
            '3f000000', //statement (init start offset)
            '6c040000', //Offset (line number 1421)
            '24000000', //unknown
            '01000000', //unknown
            '0a000000', //unknown
            '3f000000', //statement (init start offset)
            'd0040000', //Offset (line number 1446)
            '24000000', //unknown
            '01000000', //unknown
            '09000000', //unknown
            '3f000000', //statement (init start offset)
            '34050000', //Offset (line number 1471)
            '24000000', //unknown
            '01000000', //unknown
            '08000000', //unknown
            '3f000000', //statement (init start offset)
            '98050000', //Offset (line number 1496)
            '24000000', //unknown
            '01000000', //unknown
            '07000000', //unknown
            '3f000000', //statement (init start offset)
            'fc050000', //Offset (line number 1521)
            '24000000', //unknown
            '01000000', //unknown
            '06000000', //unknown
            '3f000000', //statement (init start offset)
            '60060000', //Offset (line number 1546)
            '24000000', //unknown
            '01000000', //unknown
            '05000000', //unknown
            '3f000000', //statement (init start offset)
            'c4060000', //Offset (line number 1571)
            '24000000', //unknown
            '01000000', //unknown
            '04000000', //unknown
            '3f000000', //statement (init start offset)
            '28070000', //Offset (line number 1596)
            '24000000', //unknown
            '01000000', //unknown
            '03000000', //unknown
            '3f000000', //statement (init start offset)
            '8c070000', //Offset (line number 1621)
            '24000000', //unknown
            '01000000', //unknown
            '02000000', //unknown
            '3f000000', //statement (init start offset)
            'f0070000', //Offset (line number 1646)
            '24000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3f000000', //statement (init start offset)
            '54080000', //Offset (line number 1671)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'b8080000', //Offset (line number 1696)
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'd8000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'cc000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'c0000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'b4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'a8000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '9c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '90000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '84000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '78000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '6c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '60000000', //AIEntityPlayAnimLooped Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '54000000', //offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '48000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '3c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '30000000', //setvector Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '24000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '18000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '0c000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            'e4000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //offset
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '08000000', //value 8
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '83010000', //AISetIdleHomeNode Call
            '3c000000', //statement (init statement start offset)
            '1c090000', //Offset (line number 1721)


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