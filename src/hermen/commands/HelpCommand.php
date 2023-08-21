<?php

namespace hermen\commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use hermen\Hermen;

class HelpCommand implements CommandsInterface
{
  public Hermen $hermen;
  private string $command = "help";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    $hermen->createCommand($this->command, $this, true);

    $command = $hermen->getDiscordClient()->application->commands->create(CommandBuilder::new()
      ->setName('help')
      ->setDescription('Hilfe')
      ->toArray()
    );
    $hermen->getDiscordClient()->application->commands->save($command);

    $this->hermen->discordClient->listenCommand('help', function (Interaction $interaction) {
      $commands = [];

      foreach ($this->hermen->commands as $command) {
        if(!$command instanceof Command){
          continue;
        }

        $commands[] = ['name' => $command->getCommand()->getCommand(), 'value' => $command->getCommand()->getDescription()];
      }


      $interaction->respondWithMessage(MessageBuilder::new()->setEmbeds([['title' => 'Hilfe', 'description' => "Alle Befehle", "fields" => $commands, 'color' => 65280,],]));
    });
  }

  public function getDescription(): string
  {
    return "Hilfe";
  }

  public function getCommand(): string
  {
    return $this->command;
  }
}