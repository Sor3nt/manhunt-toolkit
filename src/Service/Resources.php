<?php

namespace App\Service;

use App\Service\Archive\Bin;
use App\Service\Archive\Col;
use App\Service\Archive\Dff;
use App\Service\Archive\Grf;
use App\Service\Archive\Gxt;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use App\Service\Archive\Tex;
//use App\Service\Archive\Txd;
use App\Service\Archive\ZLib;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Resources
{

    public $workDirectory = '';


    public function load( $relativeFile, $options = [] ){

        $absoluteFile = $this->workDirectory . $relativeFile;
        if (!file_exists( $absoluteFile )) throw new \Exception(sprintf('File not found: %s', $absoluteFile));

        $fileExtension = strtolower(pathinfo($absoluteFile, PATHINFO_EXTENSION));

        $content = file_get_contents($absoluteFile);
        $contentAsHex = bin2hex($content);

        // we found a zLib compressed file
        if (substr($contentAsHex, 0, 8) === "5a32484d" || substr($contentAsHex, 0, 8) === "4d48325a"){
            $content = ZLib::uncompress( $content );
        }

        $result = $content;

        if (isset($options['force_file_extension'])) $fileExtension = $options['force_file_extension'];

        if (!isset($options['game'])) $options['game'] = "mh2";


        switch ($fileExtension){

            case 'glg':
            case 'dxt1':
            case 'dxt2':
            case 'dxt3':
            case 'dxt4':
            case 'dxt5':
                break;

            case 'scc':
            case 'mls':

                /**
                 * in some extraction case we cant not detect if we have a MH1 or MH2 file, so we need to ask the user
                 */
//                if ( isset($options['allowUserQuestion']) && $options['allowUserQuestion'] == true){
//                    $qhelper = new QuestionHelper();
//
//                    /** @var OutputInterface $output */
//                    $output = $options['outputInterface'];
//
//                    /** @var InputInterface $input */
//                    $input = $options['inputInterface'];
//
//                    do {
//                        $question = new Question('Manhunt (1) or Manhunt (2) ? : ', false);
//                        $game = (int) $qhelper->ask($input, $output, $question);
//                    }while ($game != 1 && $game != 2);
//
//                    $options['game'] = 'mh' . $game;
//                }

                $handler = new Mls();
                $result = $handler->unpack($content, 'mh2');
                break;
            case 'bin':

                if (mb_substr($content, 0, 4, '8bit')  == "\x01\x00\x00\x00"){

                    //real bin file
                }else{
                    return $this->load($relativeFile, [ 'force_file_extension' => 'inst'] );
                }

                break;

            case 'ifp':
                break;

            case 'dff':
                $handler = new Dff();
                $result = $handler->unpack($content);
                break;

            case 'grf':
                $handler = new Grf();
                $result = $handler->unpack($content);
                break;
            case 'gxt':
                $handler = new Gxt();
                $result = $handler->unpack($content);
                break;

            case 'col':
                $handler = new Col();
                $result = $handler->unpack($content);
                break;

            case 'tex':
                $handler = new Tex();
                $result = $handler->unpack($content);
                break;

            case 'inst':

                $handler = new Inst();
                $result = $handler->unpack($content);
                break;
            default:
                throw new \Exception(sprintf('Unable to load resource %s, unknown handler', $relativeFile));
        }

        return new Resource(
            $result,
            $fileExtension,
            $relativeFile,
            $content

        );
    }

    public function saveMls( Resource $resource ){

    }

}