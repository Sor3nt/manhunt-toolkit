<?php

namespace App\Command;

use App\Service\Archive\Bin;
use App\Service\Archive\Bmp;
use App\Service\Archive\Dds;
use App\Service\Archive\Dxt1;
use App\Service\Archive\Dxt5;
use App\Service\Archive\Ifp;
use App\Service\Archive\Mls;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnpackCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('archive:unpack')
            ->setAliases(['unpack', 'extract', 'uncompress'])
            ->setDescription('Unpack a Manhunt file.')
            ->addArgument('file', InputArgument::REQUIRED, 'This file will be extracted')
            ->addOption('only-unzip', null, null, 'Will only unzip the file')
            ->addOption(
                'game',
                null,
                InputOption::VALUE_OPTIONAL,
                'mh1 or mh2?',
                null
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        //TODO !!!!!
        $game = "mh2";

        $file = $input->getArgument('file');
        $path = pathinfo($file);

        $originalExtension = $path['extension'];

        //prepare output folder
        $outputTo = $path['dirname'] . '/export/' . $path['filename'] . '#' . $originalExtension;
        @mkdir($outputTo, 0777, true);

        //load the resource
        $resources = new Resources();
        $resource = $resources->load($file);

        if ($input->getOption('only-unzip')){

            $outputTo = str_replace("#", '.', $outputTo);

            file_put_contents(
                $outputTo,
                $resource->getInput()->binary
            );

            $output->writeln(sprintf("Saved to %s.",  $outputTo));
            return;
        }

        $handler = $resource->getHandler();

        $output->writeln( sprintf('Identify file as %s ', $handler->name));
        $output->write( sprintf('Processing %s ', $file));

        $results = $handler->unpack( $resource->getInput(), $game );

        if ($handler instanceof Mls){
            $results = $handler->getValidatedResults( $results );
        }

        if (is_array($results)){

            foreach ($results as $relativeFilename => $data) {

                //we loop through dataset not a fileset
                if ( is_numeric($relativeFilename) ){

                    rmdir($outputTo);
                    $outputTo = str_replace("#", '.', $outputTo) . '.json';

                    file_put_contents(
                        $outputTo,
                        \json_encode($results, JSON_PRETTY_PRINT)
                    );

                    break;

                }else{
                    $output->write('.');

                    $pathInfo = pathinfo($relativeFilename);

                    $outputDir = $outputTo . '/' . $pathInfo['dirname'];
                    @mkdir($outputDir, 0777, true);

                    if (isset($pathInfo['extension'])) {
                        $extension = ''; // we keep the extension from the given filename
                    }else if (is_array($data)){
                        $extension = '.json';

                    }else{
                        $extension = '.' . $originalExtension;
                    }

                    if (is_array($data)){
                        $data = \json_encode($data, JSON_PRETTY_PRINT);
                    }

                    file_put_contents($outputDir . '/' . $pathInfo['basename'] . $extension, $data);
                }

            }


        }else{
            rmdir($outputTo);
            $outputTo = str_replace("#", '.', $outputTo);

            file_put_contents(
                $outputTo,
                $results
            );

        }


        $output->writeln(sprintf("\nExtracted to %s",  $outputTo));
    }

}