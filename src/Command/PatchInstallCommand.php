<?php

namespace App\Command;

use App\Service\Glg;
use App\Service\PatchSystem;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PatchInstallCommand extends Command
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
            ->setDescription('List available Patches')
            ->addArgument('patch', InputArgument::REQUIRED, 'Patch name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->cache->has('workdir') == false){
            $output->writeln('<error>Error</error> Please set your Working Directory first.');
            $output->writeln('php app.php <info>set:workdir \<path-to-manhunt-2-folder></info>');
            return;
        }

        $patchCode = $input->getArgument('patch');

        if ($patchCode == "any"){
            foreach ($this->patchSystem->getAvailable() as $patch) {


                if ($this->patchSystem->isPatchActive( $patch['code'] )){
                    $output->writeln('Patch is already <info>active</info>');
                    continue;
                }

                $output->write(sprintf('Activating Patch <info>%s</info>... ', $patch['code']));
                $this->patchSystem->activatePatch( $patch['code'] );
                $output->writeln(sprintf('<info>ok</info>'));
            }
        }else{

            if (!$this->patchSystem->exists( $patchCode )){
                $output->writeln('<error>Error</error> Unknown Patch');
                return;
            }
//
//            if ($this->patchSystem->isPatchActive( $patchCode )){
//                $output->writeln('Patch is already <info>active</info>');
//                return;
//            }

            $this->patchSystem->activatePatch( $patchCode );

        }

        $this->patchSystem->applyPatches();
        $output->writeln('Patch <info>activated</info>');


    }
}