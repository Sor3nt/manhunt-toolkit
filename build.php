<?php

$pharFile = 'mht.phar';

// clean up
if (file_exists($pharFile))
{
    unlink($pharFile);
}

if (file_exists($pharFile . '.gz'))
{
    unlink($pharFile . '.gz');
}

rename('Application/App/Tests', 'Tests');

// create phar
$phar = new Phar($pharFile);

echo "startBuffering\n";
// start buffering. Mandatory to modify stub to add shebang
$phar->startBuffering();

echo "createDefaultStub\n";
// Create the default stub from main.php entrypoint
$defaultStub = $phar->createDefaultStub('mht.php');

// Add the rest of the apps files
echo "buildFromDirectory\n";
$phar->buildFromDirectory(__DIR__ . '/Application');

// Customize the stub to add the shebang
$stub = "#!/usr/bin/php \n" . $defaultStub;

// Add the stub
$phar->setStub($stub);

$phar->stopBuffering();

echo "compressFiles\n";
// plus - compressing it into gzip
$phar->compressFiles(Phar::GZ);

# Make the file executable
chmod(__DIR__ . '/' . $pharFile, 0770);

rename('Tests', 'Application/App/Tests');

echo "$pharFile successfully created" . PHP_EOL;