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

  private int $endTimestamp = 0;

  private int $lastMessageUpdate = 0;

  public function __construct(Hermen $hermen, User $user, int $endTimestamp)
  {
    $this->hermen = $hermen;
    $this->user = $user;
    $this->endTimestamp = $endTimestamp;
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
          $muteMin = 0;
          if($this->voteCountUp >= 15) {
            $muteMin = 20;
          }elseif($this->voteCountUp >= 10) {
            $muteMin = 15;
          } elseif($this->voteCountUp >= 5){
            $muteMin = 10;
          } else {
            $muteMin = 5;
          }

          $time = new Carbon(new \DateTime('+'.$muteMin.' minutes', new \DateTimeZone('Europe/Berlin')));
          $timeout = $member->timeoutMember($time, 'Votemute');
          $timeout->then(function () use ($time, $muteMin){
            $this->getMessage()->reply("Es wurde für einen Mute gestimmt! ".$this->getUser()." ist für ".$muteMin." Minuten gemuted (".$time->format("d.m.Y H:i").")! ");
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

  public function checkEnd(): bool
  {
    if($this->endTimestamp < time()){
      if($this->voteCountDown >= $this->voteCountUp){
        $this->end(false);
      } else {
        if($this->voteCountUp < 3){
          $this->end(false);
          return true;
        }

        $this->end(true);
      }
      return true;
    }
    return false;
  }

  public function isEnd(): bool
  {
    return $this->end;
  }

  public function getEndTimestamp(): int
  {
    return $this->endTimestamp;
  }

  public function getLastMessageUpdate(): int
  {
    return $this->lastMessageUpdate;
  }

  public function setLastMessageUpdate(int $lastMessageUpdate): void
  {
    $this->lastMessageUpdate = $lastMessageUpdate;
  }



}