<?php
namespace App\Service\Archive;

use App\Bytecode\Helper;
use App\Service\Binary;

class Bin {


    private function toString( $hex ){
        $hex = str_replace('00', '', $hex);
        return hex2bin($hex);
    }

    private function toInt( $hex ){
        return (int) current(unpack("L", hex2bin($hex)));
    }

    private function toFloat( $hex ){
        return (float) current(unpack("f", hex2bin($hex)));
    }


    private function substr(&$hex, $start, $end){

        $result = substr($hex, $start * 2, $end * 2);
        $hex = substr($hex, $end * 2);
        return $result;
    }

    private function extractAnimations($entry, $outputTo, $game){


        $headerType = $this->toString($this->substr($entry, 0, 4));

        if ($game == "mh2-wii"){
            $animationCount = $this->toInt(Helper::toBigEndian($this->substr($entry, 0, 4)));
        }else{
            $animationCount = $this->toInt($this->substr($entry, 0, 4));
        }

        if ($headerType !== "ANPK")
            throw new \Exception(
                sprintf('Expected ANPK got: %s', $headerType)
            );

        @mkdir($outputTo, 0777, true);

        $ifp = new Ifp();
        $ifp->extractAnimation(
            $animationCount,
            $entry,
            null,
            $outputTo,
            $game

        );
    }


