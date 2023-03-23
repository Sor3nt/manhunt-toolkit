<?php

namespace App\Service\Archive;

use App\Service\File;
use App\Service\NBinary;
use Exception;

/**
 * Based on AIX2ADX 0.1 by hcs
 *
 *
 * Class AixArchive
 * @package App\Service\Archive
 */
class AixArchive
{

    /** @var NBinary */
    private $binary;

    /**
     * AfsArchive constructor.
     * @param NBinary $binary
     * @throws Exception
     */
    public function __construct(NBinary $binary)
    {
        if ($binary->get(3) !== "AIX") throw new Exception('File is not a AIX Container');
        $this->binary = $binary;
    }


    /**
     * @return File[]
     * @throws Exception
     */
    public function extract()
    {

        $eos = 0;
        $fileIndex = 0;
        for ($goRound = 0; $goRound >=0; $goRound++) {

            $chanCount = -1;
            $searchStart = $eos;

            for ($channelToCopy = 0; ($chanCount < 0 || $channelToCopy < $chanCount); $channelToCopy++) {
                $this->binary->current = $searchStart;
                $curAix = $searchStart;
                $done = 0;

                $data = "";
                while(!$done){

                    if ($this->binary->current >= $this->binary->length()){
                        $chanCount = 0;
                        $channelToCopy = 0;
                        $goRound = -2;
                        break;
                    }

                    $head = $this->binary->consume(3, NBinary::BINARY);


                    if ($head !== "AIX") {

                        //A02 MH2 Ps2 leak
                        if($this->binary->length() === 51634176){
                            echo "Invalid Block start (A02)\n";
                            return [ new File( new NBinary($data) )];
                        }
                        throw new Exception("Invalid Block start");
                    }

                    $action = $this->binary->consume(1, NBinary::BINARY);

                    $nextAix = $curAix + 8 + $this->binary->consume(4, NBinary::BIG_U_INT_32);

                    switch ($action){
                        case 'F':
                            //header
                            break;
                        case 'E':
                            $done = true;
                            $eos = $nextAix;
                            break;

                        case 'P':

                            $channel = $this->binary->consume(1, NBinary::INT_8);
                            $chanCount = $this->binary->consume(1, NBinary::INT_8);

                            $size = $this->binary->consume(2, NBinary::BIG_U_INT_16);
                            $fame = $this->binary->consume(4, NBinary::BIG_U_INT_32);

                            if ($channel == $channelToCopy){

                                $data .= $this->binary->consume($size , NBinary::BINARY);
                            }

                            break;

                    }

                    $this->binary->current = $nextAix;
                    $curAix = $nextAix;

                }

                if ($data !== ""){
                    $files[] = new File( new NBinary($data) );
                }
                $fileIndex++;

            }

        }

        return $files;

    }

}
