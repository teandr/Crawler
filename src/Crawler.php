<?php

namespace teandr\Crawler;

use cURL\Error;
use cURL\Event;
use cURL\Request;
use cURL\Response;
use cURL\RequestsQueue;
use voku\helper\HtmlDomParser;

class Crawler implements CrawlerInterface
{
    protected $queue;

    public function __construct()
    {
        $this->queue = new RequestsQueue;

        $this->queue
            ->getDefaultOptions()
            ->set([
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
                CURLOPT_HEADER => false,
                CURLINFO_HEADER_OUT => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

        $this->queue->addListener('complete', function (Event $event) {
            $request  = $event->request;
            $response = $event->response;
            $content  = $response->getContent();
            $info     = $response->getInfo();
            $code     = $info['http_code'];

            if ($code == 200) {
                if (isset($request->data['callback'])) {
                    $callback = $request->data['callback'];

                    $this->$callback($request, $content);
                }
            } else {
                $this->queue->attach($request);
            }
        });

        $this->start();

        while ($this->queue->socketPerform()) {
           usleep(1000);
        }
    }

    public function start() {}
}
