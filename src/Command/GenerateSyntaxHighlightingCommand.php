<?php

namespace App\Command;

use App\MHT;
use App\Service\Archive\Dff;
use App\Service\Archive\Glg;
use App\Service\Archive\Mls;
use App\Service\Archive\ZLib;
use App\Service\Compiler\Compiler;
use App\Service\Compiler\FunctionMap\Manhunt;
use App\Service\Compiler\FunctionMap\Manhunt2;
use App\Service\Compiler\FunctionMap\ManhuntDefault;
use App\Service\Resources;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateSyntaxHighlightingCommand extends Command
{

    protected static $defaultName = 'generate:syntax';

    protected function configure()
    {


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $mh1 = new Manhunt();
        $mh2 = new Manhunt2();
        $mhDefault = new ManhuntDefault();

        $constants = array_merge($mh1::$constants, $mh2::$constants, $mhDefault::$constants);
        $functions = array_merge($mh1::$functions, $mh2::$functions, $mhDefault::$functions);


        $functionOutput = [];
        foreach ($functions as $name => $function) {
            $functionOutput[] = isset($function['name']) ? $function['name'] : $name;
        }

        $constantsOutput = [];
        foreach ($constants as $name => $constant) {
            $constantsOutput[] = $name;
        }



        $template = trim('
<filetype binary="false" description="Manhunt Script" name="Manhunt Script">
  <highlighting>
    <options>
      <option name="LINE_COMMENT" value="" />
      <option name="COMMENT_START" value="{" />
      <option name="COMMENT_END" value="}" />
      <option name="HEX_PREFIX" value="" />
      <option name="NUM_POSTFIXES" value="" />
    </options>
    <keywords keywords="begin;case;else;end.;entity;if;procedure;script;scriptmain;type;var" ignore_case="true">
      <keyword name="end;" />
      <keyword name="forward;" />
    </keywords>
    <keywords2 keywords="' . implode(';', $functionOutput). '" />
    <keywords3 keywords="boolean;entityptr;integer;string;vec3d" />
    <keywords4 keywords="' . implode(';', $constantsOutput). '" />
  </highlighting>
  <extensionMap>
    <mapping ext="srce" />
  </extensionMap>
</filetype>        
        ');

        file_put_contents('mls.xml', $template);


        $template = trim('
{
	"$schema": "https://raw.githubusercontent.com/martinring/tmlanguage/master/tmlanguage.json",
	"name": "MLS (Manhunt Level Script)",
	"patterns": [
		{
			"include": "#keywords"
		},
		{
			"include": "#strings"
		},
		{
			"include": "#comments"
		},
		{
			"include": "#variables"
		},
		{
			"include": "#functions"
		},
		{
			"include": "#keywords2"
		},
		{
			"include": "#scope"
		}
	],
	"repository": {
		"keywords": {
			"patterns": [{
				"name": "keyword.control.srce",
				"match": "(?i)\\b(case|else|entity|if|procedure|script|scriptmain|type|var)\\b"
			}]
		},
		"strings": {
			"name": "string.quoted.double.srce",
			"begin": "\"",
			"end": "\"",
			"patterns": [
				{
					"name": "constant.character.escape.srce",
					"match": "\\\\."
				}
			]
		},
		"comments":{
			"name": "comment",
			"begin": "{",
			"end": "}"
		},
		"variables":{
			"patterns":[
				{
					"name": "variable",
					"match": "(?i)\\b(boolean|entityptr|integer|string|vec3d)\\b"
				}]
		},
		"functions":{
			"patterns": [
				{
					"name": "entity.name.function",
					"match": "(?i)\\b(' . implode('|', $functionOutput). ')\\b"
				}]
		},
		"keywords2":{
			"patterns":[
				{
					"name": "entity.name.type",
					"match": "(?i)\\b(' . implode('|', $constantsOutput). ')\\b"
				}]
		},
		"scope":{
			"patterns":[
				{
					"name": "markup.heading",
					"match": "(?i)\\b(begin|end|forward)\\b"
				}]
		}
	},
	"scopeName": "source.srce"
}        
        
        
        ');

        file_put_contents('srce.tmLanguage.json', $template);


    }
}