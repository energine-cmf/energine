ScriptLoader.load(
    'GridManager.js'
);

var GalleryGrid = GridManager.extend({
	initialize: function(element){
        this.parent(element);
    },
	showGallery:function(){
	 ModalBox.open({
            url: this.element.getProperty('single_template') + '' + this.grid.getSelectedRecordKey() + '/show-gallery/'
        });	
	}
});