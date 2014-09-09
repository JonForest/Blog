
<!DOCTYPE html>
<?php
require "assets/php/magicquotes.php";
require "assets/php/dbconnection.php";
$tagHtml = "";
$refHtml = "";
$menuHtml = "";
//$articlePK = $_GET["a"];
$articleId = $_GET["a"];


if ($articleId=='about') {
    //Build 'About' link
    include 'assets/php/about.html';
    $title='About Me';
    $about='About Me';

}else {
    //Get details for page
    $sql = "SELECT articlePK, title, html, tldr, createdDate, lastUpdate FROM Articles WHERE articleId=? and status='published' ORDER By lastUpdate DESC LiMiT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i",$articleId);
    $stmt->execute();
    $stmt->bind_result($articlePK, $title, $html, $tldr, $date, $lastUpdate);
    $stmt->fetch(); //only need to worry about one row 
    $stmt->close();

    if ($title) {
        $sql = "SELECT tagPK, tag FROM Tags WHERE articlePK=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i",$articlePK);
        $stmt->execute();
        $stmt->bind_result($tagPK, $tag);
        while ($stmt->fetch()) 
        {
            $tagHtml+='<a href="blog.php?tag=$tagPK">$tag</a>,';
        }
        //Remove the last comma as it isn't needed
        $tagHtml = substr($tagHtml, 0, strlen($tagHtml)-1); //TODO: Think this won't work.  See enterblog.php
        $stmt->close();

        $sql = "SELECT url, description FROM Refs WHERE articlePK=$articlePK"; //Don't need to bind param as control $articePK within our code
        
        
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($url, $description);
        while ($stmt->fetch()) 
        {
            $refHtml.='<a href="'.$url.'">'.$url.'</a> - '.$description.'<br>';
        }
        //echo $refHtml;
        $stmt->close();
    } else {
        $title='No article on this ID';
    }
    
    $about='<a href="blog.php?a=about">About Me</a>';
} 



//Build menu
//TODO: Currently this will allow to published articles of the same articleId to be displayed.  Need to limit.
$sql = "SELECT articleId, title, createdDate FROM   Articles t1  WHERE  status = 'Published' AND NOT EXISTS (SELECT 1 FROM   Articles t2 WHERE  t1.articleId = t2.articleId AND  t2.status = 'Deleted' AND  t2.lastUpdate > t1.lastUpdate) ORDER BY createdDate DESC, articleId DESC";
$stmt = $con->prepare($sql);
$stmt->execute();
$stmt->bind_result($menuArticleId, $menuTitle, $menuDate);
while ($stmt->fetch()) 
{
    if ($articleId == $menuArticleId) {
        $menuHtml.=$menuDate.' - '.$menuTitle.'<br>';
    } else {
        $menuHtml.='<a href="blog.php?a='.$menuArticleId.'">'.$menuDate.' - '.$menuTitle.'</a><br>'; 
    }

}

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
            
  
        <title>The Old Dog Blog</title>
    </head>
    <body>
        <div class="header">The Old Dog Blog</div>
        <div class="main">       
        <div class="blogText">
            <h2><?=$title?></h2>
            <h3><?=$date?></h3>
            <?=$html?>
            <?php if ($title!='About Me') {?>
            <span class="section"><h4>tl;dr</h4>
                <p><?=$tldr?></p></span>
            <span>
                <h4>References</h4>
                <?=$refHtml?>

            </span>
          <?php }?>
        </div>
            <div class="info">
                <p><?=$about?></p>
                <p>Email: <br>
                <a href="mailto:jonathan@able-futures.com">jonathan@able-futures.com</a></p>
                <p>Twitter: <br>
                <a href="https://twitter.com/jon_holl" class="twitter-follow-button" data-show-count="false" data-lang="en">@jon_holl</a>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></p>
                <h4>Previous posts</h4>
                <p class="navigation">
                    <?=$menuHtml?>
                    <a href="../20130521.php">2013-05-21  - Capturing the HTML - The Editor</a><br>
                    <a href="../20130516.php">2013-05-16, 2013 - Creating the Blog - First Steps</a><br>
                    
                </p>
            </div>
        </div>
        
    </body>
</html>
