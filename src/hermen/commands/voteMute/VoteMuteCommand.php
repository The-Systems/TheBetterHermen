<?php

namespace hermen\commands\voteMute;

use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use hermen\commands\CommandsInterface;
use hermen\Hermen;

class VoteMuteCommand implements CommandsInterface
{
  public Hermen $hermen;
  private string $command = "votemute";

  private int $lastVoteMute = 0;

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    $hermen->createCommand($this->command, $this, true);

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

    $this->hermen->discordClient->listenCommand('votemute', function (Interaction $interaction) {
      if($this->lastVoteMute+600 > time()){
        $interaction->respondWithMessage(MessageBuilder::new()->setContent("Du kannst nur alle 10 Minuten einen Votemute erstellen"), true);
        return;
      } else {
        $this->lastVoteMute = time();
      }

      $user = $interaction->data->resolved->users->first();
      $this->createVoteMute($interaction, $user);
      $interaction->respondWithMessage(MessageBuilder::new()->setContent("Votemute erstellt"), true);
    });
  }

  public function runCommand(Message $message): void
  {

  }

  public function createVoteMute(Interaction $interaction, User $user): void
  {
    $channel = $interaction->channel;
    $voteMute = new VoteMute($this->hermen, $user, time()+180);

    $build = MessageBuilder::new()->setEmbeds([['title' => 'Votemute', 'description' => 'Vote für einen Mute für '.$user .' erstellt von '.$interaction->user, 'color' => 65280]]);
    $action = ActionRow::new();
    $button = Button::new(Button::STYLE_PRIMARY)->setLabel('Mute')->setStyle(Button::STYLE_PRIMARY)->setListener(function (Interaction $interaction) use ($voteMute) {
      $this->onInteraction($interaction, $voteMute, true);
    }, $this->hermen->getDiscordClient());
    $build->addComponent($action->addComponent($button));

    $action = ActionRow::new();
    $button = Button::new(Button::STYLE_PRIMARY)->setLabel('Kein Mute')->setStyle(Button::STYLE_DANGER)->setListener(function (Interaction $interaction) use ($voteMute) {
      $this->onInteraction($interaction, $voteMute, false);
    }, $this->hermen->getDiscordClient());
    $build->addComponent($action->addComponent($button));

    try {
      $hermen = $this->hermen;
      $message = $channel->sendMessage($build);
      $message->then(function(Message $message) use ($voteMute, $hermen){
        $voteMute->setMessage($message);
        $loop = $hermen->getDiscordClient()->getLoop();

        function updateMessage(VoteMute $voteMute): void
        {
          $voteMute->getMessage()->edit(MessageBuilder::new()->setEmbeds(
            [
              [
                'title' => 'Votemute',
                'description' => 'Vote für einen Mute für '.$voteMute->getUser(),
                'color' => 65280,
                'fields' => [
                  ['name' => 'Mute', 'value' => $voteMute->getVoteCountUp(), 'inline' => true],
                  ['name' => 'Kein Mute', 'value' => $voteMute->getVoteCountDown(), 'inline' => true],
                  ['name' => 'Endet in', 'value' => '<t:'.$voteMute->getEndTimestamp().':R>', 'inline' => true]
                ]
              ]
            ]
          ));
        }

        $loop->addPeriodicTimer(1, function ($timer) use ($voteMute, $loop){
          if($voteMute->getLastMessageUpdate() <= time()-5){
            $voteMute->setLastMessageUpdate(time());
            updateMessage($voteMute);
          }

          if($voteMute->checkEnd()) {
            updateMessage($voteMute);
            $loop->cancelTimer($timer);
          }
        });
      });
    } catch(NoPermissionsException $e) {
      echo "No permissions to send messages in this channel. ".$e->getMessage();
    }
  }

  public function onInteraction(Interaction $interaction, VoteMute $voteMute, bool $state): void
  {
    if($state){
      $voteMute->addMute($interaction->user);
    } else {
      $voteMute->addNoMute($interaction->user);
    }
  }

  public function getDescription(): string
  {
    return "Votemute eine Person";
  }

  public function getCommand(): string
  {
    return $this->command;
  }

}