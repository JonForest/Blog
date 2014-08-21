<?php
require "assets/php/dbconnection.php";

$articleId = $_GET["a"];


    
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
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="assets/js/jquery.caret.js"></script>
        <script src="assets/js/shortcut.js"></script>
        <link rel="stylesheet" type="text/css" href="assets/css/blog.css">
  
        <title>The Old Dog Blog</title>
    </head>
    <body>
        <div class="header">The Old Dog Blog</div>
        <div class="main">       
            <div class="blogText">
                <h2>Capturing the HTML - The Editor</h2>
                <h3>21<sup>st</sup> May, 2013</h3>
                <p>Next I'd like to store the content of the blog in a database, rather than hand-craft each page.  This will allow new pages to be developed more quickly, 
                    and allow such things as programatically building the navigation menu based on content.</p>
<p>The first step in this process is to author and capture the post content.  I had toyed with using an <b>iFrame</b> and <b>execCommand</b> (see references) to create a WYSIWYG HTML editor,
    but it quickly became apparent that this was a <i>big</i> task (see references).  It also wasn't a great functional fit, and I strongly believe in building the barest minimum of functionality
    for a first version.  Therefore I've built a slightly modified HTML editor.</p>
<p>If you want to reference the working demo while looking at this post, go <a href="exampleblogentry.php" target=_blank"">here (opens new window)</a>.</p>
<h4>The Editor Basics</h4>
<p>I have chosen a <b>&lt;textarea&gt;</b> input element into which I will type the HTML.  However, I'm also planning on building a few tools.  Below is the basic HTML construct.</p>
<div class="container">
                    <div class="lbl">HTML</div>
                    <div class="entry">
                        <button class="button">Heading<br>(ctrl+h)</button>
                        <button class="button">Bold<br>(ctrl+b)</button>
                        <button class="button">Italic<br>(ctrl+i)</button>
                        <button class="button">Paragraph<br>(ctrl+p)</button>
                        <button class="button">Code<br>(ctrl+d)</button>
                        <textarea></textarea>
                    </div>
