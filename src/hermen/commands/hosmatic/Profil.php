<?php
namespace hermen\commands\hosmatic;

use hermen\Hermen;
use hermen\commands\Commands;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Bitty\EventManager\EventInterface;
use Discord\Http\Exceptions\NoPermissionsException;

class Profil extends Commands
{
  public Hermen $hermen;
  private string $command = "profil";

  public function __construct (Hermen $hermen)
  {
    $this->hermen = $hermen;

    parent::__construct($hermen);
    parent::createCommand($this->command, $this);
  }

  public function runCommand(Message $message){
    try {

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->hermen->getConfig()['hosmatic']['url'].'/v1/user',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer '.$this->hermen->getConfig()['hosmatic']['token'],
          'ID: '.$message->user_id
        ),
      ));

      $response = curl_exec($curl);
      curl_close($curl);
      $response = json_decode($response, true);
      $response = $response['response'];



      $message->channel->sendMessage(MessageBuilder::new()->setEmbeds([['title' => 'Profil', 'description' => $response['username'], 'color' => 65280,],]));
    } catch(NoPermissionsException $e) {
      echo "No permissions to send messages in this channel. ".$e->getMessage();
    }
  }

}