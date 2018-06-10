<?php

namespace App\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
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


    public function __construct(Mls $mls, Glg $glg, Inst $inst)
    {
        $this->mls = $mls;
        $this->glg = $glg;
        $this->inst = $inst;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('archive:unpack')
            ->setAliases(['unpack'])
            ->setDescription('Unpack a GLG, INST or MLS file')
            ->addArgument('file', InputArgument::REQUIRED, 'This file will be extracted')

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

        // we found a MLS scipt
        if (substr($contentAsHex, 0, 8) == "4d484c53") { // MHLS
            $output->writeln("MHLS (MLS) file detected");

            $question = new ChoiceQuestion(
                'Please provide the game (defaults to mh1 and mh2)',
                array('mh1', 'mh2'),
                '0'
            );

            $game = strtolower($helper->ask($input, $output, $question));

            $outputTo = $folder . '/extracted/' . $filename . "." . $ext . "/";


            file_put_contents(
                $outputTo . 'ori.uncompressed',
                $content
            );

            $mhls = $this->mls->unpack($content, $game, $output);

            $this->saveMHLS( $mhls,  $outputTo);

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

        // INST format
        else if (
            (substr($contentAsHex, 4, 4) == "0000") &&
            (substr($contentAsHex, 10, 6) == "000000")
        ) {

            $output->writeln("INST file detected");

            $unpacked = $this->inst->unpack( $content );

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

        @mkdir($outputTo, 0777, true);

        foreach ($mhls as $index => $mhsc) {

            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.code', implode("\n", $mhsc['CODE']));
            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.nameremain', $mhsc['NAME_remain']);

            if (isset($mhsc['DATA'])){
                file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.data' , implode("\n", $mhsc['DATA']));
                file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.dataraw' , $mhsc['DATARAW']);
            }

            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.srce' , $mhsc['DBUG']['SRCE']);
            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.line' , implode("\n", $mhsc['DBUG']['LINE']));

            $result = array_map(function($entry){
                return $entry['name'] . ',' . $entry['priority'] . "," . $entry['position'];
            }, $mhsc['SCPT']);

            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.scpt' , implode("\n", $result));
            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.dmem' , $mhsc['DMEM']);
            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.smem' , $mhsc['SMEM']);
            file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.entt' , $mhsc['ENTT']['name'] . ',' . $mhsc['ENTT']['offset']);

            if (isset($mhsc['STAB'])) {
                file_put_contents($outputTo . $index . "#" . $mhsc['NAME'] . '.stab', \json_encode( $mhsc['STAB'], JSON_PRETTY_PRINT));
            }
        }

    }
}