<?php

namespace App\Command;

use App\Service\Bytecode;
use App\Service\BytecodeExplain;
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
        list($bytecode, $strings) = $this->bytecode->process( file_get_contents($file), $game );

        foreach ($bytecode as &$line) {
            $line = bin2hex($line);
        }

        $target = str_replace('.srce', '', $file);

        file_put_contents(
            $target . '.code',
            implode("\n", $bytecode)
        );

        file_put_contents(
            $target . '.data',
            implode("\n", $strings)
        );

        file_put_contents(
            $target . '.line',
            implode("\n", $strings)
        );

        $output->writeln('done');
    }
}