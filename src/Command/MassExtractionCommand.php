<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Mls;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MassExtractionCommand extends Command
{

    protected static $defaultName = 'mass:extraction';

    protected function configure()
    {
        $this->setDescription('Search and extract any supported file');
        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');
        $this->addArgument('type', InputArgument::OPTIONAL, 'file type');

        $this->addOption(
            'game',
            null,
            InputOption::VALUE_OPTIONAL,
            'mh1 or mh2?',
            MHT::GAME_AUTO
        );

        $this->addOption(
            'platform',
            null,
            InputOption::VALUE_OPTIONAL,
            'pc,ps2,psp,wii,xbox?',
            MHT::PLATFORM_AUTO
        );

        $this->addOption(
            'no-duplicates',
            null,
            InputOption::VALUE_NONE,
            'Keep only unique items'
        );

        $this->addOption('flat',
            null,
            InputOption::VALUE_NONE,
            'Flat the output'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folder = realpath($input->getArgument('folder'));
        $type = $input->getArgument('type');
        $flat = $input->getOption('flat');
        $game = $input->getOption('game');
        $platform = $input->getOption('platform');
        $noDuplicates = $input->getOption('no-duplicates');


        if ($noDuplicates && $flat == false){
            $output->writeln("Option 'no-duplicates' can only be used with the option '--flat'");
            exit;
        }

        $path = pathinfo($folder);

        //prepare output folder
        $outputTo = $path['dirname'] . '/export';
        @mkdir($outputTo, 0777, true);
        $outputFolder = realpath($outputTo);


        //load the resource
        $resources = new Resources();

        $finder = new Finder();
        if ( $type == null){
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
                ->name('/\.txd/i')
                ->name('/\.mls/i')
                ->files()
                ->in($folder);
        }else{
            $finder
                ->name('/' . $type . '/i')

                ->files()
                ->in($folder);
        }

        $md5ByFile = [];


        $output->writeln(sprintf("Mass Extraction for %s files", $finder->count()));


        foreach ($finder as $file) {

            try{
                $resource = $resources->load($file, $game, $platform);

            }catch(\Exception $e) {
//                $output->writeln('Not supported ' . $file->getRelativePathname());

                continue;
            }



            $handler = $resource->getHandler();
            $output->writeln('Handler ' . $handler->name . ' for file ' . $file->getRelativePathname());

            $originalExtension = $file->getExtension();

            if ($flat){
                //prepare output folder
                $outputTo = $outputFolder . '/' . str_replace('/', '_', $file->getRelativePath());

            }else{
                //prepare output folder
                $outputTo = $outputFolder . '/' . $file->getRelativePath();
                @mkdir($outputTo, 0777, true);
            }


            $results = $handler->unpack($resource->getInput(), $game, $platform);

            if ($handler instanceof Mls){
                $results = $handler->getValidatedResults( $results, $game, $platform );
            }

            if (is_array($results)){

                foreach ($results as $relativeFilename => $data) {


                    //we loop through dataset not a fileset
                    if ( is_numeric($relativeFilename) ){

                        if ($flat) {

                            file_put_contents(
                                $outputTo . '_' . $file->getFilename() . '.json',
                                \json_encode($results, JSON_PRETTY_PRINT)
                            );
                        }else{


                            file_put_contents(
                                $outputTo . '/' . $file->getFilename() . '.json',
                                \json_encode($results, JSON_PRETTY_PRINT)
                            );
                        }

                        if (json_last_error() !== 0){
                            var_dump($results);
                            $output->writeln('EMERGENCY JSON error received: ' . json_last_error_msg());
                            exit;
                        }


                        break;

                    }else{
                        $pathInfo = pathinfo($relativeFilename);

                        if ($flat) {
                            $outputDir = $outputTo . '_' . str_replace('.', '#', $file->getFilename()) . '_' . $pathInfo['dirname'];
                            @mkdir($outputTo, 0777, true);

                        }else{
                            $outputDir = $outputTo . '/' . str_replace('.', '#', $file->getFilename()) . '/' . $pathInfo['dirname'];
                            @mkdir($outputDir, 0777, true);
                        }

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



                        if ($flat) {
                            $md5 = md5($data);
                            if (!isset($md5ByFile[$md5])) $md5ByFile[$md5] = [];
                            var_dump($pathInfo['basename'] . $extension);
                            $md5ByFile[$md5][] = $outputDir ;
                            file_put_contents($outputDir . '_' . $pathInfo['basename'] . $extension, $data);
                        }else{
                            file_put_contents($outputDir . '/' . $pathInfo['basename'] . $extension, $data);

                        }
                    }

                }


            }else{

                if ($flat) {
                    file_put_contents(
                        $outputTo . '_' . $file->getFilename(),
                        $results
                    );
                }else{
                    file_put_contents(
                        $outputTo . '/' . $file->getFilename(),
                        $results
                    );

                }

            }

        }

        if ($noDuplicates == true){
            $deleted = 0;
            $output->write("Delete duplicated files ");
            foreach ($md5ByFile as $entries) {
                if (count($entries) > 1){
                    array_pop($entries);
                    foreach ($entries as $entry) {
                        $output->write(".");
                        unlink($entry);
                        $deleted++;
                    }
                }
            }

            $output->write("\n");
            $output->writeln(sprintf("%s files deleted", $deleted));
            $output->writeln(sprintf("%s files keep", count($md5ByFile)));


        }


        $output->write("\nDone.\n");
    }
}