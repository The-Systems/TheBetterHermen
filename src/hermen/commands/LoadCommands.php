<?php

namespace hermen\commands;

use hermen\commands\hosmatic\Profil;
use hermen\commands\voteMute\VoteMuteCommand;
use hermen\Hermen;

class LoadCommands
{
  public function __construct(Hermen $hermen){
    new Ping($hermen);
    new Info($hermen);
    //new Poll($hermen);
    new VoteMuteCommand($hermen);

    new Profil($hermen);
  }
}