<?php

namespace App;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    const TARGET_URL = 'https://www.magpiehq.com/developer-challenge/smartphones';

    public function run(): void
    {
        // $hrt = hrtime(true);
        $document = ScrapeHelper::fetchDocument($this::TARGET_URL);
        // echo "===== 1st FETCH =====" . "\r\n ";
        // echo hrtime(true) - $hrt . "\r\n ";
        $this->products = ScrapeHelper::fetchContent($document);

        file_put_contents('output.json', json_encode($this->products));
    }
}

$scrape = new Scrape();
$scrape->run();
