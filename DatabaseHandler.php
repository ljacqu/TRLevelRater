<?php

class DatabaseHandler {

  private $conn;
  private $name;

  function __construct() {
    $host = Configuration::DB_HOST;
    $this->name = Configuration::DB_NAME;
    $this->conn = new PDO(
      "mysql:host={$host};dbname={$this->name}", Configuration::DB_USER, Configuration::DB_PASS,
      [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]);
  }

  function getRating(string $user, string $level): ?array {
    $stmt = $this->conn->prepare('SELECT rating FROM tr_level_rating WHERE user = :user AND level = :level;');
    $stmt->bindParam('user', $user);
    $stmt->bindParam('level', $level);
    $stmt->execute();
    return $stmt->fetch();
  }

  function getRatings(string $user): array {
    $stmt = $this->conn->prepare('SELECT level, rating FROM tr_level_rating WHERE user = :user');
    $stmt->bindParam('user', $user);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  function getAverage(string $level): array {
    $stmt = $this->conn->prepare('SELECT AVG(rating) as avg, COUNT(rating) as cnt FROM tr_level_rating WHERE level = :level;');
    $stmt->bindParam('level', $level);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  function getAverages(): array {
    $stmt = $this->conn->prepare('SELECT level, avg(rating) as avg, count(rating) as cnt FROM tr_level_rating group by (level);');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  function addOrUpdateRating(string $user, string $level, float $rating): void {
    $stmt = $this->conn->prepare('
      INSERT INTO tr_level_rating (level, user, rating, date)
      VALUES (:level, :user, :rating, NOW())
      ON DUPLICATE KEY UPDATE rating = :rating, date = NOW();');

    $stmt->bindParam('user', $user);
    $stmt->bindParam('level', $level);
    $stmt->bindParam('rating', $rating);
    $stmt->execute();
  }

  function initTables(): void {
    $this->conn->exec('
      CREATE TABLE IF NOT EXISTS tr_level_rating (
        level varchar(10) NOT NULL,
        user varchar(128) NOT NULL,
        rating decimal(2, 1) NOT NULL,
        date datetime NOT NULL,
        UNIQUE KEY tr_rat_level_user (level, user) USING BTREE
      ) ENGINE = InnoDB;');
  }
}
