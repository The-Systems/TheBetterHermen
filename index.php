<?php
include __DIR__.'/vendor/autoload.php';

use hermen\Hermen;
use Discord\Discord;
use Discord\Slash\Client;
use Discord\Slash\RegisterClient;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

$hermen = new Hermen();

try {
  $discord = new Discord(['token' => $hermen->getConfig()['token']]);
} catch (\Discord\Exceptions\IntentException $e) {
  die($e->getMessage());
}

$slashClient = new RegisterClient($hermen->getConfig()['token']);

$hermen->setClients($discord, $slashClient);
