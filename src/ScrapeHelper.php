<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    const BASE_PRODUCT_IMAGE_URL = 'https://www.magpiehq.com/developer-challenge';

    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    public static function fetchContent(Crawler $crawler)
    {
        $timer = 0;
        $pages = self::getPages($crawler);
        $products = [];
        $productTitles = [];
        foreach($pages as $page) {
            $pageContent = self::fetchDocument($page);
            $pageContent->filter('#products .flex-wrap .product')
            ->each(function (Crawler $node, $i) use (&$products, &$productTitles, &$timer) {
                $titleText = $node->filter('.product-name')->first()->text();
                $capacityText = $node->filter('.product-capacity')->first()->text();
                $product['title'] = $titleText.' '.$capacityText;
                $product['capacityMB'] = self::parseCapacity($capacityText);
                $filterTime = hrtime(true);
                if (in_array($product['title'], $productTitles)) {
                    return;
                }
                $timer += hrtime(true) - $filterTime;
                $capacityMBText = $node->filter('.product-capacity')->first()->text();

                // get url image
                $imageUrlNode = $node->filter('img')->first();
                if ($imageUrlNode->count()) {
                    $product['imageUrl'] = str_replace('..', self::BASE_PRODUCT_IMAGE_URL, $imageUrlNode->attr('src'));
                }

                // get colour
                $colourNode = $node->filter('.border.border-black.rounded-full')->each(function (Crawler $colour){
                    return $colour->first()->attr('data-colour');
                });
                if (count($colourNode)) {
                    $product['colour'] = strtolower(implode(', ',$colourNode));
                }

                // product att
                $productAttrNodes = $node->filter('.rounded-md')->children();
                // price
                $priceText = $productAttrNodes->eq(3)->text();
                $product['price'] = (float) trim($priceText, '£');
                // stock
                $availabilityText = $productAttrNodes->eq(4)->text();
                $product['availabilityText'] = str_replace('Availability: ', '', $availabilityText);
                if ($product['availabilityText'] === 'Out of Stock') {
                    $product['isAvailable'] = false;
                } else {
                    $product['isAvailable'] = true;
                }

                // shippingText
                $shippingNode = $productAttrNodes->eq(5);
                if ($shippingNode->count()) {
                    $product['shippingText'] = $shippingNode->text();

                    self::parseDate($product['shippingText']);
                    $product['shippingDate'] = self::parseDate($product['shippingText']);
                }

                $productTitles[] = $product['title'];
                $productObj = new Product($product);
                $products[] = $productObj;
                return $productObj;
            });
        }
        // $uniqueTime = hrtime(true);
        // $products = array_unique($products);
        // $timer += hrtime(true) - $uniqueTime;
        echo "===== REMOVE DUP TIMER =====" . "\r\n ";
        echo $timer . "\r\n ";
        return $products;
    }

    private static function getPages(Crawler $crawler): array
    {
        $result = $crawler->filter('#pages > div > a')->each( function (Crawler $node, $i) use($crawler) {
            $href = $node->attr('href');
            return $crawler->getUri() . substr($href, strpos($href, '?page='));
        });
        return $result;
    }

    private static function parseCapacity(string $title)
    {
        preg_match('/(\d+)\s?(GB|MB)$/', $title,$m);
        if (count($m)) {
            if ($m[2] == 'GB') {
                return $m[1]*1024;
            }
            if ($m[2] == 'MB') {
                return $m[1];
            }
            return null;
        }
        return null;
    }

    private static function parseDate(string $shippingText)
    {
        // pattern 1
        preg_match('/(\d{1,2}) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (\d{4})$/', $shippingText,$m);
        if (count($m)) {
            $date = date_create_from_format('d M Y', $m[1].' '.$m[2].' '.$m[3]);
            return date_format($date, 'Y-m-d');
        }

        // pattern 2
        preg_match('/(\d{1,2}th) (January|February|March|April|May|June|July|August|September|October|November|December)$/', $shippingText,$m);
        if (count($m)) {
            $date = date_create_from_format('d F Y', $m[1].' '.$m[2].' '.date('Y'));
            return date_format($date, 'Y-m-d');
        }

        // pattern 3
        preg_match('/(\d{1,2})th (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (\d{4})$/', $shippingText,$m);
        if (count($m)) {
            $date = date_create_from_format('d M Y', $m[1].' '.$m[2].' '.$m[3]);
            return date_format($date, 'Y-m-d');
        }

        return null;
    }
}
