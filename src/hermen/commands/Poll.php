<?php
namespace hermen\commands;

use PDOException;
use hermen\Hermen;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Bitty\EventManager\EventInterface;
use Discord\Builders\Components\Option;
use Discord\Builders\Components\SelectMenu;
use Discord\Parts\Interactions\Interaction;
use Discord\Http\Exceptions\NoPermissionsException;

class Poll extends Commands
{
  public Hermen $hermen;
  private string $command = "poll";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    parent::__construct($hermen);
    parent::createCommand($this->command, $this);
  }

  public function runCommand(Message $message){
    try {
      $command = explode(" ", $message->content);

      $channel = $command[1];
      if(!is_numeric($channel)) {
        $channel = substr($command[1], 2, -1);
      }

      $options = explode(";", $command[2]);
      $endAt = strtotime($command[3]);
      if($endAt == ""){
        $endAt = time()+60*60*24;
      }

      $questionA = array_slice($command, 4);
      $question = "";

      foreach($questionA as $q){
        $question .= $q." ";
      }
      $select = SelectMenu::new();
      foreach($options as $option){
        $select->addOption(Option::new($option));
      }


      $channel = $this->hermen->discordClient->getChannel($channel);
      if(is_null($channel)){
        $message->reply("Channel not found.");
        return;
      } else {
        $message->reply("Poll created.");
      }

      $newMessage = $channel->sendMessage(
          MessageBuilder::new()
            ->setEmbeds([['title' => 'POLL', 'description' => $question, 'color' => 65280]])->addComponent($select)
      );

      $newMessage->done(function(Message $messageEvent) use ($message, $select, $endAt){
        try {
          $statement = $this->hermen->getDatabase()->prepare("INSERT INTO polls (guild_id, user_id, channel_id, message_id, created_at, end_at) VALUES (?, ?, ?, ?, ?, ?)");
          $statement->execute([(int)$message->guild_id, (int)$message->user_id, (int)$message->channel_id, 0, time(), $endAt]);
        } catch(PDOException $e){
          echo $message->guild_id;
          $message->reply("Error while creating poll. ".$e->getMessage());
          return;
        }

        $select->setListener(function (Interaction $interaction, Collection $options) {
          $interaction->respondWithMessage(MessageBuilder::new()->setContent('thankss!'));
        }, $this->hermen->getDiscordClient());
      });

    } catch(NoPermissionsException $e) {
      echo "No permissions to send messages in this channel. ".$e->getMessage();
    }
  }

}