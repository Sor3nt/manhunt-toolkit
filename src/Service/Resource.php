<?php

namespace App\Service;

class Resource
{

    private $binary = '';


    private $content = '';
    private $type = '';
    private $relativeFile = '';

    public function __construct( $content, $type, $relativeFile, $binary)
    {
        $this->binary = $binary;
        $this->content = $content;
        $this->type = strtolower($type);
        $this->relativeFile = $relativeFile;
    }

    /**
     * @return mixed
     */
    public function getContent(){
        return $this->content;
    }

    public function setContent( $content ){
        $this->content = $content;
    }


    /**
     * @return string
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * @param string $binary
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


}