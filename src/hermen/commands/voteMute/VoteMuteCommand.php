<?php

namespace hermen\commands\voteMute;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\User;
use hermen\commands\Commands;
use hermen\Hermen;

class VoteMuteCommand extends Commands
{
  public Hermen $hermen;
  private string $command = "votemute";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    parent::__construct($hermen);
    parent::createCommand($this->command, $this, true);

    $this->hermen->discordClient->listenCommand('votemute', function (Interaction $interaction) {
      $user = $interaction->data->resolved->users->first();
      $this->createVoteMute($interaction->channel, $user);
      $interaction->respondWithMessage(MessageBuilder::new()->setContent("Votemute erstellt"), true);
    });
  }

  public function runCommand(Message $message): void
  {

  }

  public function createVoteMute(Channel $channel, User $user): void
  {

    $voteMute = new VoteMute($this->hermen, $user);

    $build = MessageBuilder::new()->setEmbeds([['title' => 'Votemute', 'description' => 'Vote für einen Mute für '.$user, 'color' => 65280]]);
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
      $message = $channel->sendMessage($build);
      $message->then(function(Message $message) use ($voteMute){
        $voteMute->setMessage($message);
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

    $voteMute->getMessage()->edit(MessageBuilder::new()->setEmbeds(
      [
        [
          'title' => 'Votemute',
          'description' => 'Vote für einen Mute für '.$voteMute->getUser(),
          'color' => 65280,
          'fields' => [[
            'name' => 'Mute', 'value' => $voteMute->getVoteCountUp()],
            ['name' => 'Kein Mute', 'value' => $voteMute->getVoteCountDown()]
          ]
        ]
      ]
    ));
  }

}