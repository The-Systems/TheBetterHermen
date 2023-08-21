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

    public function __construct(Hermen $hermen)
    {
        $this->hermen = $hermen;

        parent::__construct($hermen);
        parent::createCommand($this->command, $this);
    }

    public function runCommand(Message $message)
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->hermen->getConfig()['hosmatic']['url'] . '/v1/user',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->hermen->getConfig()['hosmatic']['token'],
                    'ID: ' . $message->user_id
                ),
              CURLOPT_SSL_VERIFYHOST => 0,
              CURLOPT_SSL_VERIFYPEER => 0,
            ));

            $responseRaw = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            $response = json_decode($responseRaw, true);

            if($response == null){
              $message->channel->sendMessage("Error: invalid response, Code: ".$statusCode.", Error: ".$error);
            } else {
              if ($response['success']) {
                $response = $response['response'];
                $message->channel->sendMessage(MessageBuilder::new()->setEmbeds([['title' => 'Profil', 'description' => $response['username'], 'color' => 65280,],]));
              } else {
                $message->channel->sendMessage("Error: " . $responseRaw);
              }
            }
        } catch (NoPermissionsException $e) {
            echo "No permissions to send messages in this channel. " . $e->getMessage();
        }
    }

}