<?php
ini_set('memory_limit','-1');

require_once __DIR__ . '/vendor/autoload.php';

//$a = new \App\Service\CompilerV2\Manhunt();
//
//foreach ($a->constants as $name => $item) {
//        echo $name . "\n";
//}
//exit;
//
//$tmp = new \App\Service\NBinary();
//for($i = 60; $i > 0; $i = $i - 2){
//    $tmp->write($i, \App\Service\NBinary::FLOAT_32 );
//}
//
//$tmp->current = 0;
//for($i = 60; $i > 0; $i = $i - 2){
//    $intVal = $tmp->consume(4, \App\Service\NBinary::INT_32);
//    echo 'WriteMemory(8074056,4,' . $intVal . ');' . " sleep(150);\n";
//
//}
//exit;

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
//


    switch (strtolower($argv[1])){

        case 'patch':
            echo "Legend: U=unpack B=build S=skip R=replace A=append\n";
            echo "\n";
            include __DIR__ . '/App/Commands/patch.php';

            break;
        case 'compare':
            echo "\n";
            include __DIR__ . '/App/Commands/compare.php';

            break;

        case 'mass':
            echo "\n";
            include __DIR__ . '/App/Commands/mass.php';

            break;

        case 'memdump':

            echo "\n";
            include __DIR__ . '/App/Commands/unpack.php';
            break;
        case 'install':
            echo "\n";

            echo "Installing MHT...";
            //Windows
            if(strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0){

                file_put_contents('mht.bat', PHP_BINARY . ' "' . __DIR__ . '\\mht.phar" %1 %2 %3 %4 %5 %6');

                system(sprintf("setx path \"%s;%s\"", "%path%", '\"' . __DIR__ . '\"'));

            //Linux/Mac
            }else{

                copy($argv[0], "/usr/local/bin/mht");
                chmod("/usr/local/bin/mht", 0777);

            }

            echo "OK\n";

            break;

        default:

            if (strpos($argv[1], "#") !== false) {
                array_splice( $argv, 1  , 0, 'pack' );
            }else if (strpos($argv[1], ".") !== false){
                array_splice( $argv, 1  , 0, 'unpack' );
            }

            switch (strtolower($argv[1])) {

                case 'unpack':
                case 'extract':

                    echo "\n";
                    include __DIR__ . '/App/Commands/unpack.php';
                    break;

                case 'pack':
                case 'build':
                    echo "\n";
                    include __DIR__ . '/App/Commands/pack.php';
                    break;
                default:
                    help();
                    break;

            }
            break;
    }

}else{
    help();
}

function help(){
    echo "\n";
    echo "Usage: mht <command>\n";
    echo "-------\n";
    echo "install\t\t\tInstall MHT globally\n";
    echo "unpack [extract]\tExtract a Manhunt File\n";
    echo "pack [build]\t\tBuild a Manhunt File\n";
    echo "compare\t\t\tCompares 2 Folder, generate CSVs\n";
    echo "<file>\t\t\tAutodetection pack / unpack\n";
    echo "\n";
    echo "Call a command for additional help";
    echo "\n";

    echo "Example: mht unpack\n";
    exit;
}
