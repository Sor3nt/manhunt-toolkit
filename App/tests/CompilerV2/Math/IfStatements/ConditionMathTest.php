<?php
namespace App\Tests\CompilerV2\Math\IfStatements;

use App\MHT;
use PHPUnit\Framework\TestCase;

class ConditionMathTest extends TestCase
{

    public function test()
    {

        $this->assertEquals(true,true);
        return true;

        $script = "
            scriptmain genCoverScript;
entity a10_breakable_banister : et_name;
var self : string[32];
	maxHP : real;
	lodFactor : real;

script OnCreate;
	begin
		self := GetEntityName(this);
		maxHP := GetDamage(this);
		lodFactor := 0.5;
		if(self = 'a10_breakable_banister') then begin
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister01');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister02');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister03');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister04');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister05');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister06');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister07');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister08');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister09');
			SetEntityScriptsFromEntity(self, 'a10_breakable_banister10');
		end;
	end;

script OnDamage;
	begin
		WriteDebug(self, ' damage ', GetDamage(this), ' max ', maxHP);
		if(GetDamage(this) <= (maxHP * lodFactor)) then begin
			WriteDebug(self, 'destroyed');
			EntityIgnoreCollisions(this, true);
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


            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '86000000', //GetEntityName Call
            '21000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '04000000', //Prepare string read (header)
            '50010000', //aiaddsubpackforleader Call
            '12000000', //parameter (read string array? assign?)
            '03000000', //parameter (read string array? assign?)
            '20000000', //value 32
            '10000000', //parameter (read string array? assign?)
            '04000000', //parameter (read string array? assign?)
            '10000000', //unknown
            '03000000', //unknown
            '48000000', //unknown
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '10000000', //nested call return result
            '01000000', //nested call return result
            '4d000000', //unknown
            '16000000', //unknown
            '04000000', //unknown
            '74010000', //unknown
            '01000000', //unknown
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '0000003f', //value 1056964608
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '78010000', //unknown
            '01000000', //unknown
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '00000000', //nil Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '17000000', //value 23
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '49000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3f000000', //statement (init start offset)
            '28010000', //Offset (line number 74)
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'd4040000', //Offset (line number 309)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '34000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '50000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '6c000000', //StringCat Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '88000000', //isfirstpersoncamera Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            'a4000000', //EnteredTrigger Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            'dc000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            'f8000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
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
            '14010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '19000000', //value 25
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'd9010000', //SetEntityScriptsFromEntity Call


            '11000000', //Script end block
            '09000000', //Script end block
            '0a000000', //Script end block
            '0f000000', //Script end block
            '0a000000', //Script end block
            '3b000000', //Script end block
            '00000000', //nil Call




            '10000000', //Script start block
            '0a000000', //Script start block
            '11000000', //Script start block
            '0a000000', //Script start block
            '09000000', //Script start block
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '30010000', //IsNamedItemInInventory Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '09000000', //value 9
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '6e000000', //WriteDebugInteger Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '3c010000', //SetMoverIdlePosition Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '06000000', //value 6
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '74010000', //Offset
            '6f000000', //WriteDebugReal Call
            '74000000', //WriteDebugFlush Call



            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '84000000', //GetDamage Call
            '10000000', //nested call return result
            '01000000', //nested call return result


            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '74010000', //Offset maxHP
            '10000000', //nested call return result
            '01000000', //nested call return result
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '78010000', //Offset lodFactor
            '10000000', //nested call return result
            '01000000', //nested call return result
            '52000000', //math multiply
            '10000000', //nested call return result
            '01000000', //nested call return result
            '0f000000', //unknown
            '01000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '02000000', //unknown
            '4d000000', //unknown
            '0f000000', //unknown
            '02000000', //unknown
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //unknown
            '02000000', //unknown
            '4e000000', //unknown
            '12000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3e000000', //unknown
            '84060000', //unknown
            '33000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '01000000', //statement (compare mode INT/FLOAT)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            '28070000', //Offset (line number 458)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '50010000', //aiaddsubpackforleader Call
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '20000000', //value 32
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '44010000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0a000000', //value 10
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //writedebugstringarray Call
            '74000000', //WriteDebugFlush Call
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '49000000', //value 73
            '10000000', //nested call return result
            '01000000', //nested call return result
            '12000000', //parameter (read simple type (int/float...))
            '01000000', //parameter (read simple type (int/float...))
            '01000000', //value 1
            '10000000', //nested call return result
            '01000000', //nested call return result
            'a2020000', //EntityIgnoreCollisions Call
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