    public function unpack($entry, $outputTo){


        $lookupEntry = $entry;

        //skip the version
        $version = $this->substr($lookupEntry, 0, 4);

        $game = "mh2-pc";

        //wii version
        if ($version == "00000001"){
            $game = "mh2-wii";

            $numExec = $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4)));
            $numEnvExec = $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4)));


        }else{
            $numExec = $this->toInt($this->substr($lookupEntry, 0, 4));
            $numEnvExec = $this->toInt($this->substr($lookupEntry, 0, 4));


        }

        $index = 0;

        while ($numExec > 0){


            if ($game == "mh2-wii"){
                $execution = [
                    'executionId' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),

                    'jumpExecutionOffset' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                    'jumpExecutionSize' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),

                    'whiteLevelExecOffset' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                    'whiteLevelExecSize' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),

                    'yellowLevelExecOffset' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                    'yellowLevelExecSize' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),

                    'redLevelExecOffset' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                    'redLevelExecSize' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                ];

            }else{
                $execution = [
                    'executionId' => $this->toInt($this->substr($lookupEntry, 0, 4)),

                    'jumpExecutionOffset' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                    'jumpExecutionSize' => $this->toInt($this->substr($lookupEntry, 0, 4)),

                    'whiteLevelExecOffset' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                    'whiteLevelExecSize' => $this->toInt($this->substr($lookupEntry, 0, 4)),

                    'yellowLevelExecOffset' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                    'yellowLevelExecSize' => $this->toInt($this->substr($lookupEntry, 0, 4)),

                    'redLevelExecOffset' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                    'redLevelExecSize' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                ];
            }

            foreach ([
                'jumpExecution',
                'whiteLevelExec',
                'yellowLevelExec',
                'redLevelExec'
             ] as $section) {
                $anpk = substr($entry,
                    $execution[$section . 'Offset'] * 2,
                    $execution[$section . 'Size'] * 2
                );


                $startPos = ($execution[$section . 'Offset'] * 2) + ($execution[$section . 'Size'] * 2);

                if ($startPos > 0){

                    $this->extractAnimations($anpk, $outputTo . 'executions/' . $index . '#ExecutionId_' . $execution['executionId'] . '/' . $section . '/', $game);
                }else{
                    @mkdir(
                        $outputTo . 'executions/' . $index . '#ExecutionId_' . $execution['executionId'] . '/' . $section . '/',
                        0777,
                        true
                    );

                }
            }

            $index++;
            $numExec--;
        }

        $index = 0;
        while ($numEnvExec > 0){

            if ($game == "mh2-wii") {
                $envExecution = [
                    'executionId' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),

                    'envExecutionOffset' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                    'envExecutionSize' => $this->toInt(Helper::toBigEndian($this->substr($lookupEntry, 0, 4))),
                ];
            }else{
                $envExecution = [
                    'executionId' => $this->toInt($this->substr($lookupEntry, 0, 4)),

                    'envExecutionOffset' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                    'envExecutionSize' => $this->toInt($this->substr($lookupEntry, 0, 4)),
                ];

            }

            $anpk = substr($entry,
                $envExecution['envExecutionOffset'] * 2,
                $envExecution['envExecutionSize'] * 2
            );

            $startPos = ($envExecution['envExecutionOffset'] * 2) + ($envExecution['envExecutionSize'] * 2);

            $this->extractAnimations($anpk, $outputTo . 'envExecutions/' . $index . '#ExecutionId_' . $envExecution['executionId'] . '/', $game);


            $index++;
            $numEnvExec--;
        }
    }

    public function pack( $executions, $envExecutions ){

        /**
         * Prepare
         */
        $offsetStart = 2048;

        $ifp = new Ifp();

        $prepared = [
            'executions' => [],
            'envExecutions' => []
        ];

        foreach ($executions as $executionId => $execution) {
            $id = explode('_', $executionId)[1];

            $prepared['executions'][$id] = [];

            foreach ([
                 'jumpExecution',
                 'whiteLevelExec',
                 'yellowLevelExec',
                 'redLevelExec'
             ] as $index => $section) {

                $prepared['executions'][$id][$section] = $ifp->packAnimation($execution[$section], 'mh2');
                $prepared['executions'][$id][$section . 'Offset'] = $offsetStart;
                $size = strlen($prepared['executions'][$id][$section]) / 2;
                $prepared['executions'][$id][$section . 'Size'] = $size;

                $missed = 2048 - $size % 2048;
                $prepared['executions'][$id][$section . 'Missed'] = $missed;

                $offsetStart += $missed;
                $offsetStart += $size;
           }

        }

        foreach ($envExecutions as $executionId => $execution) {
            $id = explode('_', $executionId)[1];


            $prepared['envExecutions'][$id] = [];


            $prepared['envExecutions'][$id]['animation'] = $ifp->packAnimation($execution, 'mh2');
            $prepared['envExecutions'][$id]['Offset'] = $offsetStart;
            $size = strlen($prepared['envExecutions'][$id]['animation']) / 2;
            $prepared['envExecutions'][$id]['Size'] = $size;

            $missed = 2048 - $size % 2048;
            $prepared['envExecutions'][$id]['Missed'] = $missed;

            $offsetStart += $missed;
            $offsetStart += $size;
        }

        /**
         * generate code
         */

        $data = "01000000";


        $data .= Helper::fromIntToHex(count($executions));
        $data .= Helper::fromIntToHex(count($envExecutions));

        /**
         * generate offset table
         */
        foreach ($prepared as $executionType) {

            foreach ($executionType as $exectionId => $execution) {

                $data .= Helper::fromIntToHex($exectionId);


                if (isset($execution['jumpExecutionOffset'])){
                    foreach ([
                                 'jumpExecution',
                                 'whiteLevelExec',
                                 'yellowLevelExec',
                                 'redLevelExec'
                             ] as $section) {
                        $data .= Helper::fromIntToHex($execution[$section . 'Offset'], false);
                        $data .= Helper::fromIntToHex($execution[$section . 'Size'], false);

                    }
                }else{

                    $data .= Helper::fromIntToHex($execution['Offset'], false);
                    $data .= Helper::fromIntToHex($execution['Size'], false);
                }
            }


        }

        $data .= str_repeat('00', 2048 - (strlen($data) / 2));

        /**
         * generate animations
         */

        foreach ($prepared as $executionType) {

            foreach ($executionType as $exectionId => $execution) {

                if (isset($execution['jumpExecutionOffset'])){
                    foreach ([
                                 'jumpExecution',
                                 'whiteLevelExec',
                                 'yellowLevelExec',
                                 'redLevelExec'
                             ] as $section) {
                        $data .= $execution[$section];

                        $data .= str_repeat('00', $execution[$section . 'Missed']);

                    }
                }else{
                    $data .= $execution['animation'];
                    $data .= str_repeat('00', $execution['Missed']);

                }
            }
        }


        return $data;
    }
}