<?php

namespace App\Command;

use App\Service\Archive\Bin;
use App\Service\Archive\Fsb;
use App\Service\Archive\Glg;
use App\Service\Archive\Grf;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use App\Service\Archive\Tex;
use App\Service\BytecodeExplain;
use App\Service\Compiler\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UnpackCommand extends Command
{

    /** @var Mls*/
    private $mls;

    /** @var Glg  */
    private $glg;

    /** @var Inst  */
    private $inst;

    /** @var Fsb  */
    private $fsb;

    /** @var Grf  */
    private $grf;

    /** @var Ifp  */
    private $ifp;

    /** @var Bin  */
    private $bin;

    /** @var Tex  */
    private $tex;


    public function __construct(Mls $mls, Glg $glg, Inst $inst, Grf $grf, Ifp $ifp, Bin $bin, Tex $tex)
    {
        $this->mls = $mls;
        $this->glg = $glg;
        $this->inst = $inst;
//        $this->fsb = $fsb;
        $this->grf = $grf;
        $this->ifp = $ifp;
        $this->bin = $bin;
        $this->tex = $tex;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('archive:unpack')
            ->setAliases(['unpack'])
            ->setDescription('Unpack a GLG, INST or MLS file')
            ->addArgument('file', InputArgument::REQUIRED, 'This file will be extracted')
            ->addOption('only-unzip', null, null, 'Will only unzip the file')
            ->addOption('save-json', null, null, 'Used for Animation files')

            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $helper = $this->getHelper('question');

        $file = $input->getArgument('file');


        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $folder = realpath(pathinfo($file, PATHINFO_DIRNAME));


        $content = file_get_contents($file);
        $contentAsHex = bin2hex($content);

        // we found a zLib compressed file, extract them
        if (substr($contentAsHex, 0, 8) === "5a32484d"){
            $output->writeln("zLib compressed file detected");
            $content = $this->mls->uncompress( $content );
            $contentAsHex = bin2hex($content);
        }


        if ($input->getOption('only-unzip')){
            $outputTo = $folder . '/' . $filename . "." . $ext . ".unzipped";

            file_put_contents(
                $outputTo,
                $content
            );

            $output->writeln("done.");
            return;

        }

        if (substr($contentAsHex, 0, 8) == "474e4941") { // GNIA

            $grf = $this->grf->unpack($content);

            $outputTo = $folder . '/' . $filename . ".grf.uncompressed";

            file_put_contents(
                $outputTo,
                \json_encode($grf, JSON_PRETTY_PRINT)
            );

        // we found a MLS scipt
        }else if (substr($contentAsHex, 0, 8) == "4d484c53" || substr($contentAsHex, 0, 8) == "4d485343") { // MHSC
            $output->writeln("MHLS (MLS) file detected");

            $question = new ChoiceQuestion(
                'Please provide the source',
                array('mh1', 'mh2'),
                '0'
            );

            $game = strtolower($helper->ask($input, $output, $question));

            $outputTo = $folder . '/extracted/' . $filename . "/";
            @mkdir($outputTo);


            file_put_contents(
                $outputTo . 'ori.uncompressed',
                $content
            );

            $mhls = $this->mls->unpack($content, $game, $output);

            $this->saveMHLS( $mhls,  $outputTo);
        }
        // BIN animation file
        else if (
            (strpos(strtolower($content), "anpk") !== false) &&
            (substr($contentAsHex, 0, 8) != "414e504b") && // is not ANPK header
            (substr($contentAsHex, 0, 8) != "414e4354") // is not ANCT header
        ){

            $outputTo = $folder . '/' . $filename . "/";
            @mkdir($outputTo, 0777, true);
            $this->bin->unpack($contentAsHex, $outputTo, $input->getOption('save-json'));

        }
        // GLG Record
        else if (
            (strpos(strtolower($content), "record ") !== false) &&
            (strpos(strtolower($content), "end") !== false)
        ){

            $output->writeln("GLG file detected");
            $outputTo = $folder . '/' . $filename . "." . $ext . ".txt";

            file_put_contents(
                $outputTo,
                $content
            );
        }

        // FSB format
        else if (
            (substr($contentAsHex, 0, 6) == "465342")) {  // FSB

            $this->fsb->unpack( $content );

        }
        // TEX format
        else if (
            (substr($contentAsHex, 0, 8) == "54434454")
        ) {

            $outputTo = $folder . '/' . $filename . "/";
            @mkdir($outputTo, 0777, true);

            $this->tex->unpack($contentAsHex, $outputTo);
        }
        // IFP format
        else if (
            (substr($contentAsHex, 0, 8) == "414e4354") // ANCT
        ) {
            $outputTo = $folder . '/' . $filename . "/";
            @mkdir($outputTo, 0777, true);

            $this->ifp->unpack( $content, $output, $outputTo, $input->getOption('save-json') );

            // INST format
        } else if (
            (substr($contentAsHex, 4, 4) == "0000") &&
            (substr($contentAsHex, 10, 6) == "000000")
        ) {

            $output->writeln("INST file detected");


            $question = new ChoiceQuestion(
                'Please provide the game',
                array('mh1', 'mh2'),
                '0'
            );

            $game = strtolower($helper->ask($input, $output, $question));

            $unpacked = $this->inst->unpack( $content, $game );

            $outputTo = $folder . '/' . $filename . "." . $ext . ".json";

            file_put_contents(
                $outputTo,
                \json_encode($unpacked, JSON_PRETTY_PRINT)
            );

        }else{
            die("unknown ");

        }


        $output->writeln('done');
    }

    private function saveMHLS($mhls, $outputTo ){

        @mkdir($outputTo . "supported/", 0777, true);
        @mkdir($outputTo . "not-supported/", 0777, true);

        $levelScript = false;

        foreach ($mhls as $index => $mhsc) {

            $compiler = new Compiler();
            try{

                $compiled = $compiler->parse($mhsc['DBUG']['SRCE'], $levelScript);

                if ($index == 0){
                    $levelScript = $compiled;
                }

                if ($compiled['CODE'] != $mhsc['CODE']) throw new \Exception('CODE did not match');

                file_put_contents($outputTo . 'supported/' . $index . "#" . $mhsc['NAME'] . '.srce' , $mhsc['DBUG']['SRCE']);

            }catch(\Exception $e){

                file_put_contents($outputTo . 'error.log' , sprintf("%s occured in %s#%s\n", $e->getMessage(), $index, $mhsc['NAME']), FILE_APPEND);
                file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.code', implode("\n", $mhsc['CODE']));

                if (isset($mhsc['DATA'])){
                    file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.data' , implode("\n", $mhsc['DATA']));
                }

                file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.srce' , $mhsc['DBUG']['SRCE']);
                file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.scpt', \json_encode( $mhsc['SCPT'], JSON_PRETTY_PRINT));
                file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.smem' , $mhsc['SMEM']);
                file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.entt', \json_encode( $mhsc['ENTT'], JSON_PRETTY_PRINT));

                if (isset($mhsc['STAB'])) {
                    file_put_contents($outputTo . "not-supported/" . $index . "#" . $mhsc['NAME'] . '.stab', \json_encode( $mhsc['STAB'], JSON_PRETTY_PRINT));
                }
            }
        }

    }
}