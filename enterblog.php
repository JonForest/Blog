<?php
require "assets/php/magicquotes.php";
require "assets/php/dbconnection.php";

$articlePK = $_GET["a"];
//Create new article Id



$title="";
$html="";
$tldr="";
$tags="";

if ($articlePK) 
{
    
    //Get existing details
    $sql = "SELECT articleId, title, html, tldr FROM Articles WHERE articlePK = ? ORDER BY LastUpdate DESC LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i",$articlePK);
    $stmt->execute();
    $stmt->bind_result($articleId, $title, $html, $tldr);
    $stmt->fetch(); //only need to worry about one row 
    $stmt->close();

    //Now get Tags 
    $sql = "SELECT tag FROM Tags WHERE articlePK = $articlePK";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($tag);
    
    while ($stmt->fetch())
    {
        $tags .= "$tag,";
    } 
    
    $tags = substr($tags, 0, strlen($tags)-1);
    $stmt->close();
    
    //Now get Refs 
    $refs = array();
    $sql = "SELECT url, description FROM Refs WHERE articlePK = $articlePK";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($url, $desc);
    
    while ($stmt->fetch())
    {
        $ref = new Ref();
        $ref->url = $url;
        $ref->desc = $desc;
        array_push($refs, $ref);
        
    } 
    //echo $refs[0]->url;
    //var_dump($refs);
    $stmt->close();
    
    
}
else
{
    //Create new article
    
    $sql = "SELECT articleId FROM Articles ORDER BY articleId DESC LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($articleId);
    $stmt->fetch(); //only need to worry about one row 
    $stmt->close();
        
    $articleId+=1;//increment the id by one.  TODO: Move this to a database operation
    //echo $articleId;
       
    // Insert into the database
    $sql = "INSERT INTO Articles (articleId,title,createdDate,lastUpdate) VALUES (?,'New', DATE(NOW()), NOW())";  
    $stmt = $con->prepare($sql);
    $stmt->bind_param("d",$articleId); 
    $stmt->execute(); 
    $stmt->close(); //close statement
    
    //Find primary key Id of last statement
    $articlePK = mysqli_insert_id($con);
     
}

class Ref
{
    public $url;
    public $desc;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <link rel="stylesheet" type="text/css" href="assets/css/admin.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="assets/js/jquery.caret.js"></script>
        <script src="assets/js/shortcut.js"></script> 
        <script type="text/javascript">
            var ref;
            
            $(document).ready(function() {
                $('#editorWindow').html($('#htmlWindow').val());
                
                
                $('#htmlWindow').keyup (function () {
                   $('#editorWindow').html($('#htmlWindow').val());
                   caretPos = $('#htmlWindow').caret();
                });
                
                
                
                
                $('#bold').click (function () {
                    event.preventDefault(); // cancel default behavior
                    addHTML('<b>');
                })
                
                $('#heading').click (function () {
                    event.preventDefault(); // cancel default behavior
                    addHTML('<h4>');
                })
                
                $('#italic').click (function () {
                    event.preventDefault(); // cancel default behavior
                    addHTML('<i>');
                })
                
                $('#pre').click (function () {
                    event.preventDefault(); // cancel default behavior
                    addHTML('<pre>');
                })
                
                $('#paragraph').click (function () {
                    event.preventDefault(); // cancel default behavior
                    addHTML('<p>');
                })
                
                $('#addRef').click (function () {
                    //Get Id
                    newId = $('[id^=url]').length;  //find size of current array, use that.
                    
                    $('<input>').appendTo('#refEntry')
                                .attr('id','url_'+newId)
                                .attr('type', 'text')
                                .attr('value', 'url'+newId);
                                
                    $('<input>').appendTo('#refEntry')
                                .attr('id','desc_'+newId)
                                .attr('type', 'text')
                                .attr('value', 'description'+newId);            
                    
                });
                
                $('#save').click (function () { 
                    event.preventDefault(); // cancel default behavior

                    //Initialise the reference array
                    var ref = new Array(); //TODO: Test multiple saves don't stack references               

                    //Build the references object
                    $("input[type='text'][id*='url_']").each(function() {
                        //create ref object, if valid entry in reference
                        if ((this.value.slice(0,3)!='url') && ($('#desc_'+this.id.slice(4,5)).val().slice(0,4)!='desc')) {
                            var newRef = new Ref();
                            newRef.url = this.value;
                            newRef.description = $('#desc_'+this.id.slice(4,5)).val();
                            ref.push(newRef);       
                        }
                    });
       
                    $.post("saveblog.php", {'articleId':<?=$articleId?>,
                                            'text':$('#htmlWindow').val(),
                                            'title':$('#title').val(),
                                            'tldr':$('#tldr').val(),
                                            'tags':JSON.stringify($('#tags').val().split(',')),
                                            'references':JSON.stringify(ref)},
                                            function(retVal) {
                                                artPK = retVal.articlePK;
                                                $('#lastSaved').html(retVal.lastUpdate + ' ver:' + artPK);
                                               
                                            },'json');
                                           
                })
                
                $('#preview').click(function() {
                    console.log('clicked');
                    if (artPK) {  //TODO: This doesn't actually work'
                        window.location.href='preview.php?a='+artPK;
                    } else {
                        window.location.href='preview.php?a=<?=$articlePK?>'
                    }
                });
                
                
 
                shortcut.add('ctrl+return', function(){
                    caretPos = $('#htmlWindow').caret();
                    var html = $('#htmlWindow').val().slice(0,caretPos) + '\r' + $('#htmlWindow').val().slice(caretPos);
                    $('#htmlWindow').val(html);
                    $('#htmlWindow').caret(caretPos+1);
                    addHTML('<p>');
                    return true;
                });
                
                shortcut.add('ctrl+p', function () {
                    addHTML('<p>');
                });
                
                shortcut.add('ctrl+b', function () {
                    addHTML('<b>');
                });
                
                shortcut.add('ctrl+h', function () {
                    addHTML('<h4>');
                });
                
                shortcut.add('ctrl+i', function () {
                    addHTML('<i>');
                });
                
                shortcut.add('ctrl+d', function () {
                    addHTML('<pre>');
                });
                
                shortcut.add('ctrl+s', function () {
                    alert('not saving yet TODO');
                    
                },{
                    'propagate':false //Don't launch the browser's ctrl+s, though should be okay on Mac
                });
                
                
            });
            
            
            function addHTML(tag){
                var closingTag = [tag.slice(0, 1), '/', tag.slice(1)].join('');

                //Get current caret position and build new html
                caretPos = $('#htmlWindow').caret();
                var html = $('#htmlWindow').val().slice(0,caretPos) + tag + closingTag + $('#htmlWindow').val().slice(caretPos);

                //Add the tag/closing tag pair at the current cursor position
                $('#htmlWindow').val(html); 
                $('#htmlWindow').caret(caretPos+tag.length);
            }
            
            
            //Reference object defintion
            function Ref() {
                this.url;
                this.description;
            }
            
            
            
