CKEDITOR.plugins.add( 'energineimage', {
    lang: 'en,ru,uk',
    icons: 'energineimage',
	init: function( editor ) {

		editor.addCommand( 'energineimage', {
            exec: function(editor) {
                var panel = $('cke_' + editor.editorId);
                var zIndex = panel.getStyle('z-index');
                panel.setStyle('z-index', '1');

                ModalBox.open({
                    url: editor.singleTemplate + 'file-library/',
                    onClose: function(imageData) {

                        if (!imageData) {
                            panel.setStyle('z-index', zIndex);
                            return;
                        }

                        ModalBox.open({
                            url: editor.singleTemplate + 'imagemanager',
                            onClose: function (image) {
                                if (!image) {
                                    panel.setStyle('z-index', zIndex);
                                    return;
                                }

                                if (image.filename.toLowerCase().indexOf('http://') == -1) {
                                    image.filename = Energine.media + image.filename;
                                }

                                var imgStr = '<img src="'
                                    + image.filename + '" width="'
                                    + image.width + '" height="'
                                    + image.height + '" align="'
                                    + image.align + '" alt="'
                                    + image.alt + '" border="0" style="';

                                ['margin-left', 'margin-right', 'margin-top', 'margin-bottom'].each(function (marginProp) {
                                    if (image[marginProp] != 0) {
                                        imgStr += marginProp + ':' + image[marginProp] +
                                            'px;';
                                    }
                                });

                                imgStr += '"/>';

                                editor.insertHtml(imgStr);
                                panel.setStyle('z-index', zIndex);
                            },
                            extraData: imageData
                        });
                    }
                });
            }
        });

		if ( editor.ui.addButton ) {
			editor.ui.addButton( 'EnergineImage', {
				label: editor.lang.energineimage.toolbar,
				command: 'energineimage',
				toolbar: 'insert,10'
			});
		}
	}
});
