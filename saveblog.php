<?php

require "assets/php/magicquotes.php";
require "assets/php/dbconnection.php";
        
        $html = $_POST["text"];
        $title = $_POST["title"];
        $tldr = $_POST["tldr"];
        $articleId = $_POST["articleId"];
        $refs = json_decode($_POST["references"]);
        $tags = json_decode($_POST["tags"]);
        
       //$articleId = 1;
        //echo $articleId;

     
        // Insert into the database
        $sql = "INSERT INTO Articles (articleId,title,html,tldr,createdDate,lastUpdate) VALUES (?,?,?,?, DATE(NOW()), NOW())";  
        $stmt = $con->prepare($sql);
        //$html = $con->real_escape_string($html);
        $stmt->bind_param("dsss",$articleId, $title, $html, $tldr); 
        $stmt->execute(); 
        $stmt->close(); //close statement

        
        //Find primary key Id of last statement
        $artPK = mysqli_insert_id($con);
       
        // Insert into tags
        $sql = "INSERT INTO Tags (articlePK, tag) VALUES (?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ds",$artPK, $tag); 
        for ($i=0; $i<count($tags); $i++) 
        {
            $tag = $tags[$i];
            $stmt->execute();
        }
        $stmt->close(); //close statement

        // Insert into References
        $sql = "INSERT INTO Refs (articlePK, url, description) VALUES (?, ?, ?)";
        //echo ($sql);
        $stmt = $con->prepare($sql);
        $stmt->bind_param("dss",$artPK, $url, $desc); 
        //var_dump($refs);

        for ($i=0; $i<count($refs); $i++) 
        {
            //var_dump($refs[$i]);
            $url = $refs[$i]->url;
            $desc = $refs[$i]->description;
            $stmt->execute();
        }
        $stmt->close(); //close statement
        

        //Send back date and lastupdated in JSON format
        $sql = "SELECT articleId, createdDate, lastUpdate FROM Articles WHERE articlePK=$artPK";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($articleId, $date, $lastUpdate);
        $stmt->fetch(); //only need to worry about one row 
        $stmt->close();
        
        //Package data up an array object for transfer
        $retData = array('articlePK'=>$artPK, 'articleId' => $articleId, 'date' => $date, 'lastUpdate'=>$lastUpdate);
        echo json_encode($retData);  //Write json back 
         
        
        // put your code here*/
?>
