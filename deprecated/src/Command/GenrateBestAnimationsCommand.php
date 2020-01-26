<?php

namespace App\Command;

use App\MHT;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenrateBestAnimationsCommand extends Command
{

    protected static $defaultName = 'generate:ifp';

    protected function configure()
    {
        $this->setDescription('Generate a strmanim.ifo file based on the best results from  stored-animations.json');

        $this->addArgument('json', InputArgument::REQUIRED, 'Pathname to stored-animations.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $stored = \json_decode(file_get_contents(realpath($input->getArgument('json'))), true);


        $bestChoices = [
            'envExecutions' => [],
            'executions' => []
        ];

        foreach ($stored as $executionName => $stages) {

            foreach ($stages as $stageName => $execution) {

                if ($stageName == "jump"){
                    $stageName .= "Execution";
                }else{
                    $stageName .= "Exec";
                }

                foreach ($execution as $targetName => $platforms) {

                    $bestAnimationFrameRate = 0;
                    $bestAnimationFile = false;

                    //find the best entry (most key-frames)
                    foreach ($platforms as $platform) {
                        if ($platform['frameCount'] > $bestAnimationFrameRate){
                            $bestAnimationFrameRate = $platform['frameCount'];
                            $bestAnimationFile = $platform['file'];
                        }
                    }

                    list($execName, $execId) = explode(' ', $executionName);

                    if (strpos($bestAnimationFile, 'envExecutions') !== false){
                        if (!isset($bestChoices['envExecutions']['ExecutionId_' . $execId])){
                            $bestChoices['envExecutions']['ExecutionId_' . $execId] = [];
                        }

                        $bestChoices['envExecutions']['ExecutionId_' . $execId][] = $bestAnimationFile;

                        $newPathname = 'envExecutions/' . explode("/envExecutions/",$bestAnimationFile)[1];


                    }else{
                        if (!isset($bestChoices['executions']['ExecutionId_' . $execId])){
                            $bestChoices['executions']['ExecutionId_' . $execId] = [];
                        }

                        if (!isset($bestChoices['executions']['ExecutionId_' . $execId][$stageName])){
                            $bestChoices['executions']['ExecutionId_' . $execId][$stageName] = [];
                        }

                        $bestChoices['executions']['ExecutionId_' . $execId][$stageName][] = $bestAnimationFile;

                        $newPathname = 'executions/' . explode("/executions/",$bestAnimationFile)[1];
                    }


                    $pathInfo = pathinfo($newPathname);
                    $outputTo = 'export/strmanim#ifp/' . $pathInfo['dirname'];
                    @mkdir($outputTo, 0777, true);

                    copy($bestAnimationFile, $outputTo . '/' . $pathInfo['basename']);

                }
            }
        }



        $output->write("\nDone.\n");
    }
}