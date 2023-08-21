<?php

namespace hermen\commands\voteMute;
use Carbon\Carbon;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use hermen\Hermen;

class VoteMute
{

  private int $voteCountUp = 0;

  private int $voteCountDown = 0;
  private array $votes = [];

  private bool $end = false;

  private Message $message;
  private Hermen $hermen;
  private User $user;

  public function __construct(Hermen $hermen, User $user)
  {
    $this->hermen = $hermen;
    $this->user = $user;
  }

  public function setMessage(Message $message): void
  {
    $this->message = $message;
  }

  public function addMute(User $user): void
  {
    if($this->end){
      return;
    }
    if(in_array($user->id, $this->votes)){
      return;
    }
    $this->votes[] = $user->id;

    $this->voteCountUp++;

    if($this->voteCountUp >= 5){
      $this->end(true);
    }
  }

  public function addNoMute(User $user): void
  {
    if($this->end){
      return;
    }
    if(in_array($user->id, $this->votes)){
      return;
    }
    $this->votes[] = $user->id;


    $this->voteCountDown++;

    if($this->voteCountDown >= 5){
      $this->end(false);
    }
  }

  public function getVoteCountUp(): int
  {
    return $this->voteCountUp;
  }

  public function getVoteCountDown(): int
  {
    return $this->voteCountDown;
  }

  public function getMessage(): Message
  {
    return $this->message;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  private function end(bool $state): void
  {
    $this->end = true;
    if($state){
      try {
        $guild = $this->getMessage()->guild;

        $guild->members->fetch($this->getUser()->id)->done(function (Member $member) {
          $time = new Carbon(new \DateTime('+10 minutes', new \DateTimeZone('Europe/Berlin')));
          $timeout = $member->timeoutMember($time, 'Votemute');
          $timeout->then(function () use ($time){
            $this->getMessage()->reply("Es wurde für einen Mute gestimmt! ".$this->getUser()." ist für 10 Minuten gemuted (".$time->format("d.m.Y H:i").")! ");
          });
          $timeout->otherwise(function () {
            $this->getMessage()->reply("Es wurde für einen Mute gestimmt! ".$this->getUser()." konnte nicht gemuted werden! ");
          });
        });
      } catch (NoPermissionsException|\Exception $e) {
        $this->getMessage()->reply("Ein Fehler ist aufgetreten: ".$e->getMessage());
      }
    } else {
      $this->getMessage()->reply("Es wurde gegen einen Mute gestimmt!");
    }
  }

}