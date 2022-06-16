<?php

namespace hermen\commands;

use hermen\Hermen;
use Discord\Parts\Channel\Message;

class TriggerCommand
{
  public Hermen $hermen;
  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;
  }

  public function trigger(Message $message): void
  {
    $command = explode(" ", $message->content)[0];
    $command = substr($command, 1);

    if(isset($this->hermen->commands[$command])){
      $this->hermen->commands[$command]->runCommand($message);
    }
  }


}