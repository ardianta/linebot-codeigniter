<?php defined('BASEPATH') or exit('No direct script access allowed');

// SDK For build bot
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;

// SDK for build message
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Webhook extends CI_Controller
{
    private $bot;
    private $events;
    private $signature;
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('tebakkode_m');

        // create bot object
        $httpClient = new CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
        $this->bot  = new LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "Hello Coders!";
            header('HTTP/1.1 400 Only POST method allowed');
            exit;
        }

    // get request
    $body = file_get_contents('php://input');
        $this->signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : "-";
        $this->events = json_decode($body, true);

    // log every event requests);
    $this->tebakkode_m->log_events($this->signature, $body);


        foreach ($this->events['events'] as $event) {

           // skip group and room event
           if (! isset($event['source']['userId'])) {
               continue;
           }

           // get user data from database
           $this->user = $this->tebakkode_m->getUser($event['source']['userId']);

           // respond event
           if ($event['type'] == 'message') {
               if (method_exists($this, $event['message']['type'].'Message')) {
                   $this->{$event['message']['type'].'Message'}($event);
               }
           } else {
               if (method_exists($this, $event['type'].'Callback')) {
                   $this->{$event['type'].'Callback'}($event);
               }
           }
        }

    }


    private function followCallback($event)
    {

          $res = $this->bot->getProfile($event['source']['userId']);

          if ($res->isSucceeded())
          {

              $profile = $res->getJSONDecodedBody();
              // save user data
              $this->tebakkode_m->saveUser($profile);

                // send welcome message
                $message = "Salam kenal, " . $profile['displayName'] . "!\n";
                $message .= "Silakan kirim pesan \"MULAI\" untuk memulai kuis.";
                $textMessageBuilder = new TextMessageBuilder($message);
                $this->bot->pushMessage($event['source']['userId'], $textMessageBuilder);
          }

      }
}
