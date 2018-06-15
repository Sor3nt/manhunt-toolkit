<?php

namespace App\Command;

use App\Service\Archive\Fsb;
use App\Service\Archive\Glg;
use App\Service\Archive\Inst;
use App\Service\Archive\Mls;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ExportProjectManhuntForum extends Command
{


    private $base = "http://www.projectmanhunt.com/forums/viewforum.php?f=";


    protected function configure()
    {
        $this
            ->setName('export:pmh')
            ->setDescription('Export the Project Manhunt Forum')
            ->addArgument('forumId', InputArgument::REQUIRED, 'Forum ID (f id)')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $forumId = $input->getArgument('forumId');
        $file = "Forum." . $forumId . ".json";
        $folder = __DIR__ . '/../../ProjectManhunt/';

        if (!file_exists($folder . $file)){
            $pages = $this->getResultPage( $this->base . $forumId);

            file_put_contents($folder . $file, json_encode($pages));
        }else{
            $pages = \json_decode(file_get_contents($folder . $file), true);
        }

        $postFolder = $folder . 'Forum.' . $forumId . '.posts/';
        @mkdir($postFolder, 0777, true);

        foreach ($pages as $page) {


            $id = explode('t=', $page['link'])[1];
            $id = explode('&', $id)[0];

            if (!file_exists($postFolder . $id . '.json')){
                echo "Crawl: " . $page['title'] . "... ";

                $postLink =  'http://www.projectmanhunt.com/forums' . html_entity_decode($page['link']);
                $posts = $this->getPosts( $postLink );
                file_put_contents($postFolder . $id . '.json', \json_encode( $posts ));

                echo count($posts) . " posts saved\n";
            }else{
                echo "Skip: " . $page['title'] . "\n";

            }
        }

    }

    private function getPosts( $url ){
        $postContent = file_get_contents($url);

        $paginationPageCount = explode(" of <strong>", $postContent)[1];
        $paginationPageCount = explode("<", $paginationPageCount)[0];

        $offset = ($paginationPageCount * 15) - 15;

        $posts = [];
        for($i = 0; $i <= $offset; $i = $i + 15){

            $postContent = file_get_contents($url . '&st=0&sk=t&sd=a&start=' . $i);

            $content = explode("class=\"tablebg\"", $postContent);


            foreach ($content as $post) {
                if (strpos($post, '<b class="postauthor">') === false) continue;


                $author = explode("<b class=\"postauthor\">", $post)[1];
                $author = explode("<", $author)[0];

                $date = explode("<b>Posted:</b> ", $post)[1];
                $date = explode("&nbsp;", $date)[0];

                $post = explode("<div class=\"postbody\">", $post)[1];
                $post = explode("</div>\n", $post)[0];

                $posts[] = [
                    'author' => $author,
                    'date' => $date,
                    'post' => $post
                ];
            }
        }

        return $posts;
    }

    private function getResultPage( $url){

        $content = file_get_contents($url);

        $paginationPageCount = explode(" of <strong>", $content)[1];
        $paginationPageCount = explode("<", $paginationPageCount)[0];

        $offset = ($paginationPageCount * 50) - 50;
        $entries = [];

        for($i = 0; $i <= $offset; $i = $i + 50){

            echo "fetching " . $i . "\n";

            $content = file_get_contents($url . '&start=' . $i);

            $links = explode('<td class="row1" width="25"', $content);
            unset($links[0]);


            foreach ($links as $link) {

                $postetAt = explode("<a title=\"Posted: ", $link)[1];
                $postetAt = explode("\"", $postetAt)[0];

                $href = explode("href=\"./viewtopic.php", $link)[1];
                $href = '/viewtopic.php' . explode("\"", $href)[0];

                $title = explode("class=\"topictitle\">", $link)[1];
                $title = explode("<", $title)[0];

                $author = explode("<a href=\"./memberlist.php?", $link)[1];
                $author = explode(">", $author)[1];
                $author = explode("<", $author)[0];

                $entries[] = [
                    'date' => $postetAt,
                    'link' => $href,
                    'title' => $title,
                    'author' => $author
                ];
            }
        }

        return $entries;
    }
}