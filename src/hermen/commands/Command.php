<?php

namespace hermen\commands;

class Command
{
  private CommandsInterface $command;
  private bool $slash;

  public function __construct(CommandsInterface $command, bool $slash = false){
    $this->command = $command;
    $this->slash = $slash;
  }

  public function getCommand(): CommandsInterface
  {
    return $this->command;
  }

  public function isSlash(): bool
  {
    return $this->slash;
  }
}