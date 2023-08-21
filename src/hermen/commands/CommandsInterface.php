<?php

namespace hermen\commands;

interface CommandsInterface
{
  public function getDescription(): string;
  public function getCommand(): string;

}