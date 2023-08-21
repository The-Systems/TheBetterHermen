<?php

namespace hermen\commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use hermen\Hermen;

class SayCommand implements CommandsInterface
{
  public Hermen $hermen;
  private string $command = "say";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    $hermen->createCommand($this->command, $this, true);

    $command = $hermen->getDiscordClient()->application->commands->create(CommandBuilder::new()
      ->setName('say')
      ->setDescription('Sprich wie ein Bot')
      ->addOption((new Option($hermen->getDiscordClient()))
        ->setName('text')
        ->setDescription('Text')
        ->setType(Option::STRING)
        ->setRequired(true)
      )->toArray()
    );
    $hermen->getDiscordClient()->application->commands->save($command);

    $this->hermen->discordClient->listenCommand('say', function (Interaction $interaction) {
      $interaction->respondWithMessage(MessageBuilder::new()->setContent($interaction->data->options->first()->value));
    });

  }

  public function getDescription(): string
  {
    return "Sei ein Bot!";
  }

  public function getCommand(): string
  {
    return $this->command;
  }

}