<?php

use php\events;

?>

{embed="embed/header"}

<div class="container">
  <div class="row">
    <div class="col-sm-12">
      <?php

        $facebookConnection = new FacebookConnection(174276778638);
        $facebookConnection->getEvents();

      ?>
    </div>
  </div>
</div>

{embed="embed/footer"}
