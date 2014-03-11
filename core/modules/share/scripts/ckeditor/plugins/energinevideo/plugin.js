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
                        if (!fileInfo) {
                            panel.setStyle('z-index', zIndex);
                            return;
                        }
                        ModalBox.open({
                            url: editor.singleTemplate + 'file-library/' + fileInfo['upl_id'] + '/put-video/',
                            onClose: function (player) {
                                var iframe = editor.document.createElement('iframe'),
                                    div = editor.document.createElement('div');
                                div.setAttribute('class', 'video');
                                iframe.setAttribute('src', Energine.base + 'single/pageToolBar/embed-player/' + fileInfo['upl_id'] + '/');
                                iframe.setAttribute('width', player.width);
                                iframe.setAttribute('height', player.height);
                                iframe.setAttribute('frameborder', '0');
                                iframe.appendTo(div);
                                editor.insertElement(div);
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
