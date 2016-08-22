<?php
  require_once("StoriesConnection.php");

  $connection = new StoriesConnection();
  $connection->remove($_POST["remove"], $_POST["id"]);
  $connection->setCategories($_POST["categoryNames"], $_POST["checked"], $_POST["id"]);
  $connection->setStatus($_POST["status"], $_POST["id"]);
  $connection->update();
?>
