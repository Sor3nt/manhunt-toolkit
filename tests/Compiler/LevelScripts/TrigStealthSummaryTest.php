<?php
namespace App\Tests\LevelScripts;

use App\Bytecode\Helper;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrigStealthSummaryTest extends KernelTestCase
{

    public function test()
    {
        $script = "
scriptmain trigStealthSummary;

ENTITY
	triggerStealthSummary : et_name;
	
VAR
	stealthTutSpotted : level_var integer;
	
script OnEnterTrigger;
begin
	
	writedebug('Summary Entered');
	
    
    while NOT FrisbeeSpeechIsFinished('LEO37') do sleep(10);
    
    if stealthTutSpotted = 0 then begin
        FrisbeeSpeechPlay('LEO35', 100,10);
        while NOT FrisbeeSpeechIsFinished('LEO35') do sleep(10);
    end else if stealthTutSpotted = 1 then begin
        FrisbeeSpeechPlay('LEO36', 100,10);
        while NOT FrisbeeSpeechIsFinished('LEO31') do sleep(10);
    
    end else if stealthTutSpotted >= 2 then  begin
        FrisbeeSpeechPlay('LEO37', 100,100);
        while NOT FrisbeeSpeechIsFinished('LEO37') do sleep(10);
    end;
    
    ClearLevelGoal('GOAL3');
    ClearLevelGoal('GOAL3C');
    ClearLevelGoal('GOAL3D');
    ClearLevelGoal('GOAL3B');
    
RemoveThisScript;
  
end;

end.


        ";

        $expected = [

'10000000', //Script start block
'0a000000', //Script start block
'11000000', //Script start block
'0a000000', //Script start block
'09000000', //Script start block
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'00000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'10000000', //value 16
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'73000000', //writedebug Call
'74000000', //writedebugflush Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'69030000', //frisbeespeechisfinished Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'b8000000', //Offset (line number 46)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'48000000', //Offset (line number 18)
'1b000000', //unknown
'a0170000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'00000000', //value 0
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'10010000', //Offset (line number 68)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'f4010000', //Offset (line number 125)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'1c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'69030000', //frisbeespeechisfinished Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'ec010000', //Offset (line number 123)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'7c010000', //Offset (line number 95)
'3c000000', //statement (init statement start offset)
'64040000', //Offset (line number 281)
'1b000000', //unknown
'a0170000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'01000000', //value 1
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'3f000000', //statement (init start offset)
'4c020000', //Offset (line number 147)
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'30030000', //Offset (line number 204)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'24000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'2c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'69030000', //frisbeespeechisfinished Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'28030000', //Offset (line number 202)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'b8020000', //Offset (line number 174)
'3c000000', //statement (init statement start offset)
'64040000', //Offset (line number 281)
'1b000000', //unknown
'a0170000', //unknown
'04000000', //unknown
'01000000', //unknown
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (temp int)
'01000000', //parameter (temp int)
'02000000', //value 2
'0f000000', //parameter (temp int)
'04000000', //parameter (temp int)
'23000000', //statement (core)
'04000000', //statement (core)
'01000000', //statement (core)
'12000000', //statement (core)
'01000000', //statement (core)
'01000000', //statement (core)
'41000000', //statement (core)(operator unknwon)
'88030000', //statement (core)( Offset )
'33000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'01000000', //statement (compare mode INT/FLOAT)
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'64040000', //Offset (line number 281)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'64000000', //value 100
'10000000', //nested call return result
'01000000', //nested call return result
'66030000', //frisbeespeechplay Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'14000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'69030000', //frisbeespeechisfinished Call
'29000000', //NOT
'01000000', //NOT
'01000000', //NOT
'24000000', //statement (end sequence)
'01000000', //statement (end sequence)
'00000000', //statement (end sequence)
'3f000000', //statement (init start offset)
'64040000', //Offset (line number 281)
'12000000', //parameter (read simple type (int/float...))
'01000000', //parameter (read simple type (int/float...))
'0a000000', //value 10
'10000000', //nested call return result
'01000000', //nested call return result
'6a000000', //Sleep Call
'3c000000', //statement (init statement start offset)
'f4030000', //Offset (line number 253)
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'34000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'06000000', //value 6
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //clearlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'3c000000', //statement (init statement start offset)
'12000000', //Offset (line number 4.5)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //clearlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'44000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //clearlevelgoal Call
'21000000', //Prepare string read (DATA table)
'04000000', //Prepare string read (DATA table)
'01000000', //Prepare string read (DATA table)
'4c000000', //Offset in byte
'12000000', //parameter (Read String var)
'02000000', //parameter (Read String var)
'07000000', //value 7
'10000000', //nested call return result
'01000000', //nested call return result
'10000000', //nested string return result
'02000000', //nested string return result
'42020000', //clearlevelgoal Call
'e8000000', //RemoveThisScript Call
'11000000', //Script end block
'09000000', //Script end block
'0a000000', //Script end block
'0f000000', //Script end block
'0a000000', //Script end block
'3b000000', //Script end block
'00000000', //Script end block
        ];


        $compiler = new Compiler();
        $levelScriptCompiled = $compiler->parse(file_get_contents(__DIR__ . '/0#levelscript.srce'));

        $compiler = new Compiler();
        $compiled = $compiler->parse($script, $levelScriptCompiled);

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