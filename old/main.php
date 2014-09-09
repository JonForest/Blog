<?php

require "assets/php/dbconnection.php";

//Send back date and lastupdated in JSON format
$sql = "SELECT articleId FROM Articles WHERE status='published' ORDER BY createdDate DESC LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->execute();
$stmt->bind_result($articleId);
$stmt->fetch(); //only need to worry about one row 
$stmt->close();
//echo $articlePK;
header( 'Location: blog.php?a='.$articleId ) ;
?>
