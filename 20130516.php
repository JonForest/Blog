<?php
require "assets/php/dbconnection.php";

$articleId = $_GET["a"];
//TODO:Check if articleId is blank

    
//Build menu
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
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <link rel="stylesheet" type="text/css" href="assets/css/blog.css">
               
        <title></title>
    </head>
    <body>
        <div class="header">The Old Dog Blog</div>
        <div class="main">       
            <div class="blogText">
                <h2>Creating the Blog - First Steps</h2>
                <h3>16<sup>th</sup> May, 2013</h3>
                <span class="section">
                    <p>Hello and welcome to the first blog post in a new series that
                        will follow my attempts to get back into development after far 
                        too long as a Project Manager.</p>
                    <p>In this post I'm going to
                        cover the simple CSS for style and layout of this site.  The following blog posts
                        will include creating a simple front-end for adding posts, storing the posts in a
                        database and adding a comments tool.</p>

                    <p>By hosting-provider necessity, and because there are still lots of jobs out there,
                        I'll be carrying out my server-side coding using PHP.  I've 
                        dabbled in the past but will certainly need to moved beyond that level for future plans.</p>
                    <p>Finally, I've also started dipping my toes into the world of Android
                        development.  Therefore, once this blog is working satisfactorily,
                        I'll be adding some posts on that subject.</p>
                    <p>Right, let's get started!</p>
                </span>
                
                <span class="section">
                    <h4>The Background Image</h4>
                    <p>This background image is of Lake Tekapo on New Zealand's magnificent South Island.
                       I've carried out some minor image manipulations using 
                       <a href="http://seashore.sourceforge.net/The_Seashore_Project/About.html">Seashore</a>,
                       an open source image editor on the Mac.  I'm still new to this application, but
                       I'm pretty happy so far.</p>
                    <p>This was then fixed as the background image using the following CSS code:</p>
                    <pre class="code">
body {
  background-image:url('images/bg3.jpg'); /*display image 'bg3.jpg' found in images subfolder*/
  background-repeat:repeat-x; /*Repeat horizontally, but not vertically in case screen wider than image*/
  background-attachment: fixed; /*Keep the image fixed on the screen, do not scroll*/
  background-position-x: left; /*Align the image to the left edge of the browser*/
  background-position-y: top; /*Align the image to the top of the browser*/
}
                    </pre>

                </span>
                <span class="section">
                    <h4>The Page Structure</h4>
                    <p>I've copied pretty heavily from another Blog site for this layout,
                    but it works well and is <i>fairly</i> simple</p>
                    <div style="border-style:solid; width:30%;">
                        <div style="border-style:solid;">Title Div</div>
                        <div style="border-style:solid; width:90%;overflow:auto"">Container Div<br>
                            <div style="border-style:solid; width:70%;float:left; min-height:100px;">Blog Text Div</div>
                            <div style="border-style:solid; width:20%;float:left; min-height:100px;">Info Div</div>
                        </div>
                    </div>
                </span>
                <span class="section">
                    <h4>Title Div</h4>
                    <p>The Title div has the following style</p>
                    <pre class="code">
.header {
    width:100%; /*fill the width of the screen*/
    padding:10px 30px; /*Ensure any content is 10px from the top and bottom edge
                        and 30px from the left and right edges*/
    font-size: 1.4em;  /*1em is standard sized text, make this a bit bigger*/
    font-weight: bold;  /*Text is bold*/
    color:white;  /*Text is white*/
    background: rgb(150, 0, 0); /*For those browsers that don't support rgba,
                                  background colour is red*/
    background: rgba(150, 0, 0, 0.5);  /*Opacity rating (transparentness)*/
}
                    </pre>
                    <p>This is all pretty straight forward (CSS reference at the
                        bottom of the post), with the possible exception of 
                        <b>background: rgba(150,0,0,0.5)</b>.  The final <b>a</b> in 
                        this value standards for Alpha, which is a measure of the 
                        colour's Opacity - or how transparent the colour is.<br>
                        1 - Fully opaque, not transparent at all (equivalent to using <b>rgb</b>).<br>
                        0 - Fully transparent, no evidence of any background colour.</p>
                    <p>The <b>rgba</b> value is browser dependent.  Though now 
                        fully supported by all modern desktop/laptop browsers, some 
                        mobile browsers may not work.  For this reason, I've continued
                        to include the <b>rgb</b> value for backwards compatibility.</p>
                </span>
                <span class="section">
                    <h4>Container Div</h4>
                    <p>The Container div has the following style</p>
                    <pre class="code">                    
