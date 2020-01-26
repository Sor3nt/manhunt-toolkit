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

class AnalyseAnimationsCommand extends Command
{

    protected static $defaultName = 'analyse:animations';

    protected function configure()
    {
        $this->setDescription('Short helper to compare execution animations across the platforms. Every call append/replace the given animations and save them to the stored-animations.json file');

        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder with the extracted json files');
        $this->addArgument('platform', InputArgument::REQUIRED, 'save result as psp,ps2,wii,pc or xbox');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folder = realpath($input->getArgument('folder'));
        $platform = $input->getArgument('platform');

        $finder = new Finder();
        $finder->name('/\.json/i')->files()->in($folder );


        $storeFileName = 'stored-animations.json';
        if (file_exists($storeFileName)){
            $stored = \json_decode(file_get_contents($storeFileName), true);
        }else{
            $stored = [];
        }

        $csv = [
            [
                'name',
                'target',
                'stage',
                'pc',
                'ps2Leak',
                'ps2',
                'psp',
                'wii',
            ]
        ];

        foreach ($finder as $file) {

            preg_match('/Id_(\d+)\/(.*)(Level|Exec).*\/BAT_(DAMAGE|DIE|USE)_(.*).json/i', $file->getRelativePathname(), $matches);

            if (count($matches) == 0){

                //env exec
                preg_match('/Id_(\d+)\/BAT_(DAMAGE|DIE|USE)_(.*).json/i', $file->getRelativePathname(), $matches);

                if (count($matches) == 0){
                    var_dump("uhh hmm", $file->getRelativePathname());
                    exit;
                }

                $stage = "env";
                list(, $executionId, $target, $name) = $matches;

            }else{
                //regular exec
                list(, $executionId, $stage, , $target, $name) = $matches;
            }

            $animation = \json_decode($file->getContents(), true);
            $frameCount = 0;
            foreach ($animation['bones'] as $bone) {
                $frameCount += count($bone['frames']['frames']);
            }

            unset($animation);


            if ($stage == "env"){

                $name = explode('EXECUTE_', $name)[1];
                list($envOrJump, $name) = explode('_', $name);

            }else{
                $name = explode('EXECUTE_', $name)[1];
                $name = explode('_', $name)[0];

            }

            $name .= ' '. $executionId;

            if (!isset($stored[$name])){
                $stored[$name] = [];
            }

            if (!isset($stored[$name][$stage])){
                $stored[$name][$stage] = [];
            }

            if (!isset($stored[$name][$stage][$target])){
                $stored[$name][$stage][$target] = [];
            }

            if (!isset($stored[$name][$stage][$target][$platform])){

                $stored[$name][$stage][$target][$platform] = [
                    'frameCount' => $frameCount,
                    'file' => $folder . '/' . $file->getRelativePathname()
                ];
            }

        }

        file_put_contents($storeFileName, \json_encode($stored));


        foreach ($stored as $executionName => $stages) {

            foreach ($stages as $stageName => $execution) {

                foreach ($execution as $targetName => $platforms) {

                    $line = [$executionName, $targetName, $stageName];

                    if (isset($platforms['pc'])) {
                        $line[] = $platforms['pc']['frameCount'];
                    }else{
                        $line[] = '';
                    }

                    if (isset($platforms['ps2Leak'])){
                        $line[] = $platforms['ps2Leak']['frameCount'];
                    }else{
                        $line[] = '';
                    }

                    if (isset($platforms['ps2'])){
                        $line[] = $platforms['ps2']['frameCount'];
                    }else{
                        $line[] = '';
                    }

                    if (isset($platforms['psp'])){
                        $line[] = $platforms['psp']['frameCount'];
                    }else{
                        $line[] = '';
                    }

                    if (isset($platforms['wii'])){
                        $line[] = $platforms['wii']['frameCount'];
                    }else{
                        $line[] = '';
                    }

                    $csv[] = $line;

                }
            }

        }


        $fp = fopen('animations-analyse.csv', 'w');

        foreach ($csv as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        $output->write("\nResult combined and saved into animations-analyse.csv\n");
    }
}