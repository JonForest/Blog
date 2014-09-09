var AbleFutures = AbleFutures || {};
AbleFutures.Blog = AbleFutures.Blog || {};

AbleFutures.Blog.Admin = (function() {
    'use strict';

    var artPK;

    var initEvents = function(articlePK) {
        var deleteArticle,
            caretPos,
            newId;

        artPK = articlePK;

        //Listener for deleteArticle event
        $('.deleteBtn').on(
            'click',
            function() {
                bootbox.confirm(
                    'Are you sure you want to delete this?',
                    function(result) {
                        if (result) {
                            deleteArticle = $.ajax({
                                url: 'admin/article/delete/' + $(this).data('id'),
                                type: 'GET',
                                dataType: 'json',
                            });
                            deleteArticle.done(function() {
                                location.reload();
                            });
                        }
                    }.bind(this)
                );

            }

        );


        $('#htmlWindow').keyup (function () {

            $('#editorWindow').html($('#htmlWindow').val());
            caretPos = $('#htmlWindow').caret();
        });

        $('#bold').click (function () {
            event.preventDefault(); // cancel default behavior
            addHTML('<b>');
        });

        $('#heading').click (function () {
            event.preventDefault(); // cancel default behavior
            addHTML('<h4>');
        });

        $('#italic').click (function () {
            event.preventDefault(); // cancel default behavior
            addHTML('<i>');
        });

        $('#pre').click (function () {
            event.preventDefault(); // cancel default behavior
            addHTML('<pre>');
        });

        $('#paragraph').click (function () {
            event.preventDefault(); // cancel default behavior
            addHTML('<p>');
        });

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
            saveArticle();

        });

        $('#preview').click(function() {
            var articleSaved = saveArticle();

            articleSaved.done(function() {
                window.location = "../../article/preview/" + artPK;
            });
        });



        shortcut.add('ctrl+return', function(){
            var $htmlWindow = $('#htmlWindow'),
                caretPos = $htmlWindow.caret(),
                html = $htmlWindow.val().slice(0,caretPos) + '\r' + $('#htmlWindow').val().slice(caretPos);

            $htmlWindow.val(html);
            $htmlWindow.caret(caretPos+1);
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





    };

    function addHTML(tag){
        var $htmlWindow = $('#htmlWindow'),
            closingTag = [tag.slice(0, 1), '/', tag.slice(1)].join(''),
            caretPos;

        //Get current caret position and build new html
        caretPos = $htmlWindow.caret();
        var html = $htmlWindow.val().slice(0,caretPos) + tag + closingTag + $('#htmlWindow').val().slice(caretPos);

        //Add the tag/closing tag pair at the current cursor position
        $htmlWindow.val(html);
        $htmlWindow.caret(caretPos+tag.length);
    }


    //Reference object defintion
    function Ref() {
        this.url;
        this.description;
    }

    /**
     *
     * @returns {jqXHR}
     */
    function saveArticle() {
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

        var data = {
            'articleId':articleId,
            'text':$('#htmlWindow').val(),
            'title':$('#title').val(),
            'tldr':$('#tldr').val(),
            'tags':JSON.stringify($('#tags').val().split(',')),
            'references':JSON.stringify(ref)
        };

        return $.post(
            "../../article/save/" + articleId,
            data,
            function(retVal) {
                artPK = retVal.articlePK;
                $('#lastSaved').html(retVal.lastUpdate + ' ver:' + artPK);

            },
            'json'
        );
    }

    return {
        initEvents: initEvents
    };

}());




