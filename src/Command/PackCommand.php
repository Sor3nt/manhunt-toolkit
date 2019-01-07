<?php

namespace App\Command;

use App\MHT;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PackCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('archive:pack')
            ->setAliases(['pack', 'build', 'compress'])
            ->setDescription('Pack a source file/folder')
            ->addArgument('file', InputArgument::REQUIRED, 'File or folder.')
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
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $file = realpath($input->getArgument('file'));
        $game = $input->getOption('game');
        $platform = $input->getOption('platform');

        $outputTo = str_replace('#','.', $file);
        $outputTo = str_replace('.json','', $outputTo);

        //load the resource
        $resources = new Resources();
        $resource = $resources->load($file, $game, $platform);

        $handler = $resource->getHandler();

        $output->writeln( sprintf('Identify as %s ', $handler->name));
        $output->write( sprintf('Processing %s ', $file));

        $result = $handler->pack( $resource->getInput(), $game, $platform );

        file_put_contents($outputTo, $result);

        $output->writeln(sprintf("\nPacket to %s",  $outputTo));

    }

}