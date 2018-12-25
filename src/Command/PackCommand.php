<?php

namespace App\Command;

use App\Service\Archive\Bin;
use App\Service\Archive\Glg;
use App\Service\Archive\Grf;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use App\Service\Archive\ZLib;
use App\Service\Compiler\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    /** @var Ifp  */
    private $ifp;

    /** @var Grf  */
    private $grf;

    /** @var Bin  */
    private $bin;


    public function __construct()
    {
        $this->mls = new Mls();
        $this->glg = new Glg();
        $this->inst = new Inst();
        $this->ifp = new Ifp();
        $this->grf = new Grf();
        $this->bin = new Bin();

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('archive:pack')
            ->setAliases(['pack', 'build', 'compress'])
            ->setDescription('Pack a source file/folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'The folder/file.')
            ->addArgument('output', InputArgument::OPTIONAL, 'Output result to this file')
            ->addOption(
                'game',
                null,
                InputOption::VALUE_OPTIONAL,
                'mh1 or mh2?',
                null
            );//            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $helper = $this->getHelper('question');

        $folder = realpath($input->getArgument('folder'));
        $saveTo = $input->getArgument('output');
        $game = $input->getOption('game');


        if(is_dir(realpath($folder))){

            $finder = new Finder();
            $finder->name('/\.srce/')->files()->in( $folder );

            //MLS data folder
            if ($finder->count()){


                if (is_null($game)){
                    $question = new ChoiceQuestion(
                        'Please provide the game (defaults to mh1 and mh2)',
                        array('mh1', 'mh2'),
                        '0'
                    );

                    $game = strtolower($helper->ask($input, $output, $question));
                }

                if (is_null($saveTo)){
                    $saveTo = $folder.'.mls';
                }


                $this->packMLS(realpath($folder), $game, $saveTo);
            }else{

                $finder = new Finder();
                $finder->name('executions')->directories()->in( $folder );

                //we pack a strmanim_pc.bin
                if ($finder->count() == 1){
                    if (is_null($saveTo)){
                        $saveTo = $folder.'.bin.repack';
                    }

                    $this->packStrmAnimPcBin( realpath($folder), $saveTo);



                }else{

                    if (is_null($saveTo)){
                        $saveTo = $folder.'.ifp.repack';
                    }


                    if (is_null($game)) {
                        $question = new ChoiceQuestion(
                            'Please provide the game',
                            array('mh1', 'mh2'),
                            '0'
                        );

                        $game = strtolower($helper->ask($input, $output, $question));
                    }

                    $this->packIfp( realpath($folder), $game, $saveTo);

                }
            }

        }else{

            $content = file_get_contents($folder);

            // GLG Record
            if (
                (strpos(strtolower($content), "record ") !== false) &&
                (strpos(strtolower($content), "end") !== false)
            ){

                $output->writeln('Packing of glg files is not required. Just place the file into the right place.');

            // col file
            }else if (
                (strpos($content, "min") !== false) &&
                (strpos($content, "max") !== false) &&
                (strpos($content, "center") !== false)
            ){

               // $content = $this->col->pack(\json_decode($content, true));
                // file_put_contents($saveTo, hex2bin($content));

                // grf file
            }else if (
                (strpos($content, "block1") !== false) &&
                (strpos($content, "block2") !== false) &&
                (strpos($content, "block3") !== false)
            ){

                if (is_null($saveTo)){
                    $saveTo = $folder.'.repacked';
                }

                $hex = $this->grf->pack(\json_decode($content, true));

                file_put_contents($saveTo, hex2bin($hex));

                // Inst file
            }else if (
                (strpos($content, "record") !== false) &&
                (strpos($content, "internalName") !== false) &&
                (strpos($content, "entityClass") !== false)
            ){

                if (is_null($saveTo)){
                    $saveTo = $folder.'.repacked';
                }


                if (is_null($game)) {
                    $question = new ChoiceQuestion(
                        'Please provide the game',
                        array('mh1', 'mh2'),
                        '0'
                    );

                    $game = strtolower($helper->ask($input, $output, $question));
                }

                $this->packInst( $content, $saveTo, $game);

            }else{
                die("unable to detect file or unsupported");
            }


        }

        $output->writeln('');
        $output->writeln('done');
    }

    private function packStrmAnimPcBin($folder, $saveTo){


        $finder = new Finder();
        $finder->depth('== 0')->directories()->in($folder . '/executions');

        $executions = [];
        foreach ($finder as $directory) {

            $executionId = $directory->getFilename();
            $executions[ $executionId ] = [];

            $execFinder = new Finder();
            $execFinder->depth('== 0')->directories()->in($directory->getRealPath());

            foreach ($execFinder as $executionFolder) {
                $executionSection = $executionFolder->getFilename();
                $executions[ $executionId ][$executionSection] = [];

                $fileFinder = new Finder();
                $fileFinder->files()->in($executionFolder->getRealPath());

                foreach ($fileFinder as $file) {
                    $excutionName = $file->getFilename();
                    $executions[ $executionId ][$executionSection][$excutionName] = \json_decode($file->getContents(), true);
                }


                uksort($executions[ $executionId ][$executionSection], function($a, $b){
                    return explode("#", $a)[0] > explode("#", $b)[0];
                });

            }
        }

        uksort($executions, function($a, $b){
            return explode("#", $a)[0] > explode("#", $b)[0];
        });

        $finder = new Finder();
        $finder->depth('== 0')->directories()->in($folder . '/envExecutions');

        $envExecutions = [];
        foreach ($finder as $directory) {

            $executionId = $directory->getFilename();
            $envExecutions[ $executionId ] = [];


            $fileFinder = new Finder();
            $fileFinder->files()->in($directory->getRealPath());

            foreach ($fileFinder as $file) {
                $excutionName = $file->getFilename();
                $envExecutions[ $executionId ][$excutionName] = \json_decode($file->getContents(), true);
            }

            uksort($envExecutions[ $executionId ], function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });


        }

        $hex = $this->bin->pack($executions, $envExecutions);
        file_put_contents($saveTo, hex2bin($hex));

    }

    private function packIfp($folder, $game, $saveTo){


        $finder = new Finder();
        $finder->files()->in($folder);

        $ifp = [];

        foreach ($finder as $file) {

            $folder = $file->getPathInfo()->getFilename();

            if (!isset($ifp[$folder])) $ifp[$folder] = [];

            $ifp[$folder][$file->getFilename()] = \json_decode($file->getContents(), true);
        }

        uksort($ifp, function($a, $b){
            return explode("#", $a)[0] > explode("#", $b)[0];
        });

        foreach ($ifp as &$item) {
            uksort($item, function($a, $b){
                return explode("#", $a)[0] > explode("#", $b)[0];
            });

        }

        $hex = $this->ifp->pack($ifp, $game);

        file_put_contents($saveTo, hex2bin($hex));
    }


    private function packMLS($folder, $game, $saveTo){

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

        $compiler = new Compiler();

        $levelScriptCompiled = $compiler->parse($scripts[0]['SRCE'], false, $game);

        foreach ($scripts as &$script) {
            if (!isset($script['CODE'])){

                echo "Compile " . $script['NAME'] . "\n";

                $compiler = new Compiler();
                $name = $script['NAME'];
                $script = $compiler->parse($script['SRCE'], $levelScriptCompiled);
                $script['NAME'] = $name;

                unset($script['extra']);
            }
        }

        /**
         * Translate the files into Byte
         */

        $mlsFile = $this->mls->pack($scripts);

        /**
         * compress the file and store it
         */
        $compressedMls = ZLib::compress($mlsFile);

        file_put_contents($saveTo, $mlsFile);
        file_put_contents($saveTo . '.compressed', $compressedMls);
    }

    private function packInst($content, $saveTo, $game){
        $content = $this->inst->pack( \json_decode($content, true), $game );
        file_put_contents($saveTo, $content);

    }
}