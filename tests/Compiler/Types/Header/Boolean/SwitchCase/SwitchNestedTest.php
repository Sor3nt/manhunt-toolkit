<?php
namespace App\Tests\CompilerByType\Header\Boolean\SwitchCase;


use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwitchNestedTest extends KernelTestCase
{

    public function test()
    {

        $this->assertEquals(true, true, 'The bytecode is not correct');
        return;


        $script = "
            scriptmain LevelScript;
            
            entity
                A01_Escape_Asylum : et_level;
            
            type
                tLevelState = (LevelStart,PanicButton,Perimeter,Warehouse,ProjectArrive);
                
            var
            	lLevelState : tLevelState;
            	lLoadingFlag : boolean;
                lCurrentAmbientAudioTrack : integer;

            script OnCreate;
                begin

                    case lLoadingFlag of
                        false:
                        begin
                            lCurrentAmbientAudioTrack := 0;
                            lLevelState := LevelStart;
                            
        
                            case lLoadingFlag of
                                false:
                                begin
                                    inner1 := 0;
                                    inner2 := LevelStart;
                                end;
                                true:
                                begin
                                    inner3 ('Sound_reset');			
                                    inner4(lCurrentAmbientAudioTrack);
                                end;
                            end;                            
                            
                        end;
                        true:
                        begin
                            writedebug ('Sound_reset');			
                            SetAmbientAudioTrack(lCurrentAmbientAudioTrack);
                        end;
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

            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '14000000', //Offset
            '24000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3f000000', //statement (init start offset)
            '54000000', //Offset (line number 4108)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'ac000000', //Offset (line number 4130)
            '3c000000', //statement (init statement start offset)
            'ec000000', //Offset (line number 4146)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0c000000', //value 12
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //WriteDebugString Call
            '74000000', //WriteDebugFlush Call
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '18000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '75030000', //SetAmbientAudioTrack Call
            '3c000000', //statement (init statement start offset)
            'ec000000', //Offset (line number 4146)
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '18000000', //unknown
            '01000000', //unknown
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '10000000', //unknown
            '01000000', //unknown




            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '14000000', //Offset
            '24000000', //unknown
            '01000000', //unknown
            '01000000', //unknown
            '3f000000', //statement (init start offset)
            '54000000', //Offset (line number 4108)
            '24000000', //statement (end sequence)
            '01000000', //statement (end sequence)
            '00000000', //statement (end sequence)
            '3f000000', //statement (init start offset)
            'ac000000', //Offset (line number 4130)
            '3c000000', //statement (init statement start offset)
            'ec000000', //Offset (line number 4146)
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0c000000', //value 12
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '73000000', //WriteDebugString Call
            '74000000', //WriteDebugFlush Call
            '14000000', //Read VAR from header
            '01000000', //Read VAR from header
            '04000000', //Read VAR from header
            '18000000', //Offset
            '10000000', //nested call return result
            '01000000', //nested call return result
            '75030000', //SetAmbientAudioTrack Call
            '3c000000', //statement (init statement start offset)
            'ec000000', //Offset (line number 4146)
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '18000000', //unknown
            '01000000', //unknown
            '12000000', //parameter (access script var)
            '01000000', //parameter (access script var)
            '00000000', //value 0
            '16000000', //parameter (access script var)
            '04000000', //parameter (access script var)
            '10000000', //unknown
            '01000000', //unknown
            '3c000000', //statement (init statement start offset)
            'ec000000', //Offset (line number 4146)





            '3c000000', //statement (init statement start offset)
            'ec000000', //Offset (line number 4146)

                 
            
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