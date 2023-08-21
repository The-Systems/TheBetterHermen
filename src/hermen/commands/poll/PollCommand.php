<?php
namespace hermen\commands\poll;

use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\SelectMenu;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use hermen\commands\CommandsInterface;
use hermen\Hermen;
use PDOException;

class PollCommand implements CommandsInterface
{
  public Hermen $hermen;
  private string $command = "poll";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    $hermen->createCommand($this->command, $this, true);

    $command = $hermen->getDiscordClient()->application->commands->create(CommandBuilder::new()
      ->setName('poll')
      ->setDescription('create a Pool')
      ->addOption((new Option($hermen->getDiscordClient()))
        ->setName('channel')
        ->setDescription('Channel')
        ->setType(Option::CHANNEL)
        ->setRequired(true)
      )
      ->addOption((new Option($hermen->getDiscordClient()))
        ->setName('date')
        ->setDescription('Ende des Polls')
        ->setType(Option::STRING)
        ->setRequired(true)
      )
      ->addOption((new Option($hermen->getDiscordClient()))
        ->setName('question')
        ->setDescription('Frage des Pools')
        ->setType(Option::STRING)
        ->setRequired(true)
      )
      ->addOption((new Option($hermen->getDiscordClient()))
        ->setName('answers')
        ->setDescription('Anworten (Mit ; getrennt)')
        ->setType(Option::STRING)
        ->setRequired(true)
      )
      ->toArray()
    );
    $hermen->getDiscordClient()->application->commands->save($command);

    $this->hermen->discordClient->listenCommand('poll', function (Interaction $interaction) {
      $channel = $interaction->data->resolved->channels->first();
      $date = $interaction->data->options->offsetGet('date')->value;
      $question = $interaction->data->options->offsetGet('question')->value;
      $answers = explode(";", $interaction->data->options->offsetGet('answers')->value);

      $this->createPool($channel, $date, $question, $answers);
    });

  }

  public function createPool(Channel $channel, string $date, string $question, array $answers): void
  {
    $date = strtotime($date);
    if($date == "" OR $date == null){
      throw new PoolCreateException("Date is not valid.");
    }

    $select = SelectMenu::new();
    foreach($answers as $option){
      $select->addOption(\Discord\Builders\Components\Option::new($option));
    }

    $newMessage = $channel->sendMessage(
      MessageBuilder::new()
        ->setEmbeds([['title' => 'POLL', 'description' => $question, 'color' => 65280]])->addComponent($select)
    );
    $newMessage->done(function(Message $messageEvent) use ($question, $select, $date){
      $select->setListener(function (Interaction $interaction, Collection $options) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('thankss!', true));
      }, $this->hermen->getDiscordClient());
    });
  }

  public function getDescription(): string
  {
    return "create a Poll";
  }

  public function getCommand(): string
  {
    return $this->command;
  }


}