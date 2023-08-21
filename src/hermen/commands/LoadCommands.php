<?php

namespace hermen\commands;

use hermen\commands\hosmatic\Profil;
use hermen\commands\poll\PollCommand;
use hermen\commands\voteMute\VoteMuteCommand;
use hermen\Hermen;

class LoadCommands
{
  public function __construct(Hermen $hermen){
    new Ping($hermen);
    new Info($hermen);
    new PollCommand($hermen);
    new VoteMuteCommand($hermen);
    new HelpCommand($hermen);

    new Profil($hermen);
  }
}