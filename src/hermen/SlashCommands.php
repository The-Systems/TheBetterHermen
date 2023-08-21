<?php

namespace hermen;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Option;

class SlashCommands
{
  public function __construct(Hermen $hermen)
  {
    $this->voteMute($hermen);
  }

  public function voteMute(Hermen $hermen): void
  {
    $command = $hermen->getDiscordClient()->application->commands->create(CommandBuilder::new()
      ->setName('votemute')
      ->setDescription('Votemute a user')
      ->addOption((new Option($hermen->getDiscordClient()))
        ->setName('user')
        ->setDescription('User to votemute')
        ->setType(Option::USER)
        ->setRequired(true)
      )
      ->toArray()
    );
    $hermen->getDiscordClient()->application->commands->save($command);

  }

}