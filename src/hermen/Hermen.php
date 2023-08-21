<?php

namespace hermen;

use Discord\Parts\User\Activity;
use PDO;
use Exception;
use PDOException;
use Discord\Discord;
use hermen\events\Events;
use hermen\commands\LoadCommands;

class Hermen
{

  public readonly string $path;
  public readonly Discord $discordClient;
  public array $commands = [];

  private readonly PDO $database;

  /**
   * @throws Exception
   */
  public function __construct ()
  {
    $this->path = realpath(__DIR__ . "/../../");
    #$this->database = $this->initialDatabase();
  }

  public function getConfig(): array
  {
    return json_decode(file_get_contents($this->path."/config/config.json"), true);
  }

  public function getDatabase(): PDO
  {
    return $this->database;
  }

  /**
   * @throws Exception
   */
  private function initialDatabase(): PDO
  {
    if (!class_exists(PDO::class)) {
      throw new Exception("Class PDO not found", 404);
    }
    $db_config = json_decode(file_get_contents($this->path."/config/mysql.json"), true);


    try {
      $pdo = new PDO('mysql:host=' . $db_config['host'] . ';charset=utf8;dbname=' . $db_config['database'].';port='.$db_config['port'], $db_config['username'], $db_config['password'], [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      return $pdo;
    } catch (PDOException $e) {
      throw new Exception($e->getMessage(), $e->getCode());
    }
  }

  public function setClients(Discord $discord): void
  {
    $this->discordClient = $discord;

    new LoadCommands($this);

    $hermen = $this;
    $this->discordClient->on('ready', function (Discord $discord) use ($hermen) {
      new Events($hermen);
      new SlashCommands($hermen);
      $this->discordClient->updatePresence(new Activity($discord, ['name' => 'PHP', 'type' => Activity::TYPE_COMPETING]), false, "online");
    });


    $this->discordClient->run();
  }

  public function getDiscordClient(): Discord
  {
    return $this->discordClient;
  }


}