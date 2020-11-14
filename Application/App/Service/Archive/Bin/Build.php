<?php
namespace App\Service\Archive\Bin;


use App\Service\Archive\Ifp;
use App\Service\Helper;

class Build {
    public $keepOrder = false;


    /**
     * @param $executions
     * @param $envExecutions
     * @param $game
     * @param $platform
     * @return string
     */
    public function build( $executions, $envExecutions, $game, $platform ){

        /**
         * Prepare
         */
        $offsetStart = 2048;

        $ifp = new Ifp();
        $ifp->keepOrder = $this->keepOrder;

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

                if (!isset($execution[$section])) continue;

                $prepared['executions'][$id][$section] = $ifp->packAnimation($execution[$section], $game, $platform)->hex;
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


            $prepared['envExecutions'][$id]['animation'] = $ifp->packAnimation($execution, $game, $platform)->hex;
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


                if (!isset($execution['Offset'])){
                    foreach ([
                                 'jumpExecution',
                                 'whiteLevelExec',
                                 'yellowLevelExec',
                                 'redLevelExec'
                             ] as $section) {

                        if (!isset($execution[$section . 'Offset'])) {
                            $data .= Helper::fromIntToHex(0, false);
                            $data .= Helper::fromIntToHex(0, false);
                        }else{
                            $data .= Helper::fromIntToHex($execution[$section . 'Offset'], false);
                            $data .= Helper::fromIntToHex($execution[$section . 'Size'], false);

                        }

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

                if (!isset($execution['animation'])){
                    foreach ([
                                 'jumpExecution',
                                 'whiteLevelExec',
                                 'yellowLevelExec',
                                 'redLevelExec'
                             ] as $section) {

                        if (!isset($execution[$section])) continue;

                        $data .= $execution[$section];

                        $data .= str_repeat('00', $execution[$section . 'Missed']);
                    }
                }else{
                    $data .= $execution['animation'];
                    $data .= str_repeat('00', $execution['Missed']);

                }
            }
        }

        return hex2bin($data);
    }
}
