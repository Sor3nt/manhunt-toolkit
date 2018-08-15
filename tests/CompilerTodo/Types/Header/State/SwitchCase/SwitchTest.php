<?php
namespace App\Tests\CompilerByType\Header\State\SwitchCase;


use App\Service\Compiler\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwitchTest extends KernelTestCase
{

    public function test()
    {

        $script = "
            scriptmain LevelScript;

            type
                typeHolder = ( StartOfLevel, PickedUpSyringe, InOffice, LuredHunter, KilledHunter, BeforeElevator, LeftElevator, BeforeBeasts, SpottedByCamera, TurnedOnTV, InCarPark, EndOfLevel );
            
            VAR
            	lLevelState : typeHolder;
                me : string[16];
                
            script OnCreate;
                begin
                    case lLevelState of
                        StartOfLevel: AIPlayCommunication(me,'ShoutForHelp','ScriptedAudio');
                        PickedUpSyringe: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        InOffice: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
                        BeforeElevator: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
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

            
            '14000000', //read from header var
            '01000000', //read from header var
            '04000000', //read from header var
            '24170000', //Offset

            '24000000', //define case
            '01000000', //define case
            '05000000', //case number 3
            '3f000000', //statement (init start offset)
            '7c000000', //Offset (line number 3295)
            
            '24000000', //define case
            '01000000', //define case
            '02000000', //case number 2
            '3f000000', //statement (init start offset)
            '0c010000', //Offset (line number 3331)
            
            '24000000', //define case
            '01000000', //define case
            '01000000', //case number 1
            '3f000000', //statement (init start offset)
            '9c010000', //Offset (line number 3367)

            '24000000', //define case
            '01000000', //define case
            '00000000', //case number 0
            '3f000000', //statement (init start offset)
            '2c020000', //Offset (line number 3403)
            
            '3c000000', //statement (init statement start offset)
            'bc020000', //End Offset (line number 3439)


            // 3: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '38000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '20000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call




            // 2: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');


            '3c000000', //statement (init statement start offset)
            'bc020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '38000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '20000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call



            // 1: AIPlayCommunication(me,'ShoutForAssistance','ScriptedAudio');

            '3c000000', //statement (init statement start offset)
            'bc020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '38000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '20000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '13000000', //value 19
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call


            // 0: AIPlayCommunication(me,'ShoutForHelp','ScriptedAudio');

            '3c000000', //statement (init statement start offset)
            'bc020000', //End Offset (line number 3439)

            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '38000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '10000000', //value 16
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '00000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0d000000', //value 13
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            '21000000', //Prepare string read (DATA table)
            '04000000', //Prepare string read (DATA table)
            '01000000', //Prepare string read (DATA table)
            '10000000', //Offset in byte
            '12000000', //parameter (Read String var)
            '02000000', //parameter (Read String var)
            '0e000000', //value 14
            '10000000', //nested call return result
            '01000000', //nested call return result
            '10000000', //nested string return result
            '02000000', //nested string return result
            'fe010000', // AIPlayCommunication call




            '3c000000', //statement (init statement start offset)
            'bc020000', //End Offset (line number 3439)
                 
            
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
        list($sectionCode, $sectionDATA) = $compiler->parse($script);

        if ($sectionCode != $expected){
            foreach ($sectionCode as $index => $item) {
                if ($expected[$index] == $item){
                    echo ($index + 1) . '->' . $item . "\n";
                }else{
                    echo "MISSMATCH need " . $expected[$index] . " got " . $sectionCode[$index] . "\n";
                }
            }
            exit;
        }

        $this->assertEquals($sectionCode, $expected, 'The bytecode is not correct');
    }

}