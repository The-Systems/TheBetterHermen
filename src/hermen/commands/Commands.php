<?php

namespace hermen\commands;

use hermen\Hermen;

abstract class Commands
{

  public Hermen $hermen;
  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;
  }

  protected function createCommand(string $command, $class): void
  {
    $this->hermen->commands[$command] = $class;
  }


}

