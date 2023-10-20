<?php
  
class Level {

  public string $name;
  public string $game;
  public array $aliases;

  function __construct(string $name, string $game, array $aliases) {
    $this->name = $name;
    $this->game = $game;
    $this->aliases = $aliases;
  }

  function getId(): string {
    return $this->aliases[0];
  }
}
