<?php
include __DIR__.'/vendor/autoload.php';

use Discord\DiscordCommandClient;
use hermen\Hermen;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

$hermen = new Hermen();

try {
  $discord = new DiscordCommandClient(['token' => $hermen->getConfig()['token']]);
} catch (\Discord\Exceptions\IntentException $e) {
  die($e->getMessage());
} catch (Exception $e) {
  die($e->getMessage());
}


$hermen->setClients($discord);
