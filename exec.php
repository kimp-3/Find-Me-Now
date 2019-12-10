<?php
  @ $db = new mysqli('localhost', 'root', 'root', 'iit');
  
  if ($db->connect_error) {
    $connectErrors = array(
      'errors' => true,
      'errno' => mysqli_connect_errno(),
      'error' => mysqli_connect_error()
    );
    echo json_encode($connectErrors);
  } else {
    if (isset($_POST["id"])) {
      $PostID = (int) $_POST["id"];

      $query = "UPDATE `itemlist` SET `Status`='Found' WHERE `itemlist`.`PostID`=?";
      $statement = $db->prepare($query);
      $statement->bind_param("i",$PostID);
      $statement->execute();

      $success = array('errors'=>false,'message'=>'Item status changed!');
      echo json_encode($success);

      $statement->close();
      $db->close();
    }
  }
?>