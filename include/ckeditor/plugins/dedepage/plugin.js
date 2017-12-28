// Register a plugin named "dedepage".
(function()
{
    CKEDITOR.plugins.add( 'dedepage',
    {
        init : function( editor )
        {
            // Register the command.
            editor.addCommand( 'dedepage',{
                exec : function( editor )
                {
                    // Create the element that represents a print break.
                    // alert('dedepageCmd!');
                    editor.insertHtml("#p#������#e#");
                }
            });
            // alert('dedepage!');
            // Register the toolbar button.
            editor.ui.addButton( 'MyPage',
            {
                label : '�����ҳ��',
                command : 'dedepage',
                icon: 'images/dedepage.gif'
            });
            // alert(editor.name);
        },
        requires : [ 'fakeobjects' ]
    });
})();
