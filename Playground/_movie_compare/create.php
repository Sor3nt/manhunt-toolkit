<?php

require_once '../vendor/autoload.php';

use Symfony\Component\Finder\Finder;

list($script, $folder) = $argv;
$folder = realpath($folder);

$finder = new Finder();
$finder
    ->name('/.PMF/i')
    ->name('/.PSS/i')
    ->name('/.bik/i')
    ->files()
    ->in($folder);

$videos = [];
foreach ($finder as $file) {
    $target = str_replace(".PMF", ".mp4", $file->getRealPath());
    $target = str_replace(".PSS", ".mp4", $target);

    $isBik = strpos($target, '.bik') !== false;
    $target = str_replace(".bik", ".mp4", $target);
    $platform = $file->getPathInfo()->getFilename();
    $level = strtolower(substr($file->getFilenameWithoutExtension(), 0, 3));

    $stage = "unk";
    if (strpos($file->getFilenameWithoutExtension(), '_I') !== false) $stage = "intro";
    if (strpos($file->getFilenameWithoutExtension(), '_O') !== false) $stage = "outro";

    $videos[] = [
        'platform' => $platform,
        'level' => $level . '_' . $stage,
        'video' => $target
    ];

    if (file_exists($target)){
        unlink($target);
    }
        echo "Convert "  . $file->getFilename() . "\n";
        if ($isBik){
            system("ffmpeg -i \"" . $file->getRealPath() . "\" -c copy -an \"" .$target . "\"");

        }else{
            system("ffmpeg -i \"" . $file->getRealPath() . "\" -c copy \"" .$target . "\"");

        }
   // }
}

//$levelGroup = \App\Service\Helper::groupBy('level', $videos);

//foreach (['a01','a02','a03','a04','a06','a07','a09','a10','a11','a12','a14','a15','a16','a17', 'a18'] as $levelLook) {
//
//}
//foreach ($videos as $platform => $level) {
//
//}
/*
var_dump($levelGroup);

ffmpeg -i A18_MA_I_psp100.mp4 -c:v libx264 -crf 18 -preset slow -c:a copy -an A18_MA_I_psp100_en.mp4

ffmpeg -i A18_Manor_I_wii.bik -c:a copy -s 480x272 A18_Manor_I_wii_re.mp4


ffmpeg -ss 00:00:00.2 -i A18_MA_I_psp100_en.mp4 -c copy  A18_MA_I_psp100_en_test.mp4


ffmpeg -i a18_final.mp4 -i a18_i.mp3 -c:v copy -c:a aac a18.mp4

create 6x6 video
ffmpeg -i A18_MA_O_ps2leak_re.mp4 -i A18_MA_O_ps2unk_re.mp4  -filter_complex hstack hstack1.mp4
ffmpeg -i hstack1.mp4 -i A18_MA_O_psp100.mp4  -filter_complex hstack hstack2.mp4


ffmpeg -i A18_MA_O_psp.mp4 -i A18_MA_O_psp100.mp4  -filter_complex hstack hstack3.mp4
ffmpeg -i hstack3.mp4 -i A18_Manor_O_pc_re.mp4  -filter_complex hstack hstack4.mp4

ffmpeg -i hstack2.mp4 -i hstack4.mp4  -filter_complex vstack a18_final.mp4


create 4x4 video
ffmpeg -i A18_MA_I_ps2leak_re.mp4 -i A18_MA_I_ps2unk_re.mp4  -filter_complex hstack hstack1.mp4
ffmpeg -i A18_MA_I_psp100_en_test.mp4 -i A18_Manor_I_wii_re.mp4  -filter_complex hstack hstack2.mp4
ffmpeg -i hstack1.mp4 -i hstack2.mp4  -filter_complex vstack a18_final.mp4

A18_MA_I_ps2leak.mp4
A18_MA_I_ps2unk.mp4
A18_MA_I_psp100.mp4
A18_Manor_I_wii.bik


ffmpeg -i A18_MA_O_ps2leak_re.mp4 -vn -acodec copy audio.aac
*/