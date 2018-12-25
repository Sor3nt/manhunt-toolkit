<?php
namespace App\Service\Archive\Bin;

use App\Service\Archive\Ifp;
use App\Service\NBinary;

class Extract {

    private $binary;

    public function __construct( $binaryData ) {
        $this->binary = new NBinary($binaryData );
    }

    /**
     * @param $outputTo
     */
    public function save($outputTo){

        $version = $this->binary->consume(4, NBinary::HEX);

        $game = "mh2-pc";

        //wii version
        if ($version == "00000001"){
            $game = "mh2-wii";
            $this->binary->numericBigEndian = true;
        }

        $numExec = $this->binary->consume(4, NBinary::INT_32);
        $numEnvExec = $this->binary->consume(4, NBinary::INT_32);

        $index = 0;

        while ($numExec > 0){

            $execution = [
                'executionId'           => $this->binary->consume(4, NBinary::INT_32),
                'jumpExecutionOffset'   => $this->binary->consume(4, NBinary::INT_32),
                'jumpExecutionSize'     => $this->binary->consume(4, NBinary::INT_32),
                'whiteLevelExecOffset'  => $this->binary->consume(4, NBinary::INT_32),
                'whiteLevelExecSize'    => $this->binary->consume(4, NBinary::INT_32),
                'yellowLevelExecOffset' => $this->binary->consume(4, NBinary::INT_32),
                'yellowLevelExecSize'   => $this->binary->consume(4, NBinary::INT_32),
                'redLevelExecOffset'    => $this->binary->consume(4, NBinary::INT_32),
                'redLevelExecSize'      => $this->binary->consume(4, NBinary::INT_32)
            ];

            foreach ([
                'jumpExecution',
                'whiteLevelExec',
                'yellowLevelExec',
                'redLevelExec'
            ] as $section) {

                $anpk = $this->binary->range(
                    $execution[$section . 'Offset'] ,
                    $execution[$section . 'Offset'] + $execution[$section . 'Size'],
                    true
                );

                $anpk = new NBinary($anpk);
                $anpk->numericBigEndian = $this->binary->numericBigEndian;

                $this->extractAnimations(
                    $anpk,
                    $outputTo . 'executions/' . $index . '#ExecutionId_' . $execution['executionId'] . '/' . $section . '/',
                    $game
                );
            }

            $index++;
            $numExec--;
        }

        $index = 0;
        while ($numEnvExec > 0){

            $envExecution = [
                'executionId' => $this->binary->consume(4, NBinary::INT_32),

                'envExecutionOffset' => $this->binary->consume(4, NBinary::INT_32),
                'envExecutionSize' => $this->binary->consume(4, NBinary::INT_32),
            ];

            $anpk = $this->binary->range(
                $envExecution['envExecutionOffset'],
                $envExecution['envExecutionOffset'] + $envExecution['envExecutionSize'],
                true
            );

            $anpk = new NBinary($anpk);
            $anpk->numericBigEndian = $this->binary->numericBigEndian;

            $this->extractAnimations(
                $anpk,
                $outputTo . 'envExecutions/' . $index . '#ExecutionId_' . $envExecution['executionId'] . '/',
                $game
            );

            $index++;
            $numEnvExec--;
        }
    }

    /**
     * @param NBinary $binary
     * @param $outputTo
     * @param $game
     * @throws \Exception
     */
    private function extractAnimations(NBinary $binary, $outputTo, $game){

        $headerType = $binary->consume(4, NBinary::STRING);

        $animationCount = $binary->consume(4, NBinary::INT_32);

        if ($headerType !== "ANPK")
            throw new \Exception(
                sprintf('Expected ANPK got: %s', $headerType)
            );

        @mkdir($outputTo, 0777, true);

        $ifp = new Ifp();
        $ifp->extractAnimation(
            $animationCount,
            bin2hex($binary->binary),
            null,
            $outputTo,
            $game

        );
    }
}