<?php
ini_set('memory_limit','-1');

require_once __DIR__ . '/Application/vendor/autoload.php';

$options = [];
foreach ($argv as $index => $argument) {
    if (substr($argument, 0, 2) == "--"){
        $options[] = substr($argument, 2);
        unset($argv[$index]);
    }
}

if (in_array('no-header', $options) === false){
    echo "\n";
    echo "███╗   ███╗██╗  ██╗████████╗    ██╗   ██╗   ██████╗    █████╗ \n";
    echo "████╗ ████║██║  ██║╚══██╔══╝    ██║   ██║  ██╔═████╗  ██╔══██╗\n";
    echo "██╔████╔██║███████║   ██║       ██║   ██║  ██║██╔██║  ╚██████║\n";
    echo "██║╚██╔╝██║██╔══██║   ██║       ╚██╗ ██╔╝  ████╔╝██║   ╚═══██║\n";
    echo "██║ ╚═╝ ██║██║  ██║   ██║        ╚████╔╝██╗╚██████╔╝██╗█████╔╝\n";
    echo "╚═╝     ╚═╝╚═╝  ╚═╝   ╚═╝         ╚═══╝ ╚═╝ ╚═════╝ ╚═╝╚════╝ C\n";
    echo "\t\t\tCoded by Sor3nt | dixmor-hospital.com\n";
    echo "A free and open source toolkit to quickly modify Rockstar`s game Manhunt. \n";

}

if (isset($argv[1])){
    switch (strtolower($argv[1])){

        case 'unpack':
        case 'extract':
            echo "\n";
            include __DIR__ . '/Application/App/Commands/unpack.php';
            break;

        case 'pack':
        case 'build':
            echo "\n";
            include __DIR__ . '/Application/App/Commands/pack.php';

            break;
        case 'patch':
            echo "Legend: U=unpack B=build S=skip R=replace A=append\n";
            echo "\n";
            include __DIR__ . '/Application/App/Commands/patch.php';

            break;
        case 'compare':
            echo "\n";
            include __DIR__ . '/Application/App/Commands/compare.php';

            break;

        default:
            help();
            break;
    }

}else{
    help();
}

function help(){
    echo "Usage: mht <command>\n";
    echo "-------\n";
    echo "unpack [extract]\tExtract a Manhunt File\n";
    echo "pack [build]\t\tBuild a Manhunt File\n";
    echo "compare\t\t\tCompares 2 Folder, generate CSVs\n";
    echo "\n";
    echo "Call a command for future help";
    echo "\n";

    echo "Example: mht unpack\n";
    exit;
}
