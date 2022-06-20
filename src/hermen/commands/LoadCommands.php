<?php

namespace hermen\commands;

use hermen\Hermen;
use hermen\commands\hosmatic\Profil;

class LoadCommands
{
  public function __construct(Hermen $hermen){
    new Ping($hermen);
    new Info($hermen);
    new Poll($hermen);

    new Profil($hermen);
  }
}