<?php
namespace hermen\commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use hermen\Hermen;
use Discord\Parts\Channel\Message;

class Ping implements CommandsInterface
{
  public Hermen $hermen;
  private string $command = "ping";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    $hermen->createCommand($this->command, $this, true);

    $command = $hermen->getDiscordClient()->application->commands->create(CommandBuilder::new()
      ->setName('ping')
      ->setDescription('Pong')
      ->toArray()
    );
    $hermen->getDiscordClient()->application->commands->save($command);

    $this->hermen->discordClient->listenCommand('ping', function (Interaction $interaction) {
      $interaction->respondWithMessage(MessageBuilder::new()->setContent("Pong"));
    });

  }

  public function getDescription(): string
  {
    return "Ping";
  }

  public function getCommand(): string
  {
    return $this->command;
  }
}