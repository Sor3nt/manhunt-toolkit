<?php

namespace App\Command;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dds;
use App\Service\Archive\Dff;
use App\Service\Archive\Dxt1;
use App\Service\Archive\Dxt5;
use App\Service\Archive\Tex;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MassExtractDffCommand extends Command
{

    protected static $defaultName = 'mass-extract:dff';

    protected function configure()
    {

        $this->addArgument('folder', InputArgument::REQUIRED, 'Folder to search');
        $this->addArgument('outputTo', InputArgument::REQUIRED, 'Output folder');
        $this->addOption(
            'save-differences',
            'sd',
            InputOption::VALUE_NONE,
            'Compare File and save differences ?'
        );
        $this->addOption(
            'copy-all-found',
            'cf',
            InputOption::VALUE_NONE,
            'Copy all found files together ?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $differences = [];
        $availableFiles = [];

        $folder = realpath($input->getArgument('folder'));
        $outputTo = $input->getArgument('outputTo');
        $saveDifferences = $input->getOption('save-differences');
        $copyAllFound = $input->getOption('copy-all-found');

        @mkdir($outputTo, 0777, true);
        $outputTo = realpath($outputTo);

        $finder = new Finder();
        $finder->exclude(substr(str_replace($folder,'', $outputTo), 1))->name('*.dff')->name('*.DFF')->files()->in($folder );

        foreach ($finder as $file) {

            $output->write('Process ' . $file->getRelativePathname() . ' ');

            $path = $outputTo . '/' . $file->getRelativePathname();
            @mkdir($path, 0777, true);
            $content = $file->getContents();


            $dff = new Dff();
            $dffEntries = $dff->unpack( $content );


            foreach ($dffEntries as $dffEntry) {
                $output->write('.');

                $fileName = $dffEntry['name'] . ".dff";

                file_put_contents($path . '/' . $fileName, $dffEntry['data']);

                if (!isset($differences[ $fileName ])) $differences[ $fileName ] = [];

                $differences[  $fileName  ][ md5($dffEntry['data']) ] = $path . '/' . $fileName;


                $availableFiles[] = $path . '/' . $fileName;

            }
            $output->write("\n");

        }


        if ($saveDifferences){

            $collectionOutput = $outputTo . '/__differences';
            @mkdir($collectionOutput, 0777, true);

            $output->write("\n");
            $output->write('Save file differences ');

            foreach ($differences as $fileName => $entries) {

                $index = 1;
                foreach ($entries as $md5 => $path) {
                    $output->write('.');
    
                    list($name, $ext) = explode('.', $fileName);


                    copy($path, $collectionOutput . '/' . $name . '_' . $index . '.'. $ext);
                    $index++;
                }
            }
        }

        if ($copyAllFound){
            $collectionOutput = $outputTo . '/__any_dff/';
            @mkdir($collectionOutput, 0777, true);

            $output->write("\n");
            $output->write('Copy files together ');

            foreach ($availableFiles as $availableFile) {
                if (file_exists($collectionOutput . '/' . pathinfo($availableFile)['filename'] . '.dff')) continue;
                copy($availableFile, $collectionOutput . '/' . pathinfo($availableFile)['filename'] . '.dff');

            }
        }

        $output->write("\nDone.\n");
    }
}