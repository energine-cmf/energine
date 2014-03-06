CKEDITOR.plugins.add( 'energinevideo', {
    lang: 'en,ru,uk',
    icons: 'energinevideo',
	init: function(editor) {
        editor.addCommand('videoPropertiesDialog', new CKEDITOR.dialogCommand('videoPropertiesDialog'));
        CKEDITOR.dialog.add('videoPropertiesDialog', this.path + 'dialogs/properties.js');
		editor.addCommand( 'energinevideo', {
            exec: function(editor) {
                var panel = $('cke_' + editor.editorId);
                var zIndex = panel.getStyle('z-index');
                panel.setStyle('z-index', '1');

                ModalBox.open({
                    url: editor.singleTemplate + 'file-library/',
                    onClose: function(fileInfo) {
                        if (!fileInfo) {
                            panel.setStyle('z-index', zIndex);
                            return;
                        }
                        editor.execCommand('videoPropertiesDialog', fileInfo);
                    }
                });
            }
        });

		if ( editor.ui.addButton ) {
			editor.ui.addButton( 'EnergineVideo', {
				label: editor.lang.energinevideo.toolbar,
				command: 'energinevideo',
				toolbar: 'insert,10'
			});
		}
	}
});
