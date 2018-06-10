<?php

namespace App\Command;

use App\Service\Glg;
use App\Service\PatchSystem;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PatchAvailableCommand extends Command
{

    /** @var FilesystemCache  */
    private $cache;

    private $patchSystem;

    public function __construct( PatchSystem $patchSystem)
    {
        $this->patchSystem = $patchSystem;
        $this->cache = new FilesystemCache;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('patch:available')
            ->setDescription('List available Patches')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->cache->has('workdir') == false){
            $output->writeln('<error>Error</error> Please set your Working Directory first.');
            $output->writeln('php app.php <info>set:workdir \<path-to-manhunt-2-folder></info>');
            return;
        }

        $patches = $this->patchSystem->getAvailable();

        $output->writeln([
            sprintf('%d Patches Available', count($patches)),
            '----------'
        ]);

        foreach ($patches as $patch) {
            $output->writeln(
                '<info>' . str_pad($patch['code'], 26, ' ') . '</info>' . $patch['description']
            );
        }

        $output->writeln([
            '----------',
            'To install: <info>php app.php patch:install \<patch-name></info>'
        ]);

    }
}