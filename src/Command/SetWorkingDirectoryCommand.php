<?php

namespace App\Command;

use App\Service\Glg;
use App\Service\Prepare;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetWorkingDirectoryCommand extends Command
{

    /** @var FilesystemCache  */
    private $cache;

    /** @var Prepare  */
    private $prepare;

    public function __construct(Prepare $prepare)
    {
        $this->cache = new FilesystemCache;
        $this->prepare = $prepare;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('set:workdir')
            ->setDescription('Set the Manhunt 2 Installation folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'The Manhunt 2 File.')

//            ->setHelp('This command allows you to create a user...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folder = $input->getArgument('folder');

        if (file_exists($folder . '/Manhunt2.exe')){
            $this->cache->clear();
            $this->cache->set('workdir', realpath($folder));
            $output->writeln(sprintf('Work directory set to <info>%s</info>', $folder));

            $output->write('Decoding given GLG files... ');
            $this->prepare->cacheGlgContent();
            $output->writeln('<info>ok</info>');

            $output->write('Decoding given INST files... ');
            $this->prepare->cacheInstContent();
            $output->writeln('<info>ok</info>');


        }else{
            $output->writeln(sprintf(
                '<error>Error</error> Work directory <comment>%s</comment> did not contain the <comment>Manhunt2.exe</comment>',
                $folder
            ));
        }

    }
}