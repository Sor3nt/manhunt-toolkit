<?php

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
    echo "Legend: U=unpack B=build S=skip R=replace A=append\n";
    echo "\n";

}



switch (strtolower($argv[1])){

    case 'unpack':
    case 'extract':
        $cmd = sprintf(
            "%s %s%sApp%sCommands%s%s",
            PHP_BINARY,
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            'unpack.php'
        );
        break;

    case 'pack':
    case 'build':
        $cmd = sprintf("%s %s%s%s", PHP_BINARY, __DIR__, DIRECTORY_SEPARATOR, 'pack.php' );
        break;

    default:
        die("command not implemented yet");
        break;
}

foreach ($argv as $index => $argument) {
    if ($index <= 1) continue;

    $cmd .= " " . $argument;
}

system($cmd);
