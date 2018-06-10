<?php

namespace App\Command;

use App\Service\Glg;
use App\Service\PatchSystem;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PatchCommand extends Command
{

    /** @var FilesystemCache  */
    private $cache;

    /** @var PatchSystem  */
    private $patchSystem;


    public function __construct( PatchSystem $patchSystem)
    {
        $this->cache = new FilesystemCache;
        $this->patchSystem = $patchSystem;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('patch:install')
            ->addArgument('patch', InputArgument::REQUIRED, 'Patch name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {




    }
}