<?php

namespace hermen\commands;

use hermen\Hermen;

class LoadCommands
{
  public function __construct(Hermen $hermen){
    new Ping($hermen);
    new Info($hermen);
    new Poll($hermen);
  }
}