        </script>
        <title>Enter Blog</title>
    </head>
    <body>
        <div class="header">The Old Dog Blog</div>
        <div class="main">
            
            <div class="blogText">
                <div class="container">      
                    <div class="lbl">Title</div>
                    <div class="entry"><input type="text" id="title" value="<?=$title?>"></div>
                </div>
                <div class="container">      
                    <div class="lbl">HTML</div>
                    <div class="entry">
                        <button id="heading">Heading<br>(ctrl+h)</button>
                        <button id="bold">Bold<br>(ctrl+b)</button>
                        <button id="italic">Italic<br>(ctrl+i)</button>
                        <button id="paragraph">Paragraph<br>(ctrl+p)</button>
                        <button id="pre">Code<br>(ctrl+d)</button>
                        <textarea id="htmlWindow" name="blogInfo"><?=$html?></textarea>                       
                        <p>
                            <button id="preview" name="preview">Preview</button>
                            <button id="save" name="save">Save <br>(ctrl+s)</button>
                            <span id="lastSaved"></span>
                        </p>
                    </div>                   
                </div>
                <div class="container">      
                    <div class="lbl">Preview</div>
                    <div class="entry"><div id="editorWindow"></div></div>
                </div>
                
                


                <div class="container">      
                    <div class="lbl">tl;dr</div>
                    <div class="entry"><input type="text" id="tldr" value="<?=$tldr?>"></div>
                </div>
                
                <div class="container">      
                    <div class="lbl">Tags</div>
                    <div class="entry"><input type="text" id="tags" value="<?=$tags?>"></div>
                </div>
                <div id="references">
                    <div class="container">      
                        <div class="lbl">References</div>
                        <div class="entry" id="refEntry">
                            <?php
                                if (count($refs) == 0)
                                {
                                    echo '<input type="text" name="refURL" id="url_0" value="url0"/>';
                                    echo '<input type="text" name="refDesc" id="desc_0" value="description0" >';          
                                }
                                else
                                {
                                    for ($x=0; $x<count($refs); $x++)
                                    {
                                        //echo "<h2>$refs[0]->url</h2>";
                                        echo '<input type="text" name="refURL" id="url_'.$x.'" value="'.$refs[$x]->url.'"/>';
                                        echo '<input type="text" name="refDesc" id="desc_'.$x.'" value="'.$refs[$x]->desc.'" >';
                                    }
                                }
                            ?>
                            
                             
                        </div>
                    </div>  
                </div>
                <button id="addRef">Add Reference</input>

            </div>
            <!--<div class="info">
                Contact me at<br>
                <a href="mailto:jonathan@able-futures.com">jonathan@able-futures.com</a>
            </div>-->
        </div>
        
    </body>
</html>


