<?php
namespace App\Service;

class File{

    /** @var NBinary */
    private $binary;

    private $fourCC;

    public function __construct(NBinary $binary)
    {
        $this->binary = $binary;
        $this->fourCC = trim($this->binary->getFromPos(0, 4, NBinary::BINARY));
        $this->binary->current = 0;
    }

    public function identify()
    {
        switch (true){
            case $this->binary->getFromPos(0, 3, NBinary::BINARY) == "AIX": return "aix"; break;
            case $this->binary->getFromPos(0, 4, NBinary::BINARY) == "2AGs": return "2ags.vas"; break;
            case $this->binary->getFromPos(0, 4, NBinary::BINARY) == "VAGs": return "vags.vas"; break;

            //AFS Container
            case $this->binary->getFromPos(0, 3, NBinary::BINARY) == "AFS": return "afs"; break;

            //ADX / AHX Audio file
            case $this->binary->getFromPos(0, 2, NBinary::BINARY) == "\x80\00":
                $code = (int)$this->binary->getFromPos(4, 1, NBinary::HEX);
                if ($code == 3) return 'adx';
                if ($code == 11) return 'ahx';

                return "unk";

            //Audio context_map.bin
            case $this->binary->length() == 264: return "context_map"; break;

            //hash audio name list (from the afs container)
            case $this->binary->getFromPos(0, 4, NBinary::BINARY) == "scri": return "hash_name_list"; break;

            default:
                return "unk";
        }

    }

    /**
     * @return NBinary
     */
    public function getContent()
    {
        $this->binary->current = 0;
        return $this->binary;
    }

}