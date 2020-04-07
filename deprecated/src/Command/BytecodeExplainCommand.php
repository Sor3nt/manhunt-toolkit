<?php

namespace App\Command;

use App\MHT;
use App\Service\BytecodeExplain;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Analysis the given MLS *.code bytecode.')
            ->addArgument('file', InputArgument::REQUIRED, 'The *.code File.')
            ->addOption(
                'game',
                null,
                InputOption::VALUE_OPTIONAL,
                'mh1 or mh2?',
                MHT::GAME_AUTO
            )

            ->addOption(
                'platform',
                null,
                InputOption::VALUE_OPTIONAL,
                'pc,ps2,psp,wii,xbox?',
                MHT::PLATFORM_AUTO
            )        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $game = $input->getOption('game');
        $platform = $input->getOption('platform');

        $file = $input->getArgument('file');
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if (strtolower($ext) != 'code') throw new \Exception('Please provide a CODE file');

        $output->write(sprintf('Unpacking %s ... ', basename($file)));
        $result = $this->bytecodeExplain->explain( file_get_contents($file), $game, $platform );

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