<?php

namespace hermen\events;

use hermen\Hermen;
use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\Channel\Message;
use hermen\commands\TriggerCommand;

class Events
{
  public function __construct (Hermen $hermen)
  {
    $hermen->getDiscordClient()->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($hermen) {
      if ($message->author->bot) return;

      if($message->content[0] == $hermen->getConfig()['prefix'] AND isset($message->content[1])){
        (new TriggerCommand($hermen))->trigger($message);
      }
    });
  }
}