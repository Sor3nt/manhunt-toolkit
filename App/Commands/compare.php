<?php

$options = [];
$folders = [];
foreach ($argv as $index => $argument) {
    if ($index === 0) continue;

    if (substr($argument, 0, 2) == "--"){
        $options[] = substr($argument, 2);
        unset($argv[$index]);
    }else{
        $folders[] = $argument;
    }
}

$data = [];
echo sprintf("Prepare hashes for %s folders\n", count($folders));

foreach ($folders as $folderIndex => $folder) {

    echo sprintf("Reading dir %s ... ", $folder);

    $data[$folderIndex] = [];

    $finder = new \Symfony\Component\Finder\Finder();
    $finder->files()->in($folder);

    foreach ($finder as $file) {

        $hash = md5($file->getContents());


        $pathname = explode("/", $file->getPathname());
        unset($pathname[0]);
        $pathname = implode("/", $pathname);

        $data[$folderIndex][] = [
            'hash' => $hash,
            'pathname' => $pathname,
            'filename' => $file->getFilename(),
            'realPathname' => $file->getPathname()
        ];
    }

    echo sprintf("%s Files\n", count($data[$folderIndex]));
}

$outputs = [];
foreach ($data as $folderIndex => $entries) {

    $headerLine = [$folders[$folderIndex]];

    foreach ($data as $innerFolderIndex => $innerEntries) {
        if ($innerFolderIndex === $folderIndex) continue;

        $headerLine[] = $folders[$innerFolderIndex];
    }

    $outputs[$folderIndex] = [$headerLine];
}

foreach ($data as $folderIndex => $entries) {


    foreach ($entries as $entry) {

        $line = [$entry['realPathname']];

        foreach ($data as $innerFolderIndex => $innerEntries) {
            if ($innerFolderIndex === $folderIndex) continue;

            $scan = scanFolderForEntryHasChanges($entry, $innerEntries);

            if ($scan === null){
                $line[] = "N/A";
            }else if ($scan === true){
                $line[] = "DIFF";
            }else if ($scan === false){
                $line[] = "SAME";

            }

        }

        $outputs[$folderIndex][] = $line;
    }
}


foreach ($outputs as $index => $lines) {


    $fp = fopen('cmp' . $index . '.csv', 'w');

    foreach ($lines as $line) {
        fputcsv($fp, $line);
    }

    fclose($fp);
}

//var_dump($outputs);

function scanFolderForEntryHasChanges($searchEntry, $entries){

    foreach ($entries as $entry) {

        //we found exact these file
        if (
            $searchEntry['pathname'] == $entry['pathname'] &&
            $searchEntry['filename'] == $entry['filename']
        ){

            //its the same
            if ($searchEntry['hash'] == $entry['hash']){
                return false;
            }else{
                var_dump($searchEntry, $entry, "\n\n");
                return true;
            }
            break;
        }


    }

    return null;
}
