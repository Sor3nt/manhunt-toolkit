<?php
namespace App\Tests\CustomFunctions;

use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Compiler\Autocorrection\LineEnd\FunctionCall;
use App\Service\Compiler\Autocorrection\Statements\Short;
use App\Service\Compiler\Compiler;
use App\Service\Compiler\Tokenizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IfAndElseIfStatementLineEndTest extends KernelTestCase
{

    private function prepare($source)
    {
        // remove double whitespaces
        $source = preg_replace("/\s+/", ' ', $source);

        // remove comments / unused code
        $source = preg_replace("/\{.*?\}/m", "", $source);


        // replace line ends with new lines
        $source = preg_replace("/;/", ";\n", $source);

        $source = trim($source);

        if (empty($source)){
            throw new \Exception('Cleanup going wrong, source is empty');
        }

        return $source;
    }

    public function test()
    {

        $source = "
            if ThisLevelBeenCompletedAlready = 0 then
                 CutSceneEnd(FALSE)
            else if ThisLevelBeenCompletedAlready = 0 then
                 CutSceneEnd(FALSE)                 
            else if ThisLevelBeenCompletedAlready = 0 then
                 CutSceneEnd(FALSE)                 
            end.
        ";

        $expected = 'if thislevelbeencompletedalready = 0 then begin ' .
                        'cutsceneend ( false ) ; ' .
                    'end else if thislevelbeencompletedalready = 0 then begin '.
                        'cutsceneend ( false ) ; ' .
                    'end else if thislevelbeencompletedalready = 0 then begin '.
                        'cutsceneend ( false ) ; ' .
                    'end; end. ';

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->run($this->prepare($source));

        $tokens = (new FunctionCall())->autocorrect($tokens);

        $tokens = (new Short())->convertShortToFull($tokens);

        $str = "";
        foreach ($tokens as $token) {
            $str .= $token['value'] . " ";
        }

        $this->assertEquals($str, $expected);
    }

}