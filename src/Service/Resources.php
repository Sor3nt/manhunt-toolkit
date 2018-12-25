<?php

namespace App\Service;

use App\Service\Archive\Bin;
use App\Service\Archive\Col;
use App\Service\Archive\Dff;
use App\Service\Archive\Grf;
use App\Service\Archive\Ifp;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use App\Service\Archive\Tex;
//use App\Service\Archive\Txd;
use App\Service\Archive\ZLib;

class Resources
{

    public $workDirectory = '';

    /**
     * @param $relativeFile
     * @return \App\Service\Resource
     * @throws \Exception
     *
     * todo: caching verbauen
     */
    public function load( $relativeFile, $forceFileExtension = false ){

        $absoluteFile = $this->workDirectory . $relativeFile;
        if (!file_exists( $absoluteFile )) throw new \Exception(sprintf('File not found: %s', $absoluteFile));

        $fileExtension = strtolower(pathinfo($absoluteFile, PATHINFO_EXTENSION));

        $content = file_get_contents($absoluteFile);
        $contentAsHex = bin2hex($content);

        // we found a zLib compressed file
        if (substr($contentAsHex, 0, 8) === "5a32484d" || substr($contentAsHex, 0, 8) === "4d48325a"){
            $content = ZLib::uncompress( $content );
        }

        $result = $content;

        if ($forceFileExtension) $fileExtension = $forceFileExtension;

        switch ($fileExtension){

            case 'glg':
            case 'dxt1':
            case 'dxt2':
            case 'dxt3':
            case 'dxt4':
            case 'dxt5':
                break;

            case 'scc':
            case 'mls':
                $handler = new Mls();
                $result = $handler->unpack($content, 'mh2');
                break;
            case 'bin':

                if (mb_substr($content, 0, 4, '8bit')  == "\x01\x00\x00\x00"){

                    //real bin file
                }else{
                    return $this->load($relativeFile, 'inst');
                }

                break;

            case 'ifp':
                break;

            case 'dff':
                $handler = new Dff();
                $result = $handler->unpack($content);
                break;

            case 'grf':
                $handler = new Grf();
                $result = $handler->unpack($content);
                break;

            case 'col':
                $handler = new Col();
                $result = $handler->unpack($content);
                break;

            case 'tex':
                $handler = new Tex();
                $result = $handler->unpack($content);
                break;

            case 'inst':

                $handler = new Inst();
                $result = $handler->unpack($content);
                break;
            default:
                throw new \Exception(sprintf('Unable to load resource %s, unknown handler', $relativeFile));
        }

        return new Resource(
            $result,
            $fileExtension,
            $relativeFile,
            $content

        );
    }

    public function saveMls( Resource $resource ){

    }

}