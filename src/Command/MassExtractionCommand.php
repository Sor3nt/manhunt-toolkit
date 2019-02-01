<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MassExtractionCommand extends Command
{

    protected static $defaultName = 'mass:extraction';

    protected function configure()
    {
        $this->setDescription('Search and extract any supported file');
        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folder = realpath($input->getArgument('folder'));

        $path = pathinfo($folder);

        //prepare output folder
        $outputTo = $path['dirname'] . '/export';
        @mkdir($outputTo, 0777, true);
        $outputFolder = realpath($outputTo);


        //load the resource
        $resources = new Resources();

        $finder = new Finder();
        $finder
            ->name('/\.bin/i')
            ->name('/\.col/i')
            ->name('/\.dff/i')
            ->name('/\.glg/i')
            ->name('/\.gxt/i')
            ->name('/\.ifp/i')
            ->name('/\.inst/i')
            ->name('/\.pak/i')
            ->name('/\.tex/i')
            ->name('/\.mls/i')
            ->files()
            ->in($folder);

        foreach ($finder as $file) {

            try{
                $resource = $resources->load($file, MHT::GAME_AUTO, MHT::PLATFORM_AUTO);

            }catch(\Exception $e) {
//                $output->writeln('Not supported ' . $file->getRelativePathname());

                continue;
            }



            $handler = $resource->getHandler();
            $output->writeln('Handler ' . $handler->name . ' for file ' . $file->getRelativePathname());

            $originalExtension = $file->getExtension();

            //prepare output folder
            $outputTo = $outputFolder . '/' . $file->getRelativePath();
            @mkdir($outputTo, 0777, true);

            $results = $handler->unpack($resource->getInput(), MHT::GAME_AUTO, MHT::PLATFORM_AUTO);

            if ($handler instanceof Mls){
                $results = $handler->getValidatedResults( $results, MHT::GAME_AUTO, MHT::PLATFORM_AUTO );
            }

            if (is_array($results)){

                foreach ($results as $relativeFilename => $data) {

                    //we loop through dataset not a fileset
                    if ( is_numeric($relativeFilename) ){

                        file_put_contents(
                            $outputTo . '/' . $file->getFilename() . '.json',
                            \json_encode($results, JSON_PRETTY_PRINT)
                        );

                        if (json_last_error() !== 0){
                            var_dump($results);
                            $output->writeln('EMERGENCY JSON error received: ' . json_last_error_msg());
                            exit;
                        }


                        break;

                    }else{
                        $pathInfo = pathinfo($relativeFilename);

                        $outputDir = $outputTo . '/' . str_replace('.', '#', $file->getFilename()) . '/' . $pathInfo['dirname'];
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

                file_put_contents(
                    $outputTo . '/' . $file->getFilename(),
                    $results
                );

            }

        }


        $output->write("\nDone.\n");
    }
}