.main {
    -moz-border-radius: 15px;
    -webkit-border-radius: 15px;
    border-radius: 15px;
    width: 90%; /*90% of current screen*/
    background: rgb(0, 0, 0); /*Black background for browsers not supporting rgba*/
    background: rgba(0, 0, 0, 0.6); /* RGBa with 0.6 opacity */
    padding: 20px 10px;  /*top and bottom 20px, left and right 10px*/
    margin:10px; /*10px space between div and parent container, around each edge */
    overflow:auto; /*Required to ensure Container stretches vertically to contain
                    child divs*/
}
                    </pre>
                    <p>The interesting items here are the <b>border-radius</b>  
                        and <b>overflow:auto</b> properties.</p>  
                        <p>The <b>border-radius</b> property is browser
                        dependent, but fully supported amongst the current crop 
                        of desktop browsers.  Mobile support is a little more patchy.
                    The <b>-moz-border-radius</b> and <b>-webkit-border-radius</b> are added specifically for older versions
                        of the Mozilla and Safari respectively.</p>
                     <p>The references for more information are included below, as is the browser 
                        compatibility chart.</p>
                     <p>The <b>overflow:auto</b> property is a little more difficult
                         to explain.  The default <b>overflow</b> value is <b>visible</b>.  If this
                         value is used, then in this instance, the Container div would not stretch
                         vertically to contain the Blog Text and Info divs.</p>
                     <p>By setting a value to other than <b>visible</b>, we're created a new 
                         Block Formatting Context that will contain the child divs.  There is
                         a great answer on Stack Overflow, linked below, that contains the best
                         explanation I've seen on a fairly confusing subject.</p>
                    </p>
                </span>
                <span class="section">
                    <h4>Blog Text and Info Divs</h4>
                    <p>The Blog Text and Info divs have the following style</p>
                    <pre class="code">
.blogText {
    -moz-border-radius: 15px;
    -webkit-border-radius: 15px;
    border-radius: 15px;
    background-color: white;
    color:black;
    width:70%;
    padding: 15px;
    font-family: "Verdana";
    font-size: 0.9em;
    float:left;

}

.info {
    -moz-border-radius: 15px;
    -webkit-border-radius: 15px;
    border-radius: 15px;
    background-color: white;
    padding: 15px;
    font-family: "Verdana";
    font-size: 0.7em;
    margin: 0px 0px 0px 14px;
    float:left;
    width:23%;
}
        </pre>
                    <p>There are no new concepts on display in these classes.  The key
                    consideration is that the combined widths of the two divs, including all 
                    margins, paddings, etc. are not greater than the width of the Container div.  
                    If they are, then the Info div will appear below the Blog Text div - 
                    not horizontally aligned as desired.</p>
                    <p>Hopefully this gives a good overview of how this page is put together, and
                    explains the key concepts.  The next post should be up in a week or so, though there
                    is a lot to do in that time.</p>
                </span>
                    
                
                <span class="section"><h4>tl;dr</h4>
                    <p>CSS layout is still harder than you think it should be.</p></span>
                <span>
                    <h4>References</h4>
                    <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Reference">https://developer.mozilla.org/en-US/docs/Web/CSS/Reference</a> - 
                    CSS Reference for components used<br>
                    <a href="http://css-tricks.com/rgba-browser-support/">http://css-tricks.com/rgba-browser-support/</a> - 
                    Browser support for RBGA<br>
                    <a href="http://caniuse.com/border-radius">http://caniuse.com/border-radius</a> - 
                    Browser support for <b>border radius</b> css property<br>
                    <a href="http://stackoverflow.com/questions/12783064/why-does-overflow-hidden-have-the-unexpected-side-effect-of-growing-in-height-t/12783114#12783114">
                        http://stackoverflow.com/questions/12783064/why-does-overflow-hidden-have-the-unexpected-side-effect-of-growing-in-height-t/12783114#12783114
                    </a> - Explanation of requirement to set <b>overflow</b> CSS property<br>
                    
                </span>
            </div>
            <div class="info">
                Contact me at<br>
                <a href="mailto:jonathan@able-futures.com">jonathan@able-futures.com</a>
                <h4>Previous posts</h4>
                <p class="navigation">
                    <?=$menuHtml?>
                    <a href="20130521.php">2013-05-21  - Capturing the HTML - The Editor</a><br>
                    2013-05-16 - Creating the Blog - First Steps<br>
                    
                </p>
            </div>
        </div>
        
    </body>
</html>
