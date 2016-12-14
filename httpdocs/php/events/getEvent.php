<?php
  require_once('EventSystem.php');

  $eventSystem = new EventSystem();
  $eventSystem->getEvent($_GET["id"]);
?>