</div>
<pre>
&lt;div class="container"&gt;
&lt;div class="lbl"&gt;HTML&lt;/div&gt;
&lt;div class="entry"&gt;
    &lt;button id="heading" &gt;Heading&lt;br&gt;(ctrl+h&gt;&lt;/button&gt;
    &lt;button id="bold" &gt;Bold&lt;br&gt;(ctrl+b)&lt;/button&gt;
    &lt;button id="italic" &gt;Italic&lt;br&gt;(ctrl+i)&lt;/button&gt;
    &lt;button id="paragraph" &gt;Paragraph&lt;br&gt;(ctrl+p)&lt;/button&gt;
    &lt;button id="pre" &gt;Code&lt;br&gt;(ctrl+d)&lt;/button&gt;
    &lt;textarea id="htmlWindow"&gt;&lt;/textarea&gt;
&lt;/div&gt;
&lt;/div&gt;
</pre>
<p>Each button, or keyboard shortcut, will add the tags into the current caret (cursor) position and place the caret inside the tags.</p>
<h4>Styling of Editor Window</h4>
<p>Before we dip into the buttons and keyboard shortcut functionality, let's just cover off the CSS styling:</p>
<pre>
/*This ensures that any changes to the Padding property does
   not mess with the designated widths */
*, *:after, *:before {
   -webkit-box-sizing: border-box;
   -moz-box-sizing: border-box;
    box-sizing: border-box;
 }

/*Size and shape of all textarea elements on the page.
As we're only planning on using one, this is fine */
textarea {
    vertical-align: top;
    float:left;
    margin-right: 10px;
    margin-bottom: 20px;
    width:100%;
    border-radius:15px;
    padding:0px 5px;
    margin: 0px;
    min-height: 300px;
}

/*Changes background colour of element when in focus*/
input:focus, textarea:focus 
{ 
    background-color:rgb(255,255,204);
}

/*Shape and background colour of button*/
button {
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    background-color: rgb(224,224,224);
}
/*Background colour for when the button is being clicked, to
give it a more interactive feel*/
button:active {
    background-color: rgb(150,150,150);
}
</pre>
<p>There should be nothing there that the in-line comments don't explain.  Notice the colon
notation &lt;selector&gt;:&lt;state&gt; within the style reference.  This allows you to set a different style for different states of the same element.  E.g. Adding <b>a:hover</b> would allow you to set a style for when you hover, or mouse-over, a link.</p>
<p>You can see the available states in the CSS Reference link below.</p>
<h4>Building the Buttons</h4>
<p>The code and styles for the buttons have already been demonstrated, so we'll look straight at the code.  I have used JQuery to make my life a little easier.  If you have not used JQuery before, I'd really recommend it.  It doesn't replace the requirement to learn Javascript, but it will make common page activities a lot quicker to author and secure you against cross-browser behaviour differences.  Some kind soul has already done a hell of a lot of work for you, so use it.   I've added some JQuery references to the bottom of this post, so if you've not used it before, take a look.</p>
<p>The code for the button event and actions is below:</p>
<pre>
$('#bold').click (function () {
    addHTML('&lt;b&gt;');
})

function addHTML(tag){
    var closingTag = [tag.slice(0, 1), '/', tag.slice(1)].join('');

    //Get current caret position and build new html
    caretPos = $('#htmlWindow').caret();
    var html = $('#htmlWindow').val().slice(0,caretPos) + tag + closingTag + $('#htmlWindow').val().slice(caretPos);

    //Add the new HTML back into the textarea
    $('#htmlWindow').val(html); 

    //Position the caret inside the newly added tag
    $('#htmlWindow').caret(caretPos+tag.length);
}
</pre>
<p>The <b>$('#bold').click(func)</b> is fired on the button click event for an element with an id of <b>bold</b>.  I've used an anonymous function for the argument for the <b>click</b> function.  If you're not familiar with this concept then I would suggest reading a couple of Javascript references.  This notation is used heavily throughout JQuery and understanding it is critical.</p>
<p>The <b>addHMTL</b> function makes use of a JQuery plug-in <b>jquery.caret.js</b> - see references.  This allows us to <i>get</i> and <i>set</i> the caret (cursor) position in the <b>textarea</b> input element.  The syntax is pretty limited, and should be clear from the code sample.</p>
<p>The function's only argument is a string containing the HTML tag (e.g. "&lt;i&gt;") .  The first line of the function creates the accompanying closing tag but inserting a forward-slash (/) after the initial angle-bracket.</p>
<p>The next two lines identify where the caret is in the current text and creates a new HTML string with the tag inserted at this point.  For a large amount of text this operation my become computationally expensive, but initial tests writing this blog page haven't indicated any issue. </p>
<p>Finally the caret is re-positioned inside the newly added tag.  Note, if this line didn't exist at all the caret would be repositioned at the start of the text, as the whole HTML string has been replaced.</p>
<h4>The Keyboard Short-cuts</h4>
<p>To implement the keyboard short-cuts, we've made use of the excellent <b>shortcut.js</b> open Javascript library.  It's been extended to support JQuery, but when I tried to use that version I was getting Javascript errors inside the library code, so I reverted to this version.  See below for a code sample of how this works:</p>
<pre>
shortcut.add('ctrl+p', function () {
    addHTML('&lt;p&gt;');
});
</pre>
<p>The first argument is key, or key combination, the second is another anonymous function where you define the functionality you want to fire on key-press.  Since we want to duplicate the button press functionality, we can simply call the same <b>addHTML</b> function.</p>
<h4>Preview the code</h4>
<p>The final piece of this puzzle is to provide a mechanism to see the output in real-time as you're typing.  This is easily done by defining a <b>&lt;div&gt;</b> and then updating the HTML into that div on each key-press.  Let's look at the div code:</p>
<pre>
&lt;div id="editorWindow"&gt;&lt;/div&gt; 
</pre>
<p>And now the Javascript</p>
<pre>
$('#htmlWindow').keyup (function () {
    $('#editorWindow').html($('#htmlWindow').val());
});
</pre>
<p>This once again uses JQuery to pick up the <b>keyup</b> event when <b>textarea</b> is in focus.  Keyup is important, as if keydown is used this event will fire before the latest key-press has registered.  Notice that <b>htmlWindow</b> requires use of the <b>val()</b> function rather than <b>html</b> as is used for the div.  For the content of form elements you collect their contents using their value rather than their text or html.  In traditional Javascript you would require <b>document.getElementById('htmlWindow').value</b>, the <b>document.getElementById('htmlWindow').innerHTML</b> command would not return any value.</p>
<h4>The Wrap-Up</h4>
<p>We now have all the tools to build the HTML for the main part of the blog post.  However, it is not yet being posted into a database, has no save tool, and there is no proper Preview tool.  These items will be picked up in the some of the next posts. </p>
                    
                
                <span class="section"><h4>tl;dr</h4>
                    <p>JQuery makes life easier and don't over-develop.</p></span>
                <span>
                    <h4>References</h4>
                    <a href="http://stackoverflow.com/questions/4426478/building-a-wysiwyg-editor">http://stackoverflow.com/questions/4426478/building-a-wysiwyg-editor</a> - 
                    Great Stack Overflow post about the method and challenges of writing your own HTML WYSIWYG editor. <br>
                    <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Reference">https://developer.mozilla.org/en-US/docs/Web/CSS/Reference</a> - 
                    CSS Reference for components used<br>
                    <a href="http://jquery.com/>http://jquery.com/</a> - 
                    JQuery project homepage<br>
                    <a href="http://learn.jquery.com/">http://learn.jquery.com/</a> - 
                    Tutorials and reference for JQuery<br>
                    <a href="http://plugins.jquery.com/caret/">
                        http://plugins.jquery.com/caret/
                    </a> - Link to the shortcuts.js home page, for reference and to download the library<br>
                    <a href="http://www.openjs.com/scripts/events/keyboard_shortcuts/">
                        http://www.openjs.com/scripts/events/keyboard_shortcuts/
                    </a> - Link to the shortcuts.js home page, for reference and to download the library<br>
                    
                </span>
            </div>
            <div class="info">
                Contact me at<br>
                <a href="mailto:jonathan@able-futures.com">jonathan@able-futures.com</a>
                <h4>Previous posts</h4>
                <p class="navigation">
                    <?=$menuHtml?>
                    2013-05-21  - Capturing the HTML - The Editor<br>
                    <a href="20130516.php">2013-05-16 - Creating the Blog - First Steps</a><br>
                    
                </p>
            </div>
        </div>
        
    </body>
</html>
