<?php

namespace App\Command;

use App\Service\BytecodeExplain;
use App\Service\Inst;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BytecodeExplainCommand extends Command
{

    /** @var BytecodeExplain */
    private $bytecodeExplain;


    public function __construct(BytecodeExplain $bytecodeExplain)
    {
        $this->bytecodeExplain = $bytecodeExplain;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('bytecode:explain')
            ->setDescription('Analysis the bytecode ')
            ->addArgument('file', InputArgument::REQUIRED, 'The code File.')

//            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $file = $input->getArgument('file');
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if (strtolower($ext) != 'code') throw new \Exception('Please provide a CODE file');

        $output->write(sprintf('Unpacking %s ... ', basename($file)));
        $result = $this->bytecodeExplain->explain( file_get_contents($file) );

        $result = array_map(function($entry){
            return $entry[0] . (isset($entry[1]) ? ',' . $entry[1] : '');
        }, $result);

        file_put_contents(
            $file . '.explained.csv',
            implode("\n", $result)
        );

        $output->writeln('done');
    }
}