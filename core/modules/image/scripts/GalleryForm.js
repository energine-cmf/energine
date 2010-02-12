ScriptLoader.load('Form');
var GalleryForm = new Class({
    Extends:Form,
    initialize: function(element){
        this.parent(element);
    },
    openFileLib : function(button) {
        var path = $($(button).getProperty('link')).get('value');
        if (path == '') {
            path = null;
        }
        ModalBox.open({
                    url : this.singlePath + 'file-library/image-only/',
                    extraData : path,
                    onClose : function(result) {
                        if (result) {
                            button = $(button);
                            $(button.getProperty('link')).value = result['upl_path'];
                            if (image = $(button.getProperty('preview'))) {
                                image.setProperty('src', result['upl_path']);
                            }
                        }
                    }
                });
    }
});