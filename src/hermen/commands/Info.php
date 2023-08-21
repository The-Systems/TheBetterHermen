<?php
namespace hermen\commands;

use hermen\Hermen;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Discord\Http\Exceptions\NoPermissionsException;

class Info implements CommandsInterface
{
  public Hermen $hermen;
  private string $command = "info";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    $hermen->createCommand($this->command, $this);
  }

  public function runCommand(Message $message){
    try {
      $message->channel->sendMessage(MessageBuilder::new()->setEmbeds([['title' => 'Info', 'description' => 'This is a test bot.', 'color' => 65280,],]));
    } catch(NoPermissionsException $e) {
      echo "No permissions to send messages in this channel. ".$e->getMessage();
    }
  }

  public function getDescription(): string
  {
    return "Info";
  }

  public function getCommand(): string
  {
    return $this->command;
  }
}