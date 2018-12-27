<?php

namespace App\Command;

use App\Service\Archive\Bin;
use App\Service\Archive\Bmp;
use App\Service\Archive\Dds;
use App\Service\Archive\Dxt1;
use App\Service\Archive\Dxt5;
use App\Service\Archive\Ifp;
use App\Service\Compiler\Compiler;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $file = $input->getArgument('file');

        $resources = new Resources();
        $resource = $resources->load($file, [
            'allowUserQuestion' => true,
            'outputInterface' => $output,
            'inputInterface' => $input
        ]);

        $content = $resource->getContent();

        if ($input->getOption('only-unzip')){

            file_put_contents(
                $file . '.unzipped',
                $resource->getBinary()
            );

            $output->writeln(sprintf("Saved to %s.", $file . '.unzipped'));
            return;
        }

        $path = pathinfo($file);
        $outputTo = $path['dirname'] . '/export/' . $path['filename'] . '_' . $resource->getType();

        switch ($resource->getType()){

            case 'tex':
                @mkdir($outputTo, 0777, true);

                $ddsHandler = new Dds();
                $bmpHandler = new Bmp();

                foreach ($content as $item) {

                    //decode the DDS
                    $ddsDecoded = $ddsHandler->decode($item['data']);

                    if($ddsDecoded['format'] == "DXT1") {
                        $dxtHandler = new Dxt1();
                    }else if($ddsDecoded['format'] == "DXT5"){
                        $dxtHandler = new Dxt5();
                    }else{
                        throw new \Exception('Format not implemented: ' . $ddsDecoded['format']);
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

                    file_put_contents($outputTo . '/' . $item['name'] . ".bmp" , $bmpImage);
                }

                break;
            case 'grf':
            case 'col':
                file_put_contents(
                    $outputTo . '.json',
                    \json_encode($content, JSON_PRETTY_PRINT)
                );
                break;
            case 'ifp':

                @mkdir($outputTo, 0777, true);

                $handler = new Ifp();
                $handler->unpack($content, $outputTo . '/');

                break;
            case 'bin':

                @mkdir($outputTo, 0777, true);

                $handler = new Bin();
                $handler->unpack($content, $outputTo . '/');

                break;
            case 'scs':
            case 'mls':

                $supportedOut = $outputTo . "/supported";
                $notSupportedOut = $outputTo . "/not-supported";

                @mkdir($supportedOut, 0777, true);
                @mkdir($notSupportedOut, 0777, true);

                $levelScript = false;

                foreach ($content as $index => $mhsc) {

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

                    }catch(\Exception $e){

                        file_put_contents(
                            $outputTo . '/error.log',
                            sprintf(
                                "%s occured in %s#%s\n",
                                $e->getMessage(),
                                $index,
                                $mhsc['NAME']
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

                break;
            default:

                file_put_contents(
                    $outputTo . '.plain',
                    $content
                );

            break;
        }

        $output->writeln('done');
    }

}