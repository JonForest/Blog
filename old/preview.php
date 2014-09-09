
<!DOCTYPE html>
<?php
require "assets/php/magicquotes.php";
require "assets/php/dbconnection.php";


    $tagHtml = "";
    $refHtml = "";
    $articlePK = $_GET["a"];
    //TODO:Check if articleId is blank

    //Send back date and lastupdated in JSON format
    $sql = "SELECT articleId, title, html, tldr, createdDate, lastUpdate FROM Articles WHERE articlePK=$articlePK";
    //echo $sql;
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($articleId, $title, $html, $tldr, $date, $lastUpdate);
    $stmt->fetch(); //only need to worry about one row 
    $stmt->close();
    
    $sql = "SELECT tagPK, tag FROM Tags WHERE articlePK=$articlePK";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($tagPK, $tag);
    while ($stmt->fetch()) 
    {
        $tagHtml+= '<a href="blog.php?tag=$tagPK">$tag</a>,';
    }
    //Remove the last comma as it isn't needed
    $tagHtml = substr($tagHtml, 0, strlen($tagHtml)-1); //TODO: Think this won't work.  See enterblog.php
    $stmt->close();
    
    $sql = "SELECT url, description FROM Refs WHERE articlePK=$articlePK";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($url, $description);
    while ($stmt->fetch()) 
    {
        $refHtml.='<a href="'.$url.'">'.$url.'</a> - '.$description.'<br>';
    }
    //echo $refHtml;
    $stmt->close();
    
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="../assets/js/jquery.caret.js"></script>
        <script src="../assets/js/shortcut.js"></script>
        <link rel="stylesheet" type="text/css" href="../assets/css/blog.css">
            
        <script type="text/javascript">
            
            $(document).ready(function() {
                
                
                
                $('#publish').click (function () {
                    console.log('publish');
                    window.location.href='publishaction.php?a=<?=$articlePK?>';
                });
                
                $('#back').click (function () {
                    console.log('back');
                    window.location.href='enterblog.php?a=<?=$articlePK?>';
                });
            });
                
              
        </script>   
        <title>The Old Dog Blog</title>
    </head>
    <body>
        <div class="header">The Old Dog Blog</div>
        <div class="main">       
            <div class="blogText"> 
                <h2><?=$title?></h2>
                <h3><?=$date?></h3>
                <?=$html?> 
                <span class="section"><h4>tl;dr</h4>
                    <p><?=$tldr?></p></span>
                <span>
                    <h4>References</h4>
                    <?=$refHtml?>
                    
                </span>
            </div>
            <div class="info">
                <button id="back">Back</button>
                <button id="publish">Publish</button>
                    
                </p>
            </div>
        </div>
        
    </body>
</html>
