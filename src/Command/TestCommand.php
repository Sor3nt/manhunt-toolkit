<?php

namespace App\Command;

use App\Service\Archive\Mls;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TestCommand extends Command
{

    /** @var Mls */
    private $mls;


    public function __construct(Mls $mls)
    {
        $this->mls = $mls;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('test')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $exe = bin2hex(file_get_contents("../Manhunt2.exe"));
//        $start = $exe;

        $data = substr($exe, strpos($exe, "Z2HM"));

        $pos = 2;
        do{
            $start = substr($data, 0, $pos);

            try {
                var_dump(zlib_decode(hex2bin($start)));
                exit;

            }catch(\Exception $e){
            }

            $pos += 2;

        }while(strlen($start));



    }
}