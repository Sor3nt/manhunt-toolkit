<?php

namespace App\Service;

use App\Service\Archive\Afs;
use App\Service\Archive\Archive;
use App\Service\Archive\Bin;
use App\Service\Archive\Bsp;
use App\Service\Archive\Col;
use App\Service\Archive\ContextMapBin;
use App\Service\Archive\Dds;
use App\Service\Archive\Dff;
use App\Service\Archive\Dir;
use App\Service\Archive\Fev;
use App\Service\Archive\Font;
use App\Service\Archive\Fsb3;
use App\Service\Archive\Fsb4;
use App\Service\Archive\Fxb;
use App\Service\Archive\Glg;
use App\Service\Archive\Grf;
use App\Service\Archive\Gxt;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\Mdl;
use App\Service\Archive\Mls;
use App\Service\Archive\Pak;
use App\Service\Archive\Rib;
use App\Service\Archive\Tag;
use App\Service\Archive\Tex;
use App\Service\Archive\Pack;
use App\Service\Archive\TxdPlaystation;
use App\Service\Archive\TxdPlaystation2Mh1;
use App\Service\Archive\TxdWii;
use App\Service\Archive\Vas;
use App\Service\Archive\Wav;
use Symfony\Component\Finder\Finder;

class Resources
{

    public $workDirectory = '';

    /** @var Archive[]  */
    private $archives = [
        Pack::class, Font::class, Rib::class,
        Col::class,     Dds::class,     Fxb::class,     Grf::class, Fev::class, Dir::class, Tag::class,
        Gxt::class,     Ifp::class,     Inst::class,    Mls::class,     Tex::class, Fsb4::class, Fsb3::class, Wav::class,
        Pak::class,     Glg::class,     Mdl::class,     Bsp::class,     TxdPlaystation::class, TxdWii::class , Afs::class,
        Dff::class,     Vas::class,     Bin::class,     TxdPlaystation2Mh1::class, ContextMapBin::class
    ];


    public function load( $relativeFile, $game, $platform ){

        $absoluteFile = $this->workDirectory . $relativeFile;

        if (!file_exists( $absoluteFile ) && !is_dir($absoluteFile)){
            echo sprintf('File/Folder not found: %s', $absoluteFile);
            exit;
        }

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

        if ($handler == false){
            echo sprintf('No handler available for file %s', $absoluteFile);
            exit;
        }

        return new Resource(
            $handler,
            $relativeFile,
            $input
        );
    }

}