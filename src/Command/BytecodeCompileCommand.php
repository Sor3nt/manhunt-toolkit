<?php

namespace App\Command;

use App\Bytecode\Mls\Sequence;
use App\Service\Bytecode;
use App\Service\BytecodeExplain;
use App\Service\Compiler\Compiler;
use App\Service\Inst;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BytecodeCompileCommand extends Command
{

    /** @var Bytecode */
    private $bytecode;


    public function __construct(Bytecode $bytecode)
    {

        $this->bytecode = $bytecode;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('bytecode:compile')
            ->setDescription('compile srce ')
            ->addArgument('file', InputArgument::REQUIRED, 'The srce File.')
            ->addArgument('game', InputArgument::REQUIRED, 'mh1 or mh2.')

//            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $file = $input->getArgument('file');
        $game = $input->getArgument('game');
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if (strtolower($ext) != 'srce') throw new \Exception('Please provide a SRCE file');
        if ($game !== "mh1" && $game !== "mh2") throw new \Exception('Please provide the target game mh1 or mh2');

        $output->write(sprintf('Compile %s ... ', basename($file)));

        /** @var Sequence[] $bytecode */

        $compiler = new Compiler();
        list($sectionCode, $sectionDATA) = $compiler->parse(file_get_contents($file));

        $target = str_replace('.srce', '', $file);

        file_put_contents(
            $target . '.code',
            implode("\n", $sectionCode)
        );


        file_put_contents(
            $target . '.scpt',
            'oncreate,0,0'
//            'oncreate,0,' . (count($sectionCode) * 4)
        );

        file_put_contents(
            $target . '.data',
            implode("\n", $sectionDATA)
        );


        file_put_contents(
            $target . '.smem',
            '68596'
        );


        $output->writeln('done');
    }
}