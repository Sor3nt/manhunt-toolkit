<?php

$cmd = sprintf(
    "%s %s%s%s",
    PHP_BINARY,
    __DIR__,
    DIRECTORY_SEPARATOR,
    'mht.php $@'
);

if(strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0){

    system(sprintf("setx path \"%path%;%s\"", __DIR__));
}else{
    file_put_contents("/usr/local/bin/mht", $cmd);
    chmod("/usr/local/bin/mht", 0777);

}

