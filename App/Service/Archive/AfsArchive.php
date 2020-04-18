<?php

namespace App\Service\Archive;

use App\Service\File;
use App\Service\NBinary;
use Exception;

/**
 * Class AfsArchive
 * @package App\Service\Archive
 */
class AfsArchive
{

    /** @var NBinary */
    private $binary;

    private $entryCount = 0;

    /**
     * AfsArchive constructor.
     * @param NBinary $binary
     * @throws Exception
     */
    public function __construct(NBinary $binary)
    {
        if ($binary->get(3) !== "AFS") throw new Exception('File is not a AFS Container');
        $this->binary = $binary;

        $this->entryCount = $binary->consume(4, NBinary::INT_32, 4);
    }


    /**
     * @param NBinary $binary
     * @return NBinary
     */
    private function getBlock(NBinary $binary)
    {
        $offset = $binary->consume(4, NBinary::INT_32);
        $size = $binary->consume(4, NBinary::INT_32);

        $current = $binary->current;

        $binary->current = $offset;
        $data = $binary->consume($size, NBinary::BINARY);
        $binary->current = $current;

        return new NBinary($data);
    }

    /**
     * @return File[]
     * @throws Exception
     */
    public function extract()
    {

        $entries = [];
        while ($this->entryCount--) {
            $entry = new File($this->getBlock($this->binary));

            if ($entry->identify() == "afs") {
                $subAfs = new AfsArchive($entry->getContent());
                $subEntries = $subAfs->extract();
                foreach ($subEntries as $subEntry) {
                    $entries[] = $subEntry;
                }

            } else {
                $entries[] = $entry;

            }
        }

        return $entries;
    }


}
