<?php
  require_once("StoriesConnection.php");

  $connection = new StoriesConnection();
  $connection->getStories($_POST['categories'], $_POST['status'], $_POST['startDate'], $_POST['endDate']);
?>
