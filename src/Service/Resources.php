<?php

namespace App\Service;

use App\Service\Archive\Archive;
use App\Service\Archive\Bin;
use App\Service\Archive\Bsp;
use App\Service\Archive\Col;
use App\Service\Archive\Dds;
use App\Service\Archive\Dff;
use App\Service\Archive\Fsb;
use App\Service\Archive\Glg;
use App\Service\Archive\Grf;
use App\Service\Archive\Gxt;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\Mdl;
use App\Service\Archive\Mls;
use App\Service\Archive\Pak;
use App\Service\Archive\Tex;
use App\Service\Archive\Txd;
use Symfony\Component\Finder\Finder;

class Resources
{

    public $workDirectory = '';

    /** @var Archive[]  */
    private $archives = [
        Bin::class,     Col::class,     Dds::class,     Dff::class,     Grf::class,
        Gxt::class,     Ifp::class,     Inst::class,    Mls::class,     Tex::class,
        Pak::class,     Glg::class,     Mdl::class,     Bsp::class,     Txd::class
    ];


    public function load( $relativeFile, $game, $platform ){

        $absoluteFile = $this->workDirectory . $relativeFile;

        if (!file_exists( $absoluteFile ) && !is_dir($absoluteFile)) throw new \Exception(sprintf('File/Folder not found: %s', $absoluteFile));

        $handler = false;

        if (is_dir($absoluteFile)){
            $input = new Finder();
            $input->files()->in($absoluteFile);
        }else{
            $input = new NBinary( file_get_contents($absoluteFile) );
        }

        foreach ($this->archives as $archive) {
            if ($archive::canHandle( $relativeFile, $input, $game, $platform )){
                /** @var Archive $handler */
                $handler = new $archive();
                break;
            }
        }

        if ($handler == false) throw new \Exception(sprintf('No handler available for file %s', $absoluteFile));

        return new Resource(
            $handler,
            $relativeFile,
            $input
        );
    }

}