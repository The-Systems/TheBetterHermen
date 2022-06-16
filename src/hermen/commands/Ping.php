<?php
namespace hermen\commands;

use hermen\Hermen;
use Discord\Parts\Channel\Message;
use Bitty\EventManager\EventInterface;

class Ping extends Commands
{
  public Hermen $hermen;
  private string $command = "ping";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    parent::__construct($hermen);
    parent::createCommand($this->command, $this);
  }

  public function runCommand(Message $message){
    $message->reply("Pong!");
  }

}