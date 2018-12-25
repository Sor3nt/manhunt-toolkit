<?php

namespace App\Command;

use App\Service\Archive\Bmp;
use App\Service\Archive\Dds;
use App\Service\Archive\Dxt1;
use App\Service\Archive\Dxt5;
use App\Service\Archive\Tex;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MassExtractTexCommand extends Command
{

    protected static $defaultName = 'mass-extract:tex';

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

        $tex = new Tex();

        $differences = [];
        $availableFiles = [];

        $folder = realpath($input->getArgument('folder'));
        $outputTo = realpath($input->getArgument('outputTo'));
        $saveDifferences = $input->getOption('save-differences');
        $copyAllFound = $input->getOption('copy-all-found');

        @mkdir($outputTo, 0777, true);

        $finder = new Finder();
        $finder->name('*.tex')->name('*.TEX')->files()->in($folder );

        foreach ($finder as $file) {

            $output->write('Process ' . $file->getRelativePathname() . ' ');

            $path = $outputTo . '/' . $file->getRelativePathname();
            @mkdir($path, 0777, true);

            $content = $file->getContents();
            $contentMd5 = md5($content);

            $existingFiles = scandir($path);

            $skip = false;
            foreach ($existingFiles as $existingFile) {
                if (strpos($existingFile, $contentMd5) !== false){

                    list($textureName, $contentMd5, $textureMd5) = explode("___", $existingFile);

                    if (!isset($differences[ $textureName ])) $differences[ $textureName ] = [];

                    $differences[ $textureName ][ $textureMd5 ] = $path . '/' . $existingFile;
                    $availableFiles[] = $path . '/' . $existingFile;
                    $output->write("s");
                    $skip = true;

                    continue;
                }
            }

            if ($skip){
                $output->write("\n");
                continue;
            }

            $textures = $tex->unpack( $content );


            $ddsHandler = new Dds();
            $bmpHandler = new Bmp();

            foreach ($textures as $texture) {
                $output->write('.');

                $textureMd5 = md5($texture['data']);

                //decode the DDS
                $ddsDecoded = $ddsHandler->decode($texture['data']);

                if($ddsDecoded['format'] == "DXT1") {
                    $dxtHandler = new Dxt1();
                }else if($ddsDecoded['format'] == "DXT5"){
                    $dxtHandler = new Dxt5();

                }else{

                    if ($ddsDecoded['format'] == ""){

                        if ($ddsDecoded['height'] == 32 && $ddsDecoded['width'] == 32 && $ddsDecoded['depth'] == 0){
                            continue;

                        }else{
                            var_dump($ddsDecoded);
                            exit;

                        }
                    }else{
                        throw new \Exception('Format not implemented: ' . $ddsDecoded['format']);
                    }
                }


                //decode the DXT Texture
                $bmpRgba = $dxtHandler->decode(
                    $ddsDecoded['data'],
                    $ddsDecoded['width'],
                    $ddsDecoded['height'],
                    'abgr'
                );


                //Convert the RGBa values into a Bitmap
                $bmpImage = $bmpHandler->encode(
                    $bmpRgba,
                    $ddsDecoded['width'],
                    $ddsDecoded['height']
                );

                $fileName = $texture['name'] . "___" . $contentMd5 . "___" . $textureMd5 . ".bmp";

                file_put_contents($path . '/' . $fileName, $bmpImage);

                if (!isset($differences[ $texture['name'] ])) $differences[ $texture['name'] ] = [];

                $differences[ $texture['name'] ][ $textureMd5 ] = $path . '/' . $fileName;

                $availableFiles[] = $path . '/' . $fileName;

            }
            $output->write("\n");

        }

        if ($saveDifferences){
            $collectionOutput = $outputTo . '/__differences';
            @mkdir($collectionOutput, 0777, true);

            $output->write("\n");
            $output->write('Save file differences ');

            foreach ($differences as $entries) {
                if (count($entries) == 1) continue;

                $index = 1;
                foreach ($entries as $texture) {
                    $output->write('.');

                    list($textureName, $contentMd5, $textureMd5) = explode("___", $texture);
                    $textureName = array_reverse(explode("/", $textureName))[0];

                    copy($texture, $collectionOutput . '/' . $textureName . "_" . $index . ".bmp");
                    $index++;
                }
            }
        }


        if ($copyAllFound){
            $collectionOutput = $outputTo . '/__any_textures/';
            @mkdir($collectionOutput, 0777, true);

            $output->write("\n");
            $output->write('Copy files together ');

            foreach ($availableFiles as $availableFile) {
                if (file_exists($collectionOutput . '/' . pathinfo($availableFile)['filename'] . '.bmp')) continue;
                copy($availableFile, $collectionOutput . '/' . pathinfo($availableFile)['filename'] . '.bmp');

            }
        }

        $output->write("\nDone.\n");
    }
}