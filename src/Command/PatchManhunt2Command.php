<?php

namespace App\Command;

use App\Service\Archive\Manhunt2Exe;
use App\Service\NBinary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PatchManhunt2Command extends Command
{

    protected static $defaultName = 'patch:mh2';

    private $manhunt2Exe;

    public function __construct(Manhunt2Exe $manhunt2Exe)
    {
        $this->manhunt2Exe = $manhunt2Exe;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('exe', InputArgument::REQUIRED, 'manhunt2.exe location')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $exe = realpath($input->getArgument('exe'));

        $binary = new NBinary(file_get_contents($exe));
        $this->manhunt2Exe->binary = $binary;

        list($active, $inactive) = $this->manhunt2Exe->getPatchesStatus();

        if (count($active)){
            $output->write('Active patches: ');

            foreach ($active as $activePatch) {
                $output->write($activePatch . ' ');
            }

            $output->write("\n");
            $output->writeln("==========================");
        }

        // build  question list, add only possible selection
        $questionList = [];
        foreach ($active   as $patchName) { $questionList[] = 'Remove Patch for ' . $patchName; }
        foreach ($inactive as $patchName) { $questionList[] = 'Apply Patch for '  . $patchName; }


        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select a action',
            $questionList
        );
        $question->setErrorMessage('Patch %s is invalid.');

        $patchId = $helper->ask($input, $output, $question);

        $applyOrRemove = substr($patchId, 0, 11) == "Apply Patch";

        // i know... bad hack ...
        $patchName = explode('for ', $patchId)[1];

        $result = $this->manhunt2Exe->patch($patchName, $applyOrRemove);


        switch ($result){
            case Manhunt2Exe::PATCH_NOT_FOUND:
                $output->writeln('Patch not found.');
                break;
            case Manhunt2Exe::ALREADY_ACTIVE:
                $output->writeln('Patch is alread active.');
                break;

            case Manhunt2Exe::OFFSET_WRONG_DATA:
                $output->writeln('Unable to Patch, wrong data received');
                break;

            case Manhunt2Exe::APPLIED:
                file_put_contents($exe, $binary->binary);
                $output->writeln('Successfully!');
                break;
            default:
                $output->writeln(sprintf('Unhandled state %s!', $result));
                break;
        }

    }
}