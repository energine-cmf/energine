CKEDITOR.plugins.add( 'energinevideo', {
    lang: 'en,ru,uk',
    icons: 'energinevideo',
	init: function(editor) {
		editor.addCommand( 'energinevideo', {
            exec: function(editor) {
                var panel = $('cke_' + editor.editorId);
                var zIndex = panel.getStyle('z-index');
                panel.setStyle('z-index', '1');

                ModalBox.open({
                    url: editor.singleTemplate + 'file-library/',
                    onClose: function(fileInfo) {
                        // If user closed modal box without choosing file
                        if (!fileInfo) {
                            panel.setStyle('z-index', zIndex);
                            return;
                        }
                        // If its not video file
                        if('video' !== fileInfo['upl_internal_type']) {
                            alert(Energine.translations.get('TXT_ERROR_NOT_VIDEO_FILE'));
                            panel.setStyle('z-index', zIndex);
                            return;
                        }
                        ModalBox.open({
                            url: editor.singleTemplate + 'file-library/' + fileInfo['upl_id'] + '/put-video/',
                            onClose: function (player) {
                                if (!player) {
                                    panel.setStyle('z-index', zIndex);
                                    return;
                                }
                                var iframe = editor.document.createElement('iframe');
                                iframe.setAttribute('src', Energine.base + 'single/pageToolBar/embed-player/' + fileInfo['upl_id'] + '/');
                                iframe.setAttribute('width', player.width);
                                iframe.setAttribute('height', player.height);
                                iframe.setAttribute('frameborder', '0');
                                editor.insertElement(iframe);
                            }
                        });
                    }
                });
            }
        });

		if (editor.ui.addButton) {
			editor.ui.addButton( 'EnergineVideo', {
				label: editor.lang.energinevideo.toolbar,
				command: 'energinevideo',
				toolbar: 'insert,10'
			});
		}
	}
});
