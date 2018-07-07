<?php

namespace App\Command;

use App\Service\Archive\Glg;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;

class PackCommand extends Command
{

    /** @var Mls */
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
            ->setName('archive:pack')
            ->setAliases(['pack'])
            ->setDescription('Pack a source file to GLG, INST or MLS')
            ->addArgument('folder', InputArgument::REQUIRED, 'The folder/file.')
            ->addArgument('output', InputArgument::OPTIONAL, 'Output result to this file')
//            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $helper = $this->getHelper('question');

        $folder = $input->getArgument('folder');
        $saveTo = $input->getArgument('output');

        //MLS data folder
        if(is_dir(realpath($folder))){


            $question = new ChoiceQuestion(
                'Please provide the game (defaults to mh1 and mh2)',
                array('mh1', 'mh2'),
                '0'
            );

            $game = strtolower($helper->ask($input, $output, $question));

            if (is_null($saveTo)){
                $saveTo = $folder.'-repacked';
            }


            $this->packMLS(realpath($folder), $game, $saveTo, $output);

        }else{

            $content = file_get_contents($folder);

            // GLG Record
            if (
                (strpos(strtolower($content), "record ") !== false) &&
                (strpos(strtolower($content), "end") !== false)
            ){

                if (is_null($saveTo)){
                    $saveTo = $folder.'.repacked';
                }

                $this->packGLG( $content, $saveTo);

            // Inst file
            }else if (
                (strpos($content, "record") !== false) &&
                (strpos($content, "internalName") !== false) &&
                (strpos($content, "entityClass") !== false)
            ){

                if (is_null($saveTo)){
                    $saveTo = $folder.'.repacked';
                }


                $question = new ChoiceQuestion(
                    'Please provide the game (defaults to mh1 and mh2)',
                    array('mh1', 'mh2'),
                    '0'
                );

                $game = strtolower($helper->ask($input, $output, $question));

                $this->packInst( $content, $saveTo, $game);

            }else{
                die("unable to detect file or unsupported");
            }


        }

        $output->writeln('done');
    }

    private function packMLS($folder, $game, $saveTo, OutputInterface $output = null){

        /**
         * To build a valid MLS file, these sections are important
         *
         * - CODE
         * - DATA
         * - SMEM
         * - ENTT
         * - SCPT
         * - NAME
         *
         * The remaining sections are debug sections and only useable with the right exe file (current unavailable)
         *
         * - DMEM
         * - LINE
         * - SRCE
         */

        /**
         * Prepare the date, load any file into an array
         */
        $finder = new Finder();
        $finder->name('/\.code|\.data|\.dataraw|\.dmem|\.entt|\.line|\.nameremain|\.scpt|\.smem|\.stab|\.srce/')->files()->in( $folder );

        $scripts = [];

        foreach ($finder as $file) {

            list($index, $filename) = explode("#", $file->getFilename());
            $index = (int) $index;

            list($scriptName, $section) = explode(".", $filename);

            if (!isset($scripts[$index])) $scripts[$index] = [ "NAME" => $scriptName ];

            $scripts[$index][ strtoupper($section) ] = $file->getContents();

        }

        /**
         * Translate the files into Byte
         */

        $mlsFile = $this->mls->pack($scripts, $game, false, $output);

        /**
         * compress the file and store it
         */
        $compressedMls = $this->mls->compress($mlsFile);

        file_put_contents($saveTo, $compressedMls);
        file_put_contents($saveTo . '.uncompressed', $mlsFile);


    }

    private function packGLG($content, $saveTo){
        $content = $this->glg->compress( $content );
        file_put_contents($saveTo, $content);

    }
    private function packInst($content, $saveTo, $game){
        $content = $this->inst->pack( \json_decode($content, true), $game );
        file_put_contents($saveTo, $content);

    }
}