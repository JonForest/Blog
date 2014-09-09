<?php
require "assets/php/magicquotes.php";
require "assets/php/dbconnection.php";

        
        $articlePK = $_GET["a"];


     
        // Insert into the database
        $sql = "UPDATE Articles SET status='published' WHERE articlePK=?"; 
        $stmt = $con->prepare($sql);
        //$html = $con->real_escape_string($html);
        $stmt->bind_param("d",$articlePK); 
        $stmt->execute(); 
        $stmt->close(); //close statement
        
        
        
        
        $sql = "SELECT articleId FROM Articles WHERE articlePK=?";
        $stmt=$con->prepare($sql);
        $stmt->bind_param("d",$articlePK);
        $stmt->execute();
        $stmt->bind_result($articleId);
        $stmt->fetch();
        $stmt->close();
        
        //remove previous published tags
        $sql = "UPDATE Articles SET status='pre-published' WHERE articleId=$articleId AND articlePK!=?";
        $stmt=$con->prepare($sql);
        $stmt->bind_param("d",$articlePK);
        $stmt->execute();
        $stmt->close();

        header("Location: blog.php?a=$articleId");
?>
