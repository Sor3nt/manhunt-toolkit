<?php
namespace App\Service\Archive\Bin;

use App\MHT;
use App\Service\Archive\Ifp;
use App\Service\NBinary;

class Extract {



    public function get( NBinary $binary, $game, $platform ){

        $version = $binary->consume(4, NBinary::HEX);


        if ($game == MHT::GAME_AUTO) $game = MHT::GAME_MANHUNT_2;


        if ($platform == MHT::PLATFORM_AUTO){
            //wii version
            if ($version == "00000001"){
                $platform = MHT::PLATFORM_WII;
                $binary->numericBigEndian = true;
            }else{
                $platform = MHT::PLATFORM_PC;
            }

        }


        $numExec = $binary->consume(4, NBinary::INT_32);
        $numEnvExec = $binary->consume(4, NBinary::INT_32);

        $index = 0;

        $results = [];
        
        while ($numExec > 0){

            $execution = [
                'executionId'           => $binary->consume(4, NBinary::INT_32),
                'jumpExecutionOffset'   => $binary->consume(4, NBinary::INT_32),
                'jumpExecutionSize'     => $binary->consume(4, NBinary::INT_32),
                'whiteLevelExecOffset'  => $binary->consume(4, NBinary::INT_32),
                'whiteLevelExecSize'    => $binary->consume(4, NBinary::INT_32),
                'yellowLevelExecOffset' => $binary->consume(4, NBinary::INT_32),
                'yellowLevelExecSize'   => $binary->consume(4, NBinary::INT_32),
                'redLevelExecOffset'    => $binary->consume(4, NBinary::INT_32),
                'redLevelExecSize'      => $binary->consume(4, NBinary::INT_32)
            ];

            foreach ([
                'jumpExecution',
                'whiteLevelExec',
                'yellowLevelExec',
                'redLevelExec'
            ] as $section) {

                //not every BIN file has any execution state
                if ($execution[$section . 'Offset'] == 0) continue;

                $anpk = $binary->range(
                    $execution[$section . 'Offset'] ,
                    $execution[$section . 'Offset'] + $execution[$section . 'Size'],
                    true
                );

                $anpk = new NBinary($anpk);
                $anpk->numericBigEndian = $binary->numericBigEndian;

                $targetFileName = "executions/ExecutionId_" . $execution['executionId'] . '/' . $section;
//                $targetFileName = "executions/" . $index . "#ExecutionId_" . $execution['executionId'] . '/' . $section;

                $animations = $this->extractAnimations(
                    $anpk,
                    $game,
                    $platform
                );
                foreach ($animations as $animationFileName => $animation) {
                    $results[ $targetFileName . '/' . $animationFileName] = $animation;
                }
            }

            $index++;
            $numExec--;
        }

        $index = 0;
        while ($numEnvExec > 0){

            $envExecution = [
                'executionId' => $binary->consume(4, NBinary::INT_32),

                'envExecutionOffset' => $binary->consume(4, NBinary::INT_32),
                'envExecutionSize' => $binary->consume(4, NBinary::INT_32),
            ];

            $anpk = $binary->range(
                $envExecution['envExecutionOffset'],
                $envExecution['envExecutionOffset'] + $envExecution['envExecutionSize'],
                true
            );

            $anpk = new NBinary($anpk);
            $anpk->numericBigEndian = $binary->numericBigEndian;

            $targetFileName = "envExecutions/ExecutionId_" . $envExecution['executionId'];
//            $targetFileName = "envExecutions/" . $index . "#ExecutionId_" . $envExecution['executionId'];

            $animations = $this->extractAnimations(
                $anpk,
                $game,
                $platform
            );

            foreach ($animations as $animationFileName => $animation) {
                $results[ $targetFileName . '/' . $animationFileName] = $animation;
            }

            $index++;
            $numEnvExec--;
        }
        
        return $results;
    }

    /**
     * @param NBinary $binary
     * @param $game
     * @param $platform
     * @return array
     * @throws \Exception
     */
    private function extractAnimations(NBinary $binary, $game, $platform){

        $headerType = $binary->consume(4, NBinary::STRING);

        $animationCount = $binary->consume(4, NBinary::INT_32);

        if ($headerType !== "ANPK")
            throw new \Exception(
                sprintf('Expected ANPK got: %s', $headerType)
            );

        return (new Ifp())->extractAnimation(
            $animationCount,
            $binary,
            $game,
            $platform
        );

    }
}