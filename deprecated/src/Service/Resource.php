<?php

namespace App\Service;

use App\Service\Archive\Archive;
use Symfony\Component\Finder\Finder;

class Resource
{

    /** @var Finder|NBinary */
    private $input;

    private $handler = '';
    private $relativeFile = '';

    public function __construct( Archive $handler, $relativeFile, $input)
    {
        $this->input = $input;
        $this->handler = $handler;
        $this->relativeFile = $relativeFile;
    }

    /**
     * @return Archive
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param Archive $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }




    /**
     * @return Finder|NBinary
     */
    public function getInput()
    {
        return $this->input;
    }


    /**
     * @param Finder|NBinary $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }


}