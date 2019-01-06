<?php

namespace App\Command;

use App\Service\Archive\Dff;
use App\Service\Archive\Mls;
use App\Service\Archive\ZLib;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MassExtractMlsCommand extends Command
{

    protected static $defaultName = 'mass-extract:mls';

    protected function configure()
    {
        $this->setDescription('Search and extract any *.mls. (MH1 PC/PS2/XBOX & MH2 PC/PS2/PSP/WII)');

        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resources = new Resources();

        $folder = realpath($input->getArgument('folder'));

        $finder = new Finder();
        $finder->name('*.mls')->name('*.MLS')->files()->in($folder );

        $outputTo = getCwd() . '/export';
        $allOkCount = 0;
        $allFailCount = 0;

        foreach ($finder as $file) {

            $output->write('Process ' . $file->getRelativePathname() . ' ');

            $path = $outputTo . '/' . $file->getRelativePathname();
            @mkdir($path, 0777, true);

            $resource = $resources->load($file->getRelativePathname(), [ 'game' => 'mh2' ]);

            $handler = new Mls();
            $scripts = $handler->unpack($resource->getInput(), 'mh2');

            $supportedOut = $path . "/supported";
            $notSupportedOut = $path . "/not-supported";

            @mkdir($supportedOut, 0777, true);
            @mkdir($notSupportedOut, 0777, true);

            $levelScript = false;

            $okCount = 0;
            $failCount = 0;
            foreach ($scripts as $index => $mhsc) {
                $compiler = new Compiler();

                try{

                    $compiled = $compiler->parse($mhsc['SRCE'], $levelScript);

                    if ($index == 0){
                        $levelScript = $compiled;
                    }

                    if ($compiled['CODE'] != $mhsc['CODE']) throw new \Exception('CODE did not match');

                    file_put_contents(
                        $supportedOut . '/' . $index . "#" . $mhsc['NAME'] . '.srce' ,
                        $mhsc['SRCE']
                    );

                    $output->write(".");
                    $okCount++;

                }catch(\Exception $e){
                    $output->write("f");
                    $failCount++;

                    file_put_contents(
                        $outputTo . '/error.log',
                        sprintf(
                            "%s occured in %s#%s\n",
                            $e->getMessage(),
                            $index,
                            $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.srce'
                        ),
                        FILE_APPEND
                    );

                    file_put_contents(
                        $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.code',
                        implode("\n", $mhsc['CODE'])
                    );

                    file_put_contents(
                        $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.srce' ,
                        $mhsc['SRCE']
                    );

                    file_put_contents(
                        $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.scpt',
                        \json_encode( $mhsc['SCPT'])
                    );

                    file_put_contents(
                        $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.smem' ,
                        $mhsc['SMEM']
                    );

                    file_put_contents(
                        $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.entt',
                        \json_encode( $mhsc['ENTT'])
                    );

                    if (isset($mhsc['DATA'])){
                        file_put_contents(
                            $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.data' ,
                            implode("\n", $mhsc['DATA'])
                        );
                    }

                    if (isset($mhsc['STAB'])) {
                        file_put_contents(
                            $notSupportedOut . "/" . $index . "#" . $mhsc['NAME'] . '.stab',
                            \json_encode( $mhsc['STAB'])
                        );
                    }
                }
            }

            $output->write(" (".$okCount."/" . ($failCount + $okCount) . ")\n");

            $allOkCount += $okCount;
            $allFailCount += $failCount;

        }


        $output->write(" Overall: (".$allOkCount."/" . ($allFailCount + $allOkCount) . ")\n");
        $output->write("\nDone.\n");
    }
}