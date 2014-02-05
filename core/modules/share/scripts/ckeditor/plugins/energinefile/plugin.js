CKEDITOR.plugins.add( 'energinefile', {
    lang: 'en,ru,uk',
    icons: 'energinefile',
	init: function( editor ) {

		editor.addCommand( 'energinefile', {
            exec: function(editor) {

                var panel = $('cke_' + editor.editorId);
                var zIndex = panel.getStyle('z-index');
                panel.setStyle('z-index', '1');

                ModalBox.open({
                    url: editor.singleTemplate + 'file-library',
                    onClose: function (data) {

                        if (!data) {
                            panel.setStyle('z-index', zIndex);
                            return;
                        }

                        var filename = data['upl_path'];

                        if (filename.toLowerCase().indexOf('http://') == -1) {
                            filename = Energine.media + filename;
                        }

                        var style = new CKEDITOR.style({
                            element: 'a',
                            attributes: {
                                'href': filename
                            }
                        });
                        style.type = CKEDITOR.STYLE_INLINE;
                        style.apply(editor.document);

                        if(editor.getSelection().getSelectedText() == '') {
                            editor.insertHtml('<a href = "' + filename + '">' + data['upl_title'] + '</a>');
                        }

                        panel.setStyle('z-index', zIndex);
                    }
                });

            }
        });

		if ( editor.ui.addButton ) {
			editor.ui.addButton( 'EnergineFile', {
				label: editor.lang.energinefile.toolbar,
				command: 'energinefile',
				toolbar: 'insert,10'
			});
		}
	}
});
