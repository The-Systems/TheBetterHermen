<?php
namespace hermen\commands;

use hermen\Hermen;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Bitty\EventManager\EventInterface;
use Discord\Http\Exceptions\NoPermissionsException;

class Info extends Commands
{
  public Hermen $hermen;
  private string $command = "info";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    parent::__construct($hermen);
    parent::createCommand($this->command, $this);
  }

  public function runCommand(Message $message){
    try {
      $message->channel->sendMessage(MessageBuilder::new()->setEmbeds([['title' => 'Info', 'description' => 'This is a test bot.', 'color' => 65280,],]));
    } catch(NoPermissionsException $e) {
      echo "No permissions to send messages in this channel. ".$e->getMessage();
    }
  }

}