<?php

namespace hermen\commands\hosmatic;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Interaction;
use hermen\commands\CommandsInterface;
use hermen\Hermen;
use hermen\commands\Commands;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Discord\Http\Exceptions\NoPermissionsException;

class Profil implements CommandsInterface
{
    public Hermen $hermen;
    private string $command = "profil";

    public function __construct(Hermen $hermen)
    {
      $this->hermen = $hermen;
      $hermen->createCommand($this->command, $this, true);

      $command = $hermen->getDiscordClient()->application->commands->create(CommandBuilder::new()
        ->setName($this->command)
        ->setDescription($this->getDescription())
        ->toArray()
      );
      $hermen->getDiscordClient()->application->commands->save($command);

      $this->hermen->discordClient->listenCommand($this->command, function (Interaction $interaction) {
        $this->getProfil($interaction);
      });
    }

    public function getProfil(Interaction $interaction): void
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
                    'ID: ' . $interaction->user->id
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
              $interaction->respondWithMessage(MessageBuilder::new()->setContent("Error: " . $error));
            } else {
              if ($response['success']) {
                $response = $response['response'];
                $interaction->respondWithMessage(MessageBuilder::new()->setEmbeds([['title' => 'Profil', 'description' => $response['username'], 'color' => 65280,],]));
              } else {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent("Error: " . $response['response']['error_message']));
              }
            }
        } catch (NoPermissionsException $e) {
            echo "No permissions to send messages in this channel. " . $e->getMessage();
        }
    }

  public function getDescription(): string
  {
    return "Hosmatic Profil abrufen";
  }

  public function getCommand(): string
  {
    return $this->command;
  }
}