CKEDITOR.plugins.add( 'energinefile', {
    lang: 'en,ru,uk',
    icons: 'energinefile',
	init: function( editor ) {

		editor.addCommand( 'energinefile', {
            exec: function(editor) {

                var panel = $('cke_' + editor.editorId);
                panel.hide();

                ModalBox.open({
                    url: editor.singleTemplate + 'file-library',
                    onClose: function (data) {

                        if (!data) {
                            panel.show();
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

                        panel.show();
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
