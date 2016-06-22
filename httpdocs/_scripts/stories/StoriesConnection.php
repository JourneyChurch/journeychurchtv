<?php
  class StoriesConnection {
    private $DB_NAME = "stories_";
    private $DB_USER = "stories-admin";
    private $DB_PASS = "%M79dl2g";
    private $DB_HOST = "localhost";
    private $con;
    private $sql = "";
    private $result;

    public function connect() {
      $this->con = mysqli_connect($this->DB_HOST, $this->DB_USER, $this->DB_PASS, $this->DB_NAME);

      if (!$this->con) {
        die("Cannot connect to database" . mysql_error());
      }
    }

    public function submit($name, $beginning, $persevered, $growth, $email) {
      $this->connect();

      if (isset($email)) {
        $this->sql .= "INSERT INTO `stories-posts` (Name, Beginning, Persevered, Growth, Email) VALUES ('$name', '$beginning', '$persevered', '$growth', '$email')";

        mysqli_query($this->con, $this->sql);
      }

      mysqli_close($this->con);
    }

    public function getStories($categories, $status, $startDate, $endDate) {
      $this->connect();

      $this->sql .= "SELECT * FROM `stories-posts` WHERE Removed = 0";

      if (!empty($categories)) {
        foreach($categories as $category) {
           $this->sql .= " AND `$category` = 1";
        }
      }

      if (!empty($status)) {
        if ($status != 'all') {
            $this->sql .= " AND `Status` = '$status'";
        }
      }

      if (!empty($startDate) && !empty($endDate)) {
        $this->sql .= " AND `Date` >= '$startDate' AND `Date` <= '$endDate'";
      }
      else if (!empty($startDate)) {
        $this->sql .= " AND `Date` >= '$startDate'";
      }
      else if (!empty($endDate)) {
        $this->sql .= " AND `Date` <= '$endDate'";
      }

      $this->sql .= " ORDER BY ID DESC";

      //echo $this->sql;

      $this->result = mysqli_query($this->con, $this->sql);

      $values = array();
      $AllCategories = array("abuse", "addiction", "adoption", "anger", "apathy", "bitterness", "death-&-loss", "disappointment", "doubt", "family", "financial", "forgiveness", "gods-love", "grace", "healing-recovery", "hope", "journey-groups", "life-change", "love-relationships", "marriage", "mercy", "miracle", "missions", "natural-disasters", "parenting", "patience", "persecution", "prophecy", "reconciliation", "religion", "salvation", "school", "serving", "work");
      $count = 0;

      while($row = mysqli_fetch_assoc($this->result)) {
        $id = $row['ID'];
        $name = $row['Name'];
      	$beginning = $row['Beginning'];
      	$persevered = $row['Persevered'];
      	$growth = $row['Growth'];
      	$email = $row['Email'];
        $status = $row['Status'];

        $storyCategories = array();

        foreach($AllCategories as $category) {
          if ($row[$category] == 1) {
            array_push($storyCategories, $category);
          }
        }

        $timestamp = strtotime($row['Date']);
        $date = date('M j Y', $timestamp);

        //$this->printStory($id, $name, $beginning, $persevered, $growth, $email, $date, $status);
        $values[$count] = array('id' => $id, 'name' => $name, 'beginning' => $beginning, 'persevered' => $persevered, 'growth' => $growth, 'email' => $email, 'status' => $status, 'date' => $date, 'categories' => $storyCategories);

        ++$count;
      }

      mysqli_close($this->con);

      echo json_encode($values);
    }

    public function remove($remove, $id) {
      if ($remove == 1) {
          $this->sql .= "UPDATE `stories-posts` SET Removed = 1 WHERE ID = $id;";
      }
    }

    public function setCategories($categoryNames, $checked, $id) {
      $this->sql .= "UPDATE `stories-posts` SET";

      $categoryIndex = 0;

      foreach($categoryNames as $category) {
        if ($checked[$categoryIndex] == 1) {
          $this->sql .= " `$category` = 1,";
        }
        else {
          $this->sql .= " `$category` = 0,";
        }

        ++$categoryIndex;
      }

      $this->sql = rtrim($this->sql, ",");

      $this->sql .= " WHERE `ID` = $id;";
    }

    public function setStatus($status, $id) {
      $this->sql .= " UPDATE `stories-posts` SET `Status` = '$status' WHERE `ID` = '$id';";
    }

    // multiple queries from database to admin
    public function update() {
      $this->connect();

      mysqli_multi_query($this->con, $this->sql);
      $this->sql = "";
      mysqli_close($this->con);
      $this->getStories();
    }
  }
?>
