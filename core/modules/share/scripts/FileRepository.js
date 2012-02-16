ScriptLoader.load(
    'GridManager'
);
Grid.implement({
    iterateFields:function (record, fieldName, row) {
// Пропускаем невидимые поля.
        if (!this.metadata[fieldName].visible ||
            this.metadata[fieldName].type == 'hidden') return;
        var cell = new Element('td').injectInside(row);
        if (fieldName == 'upl_path') {
            cell.setStyles({ 'text-align':'center', 'vertical-align':'middle' });
            var image = new Element('img').setProperties({'width':40, 'height':40 }).injectInside(cell);
            switch (record['upl_internal_type']) {
                case 'folder':
                    image.setProperty('src', 'images/icons/icon_folder.gif');
                    break;
                case 'repo':
                    image.setProperty('src', 'images/icons/icon_undefined.gif');
                    break;
                case 'folderup':
                    image.setProperty('src', 'images/icons/icon_folder_up.gif');
                    break;
                case 'video':
                case 'image':
                    image.setProperty('src', 'resizer/w40-h40/' + record[fieldName]);
                    break;
                default:
                    image.setProperty('src', 'images/icons/icon_undefined.gif');
                    break;
            }

        }
        else {
            var fieldValue = '';
            if (record[fieldName]) {
                var fieldValue = record[fieldName].clean();
            }
            if (fieldValue != '') cell.set('html', fieldValue);
            //if (fieldValue != '') cell.appendText(fieldValue);
            else cell.set('html', '&#160;');
        }
    }
});
var FileRepository = new Class({
    Extends:GridManager,
    initialize:function (element) {
        this.parent(element);
        this.currentPID = false;
    },
    onDoubleClick:function () {
        this.open();
    },
    onSelect:function () {
        var r = this.grid.getSelectedRecord();
        this.toolbar.enableControls();
        switch (r.upl_internal_type) {
            case 'folder':
                this.toolbar.getControlById('open').enable()
                break;
            case 'folderup':
                this.toolbar.disableControls();
                this.toolbar.getControlById('open').enable();
                this.toolbar.getControlById('addDir').enable();
                this.toolbar.getControlById('add').enable()
                break;
            case 'repo':
                this.toolbar.disableControls();
                this.toolbar.getControlById('open').enable()
                break;
            default:
                this.toolbar.getControlById('open').disable();
                break;
        }
    },
    processServerResponse:function (result) {

        if (!this.initialized) {
            this.grid.setMetadata(result.meta);
            this.initialized = true;
        }
        if (!result.data) {
            result.data = [];
        }
        /*if (this.currentPID) {
         result.data.unshift({'upl_id':0, 'upl_internal_type':'folderup', 'upl_path':'', 'upl_pid':'', 'upl_title':'...'});
         }*/
        this.grid.setData(result.data);

        if (result.pager)
            this.pageList.build(result.pager.count, result.pager.current, result.pager.records);


        if (!this.grid.isEmpty()) {
            this.toolbar.enableControls();
            this.pageList.enable();
        }


        if (control = this.toolbar.getControlById('add')) control.enable();
        this.grid.build();
        this.overlay.hide();
    },
    open:function () {
        var r = this.grid.getSelectedRecord();
        switch (r.upl_internal_type) {
            case 'repo':
            case 'folder':
                this.currentPID = r.upl_id;
                this.loadPage(this.pageList.currentPage);
                break;
            case 'folderup':
                this.currentPID = r.upl_id;
                this.loadPage(1);
                break;
        }
    },
    add:function () {
        var pid = this.grid.getSelectedRecord().upl_pid;
        if (pid) {
            pid += '/';
        }
        ModalBox.open({
            url:this.singlePath + pid + 'add/',
            onClose:this._processAfterCloseAction.bind(this)
        });
    },
    addDir:function () {
        var pid = this.grid.getSelectedRecord().upl_pid;
        if (pid) {
            pid += '/';
        }
        ModalBox.open({
            url:this.singlePath + pid + 'add-dir/',
            onClose:this._processAfterCloseAction.bind(this)
        });
    },
    loadPage:function (pageNum) {
        this.pageList.disable();
        this.toolbar.disableControls();
        this.overlay.show();
        this.grid.clear();
        var level = '';
        if (this.currentPID) {
            level = this.currentPID + '/';
        }
        var postBody = '', url = this.singlePath + level + 'get-data/' + 'page-' + pageNum + '/';
        //if (this.langId) postBody += 'languageID=' + this.langId + '&';
        postBody += this.filter.getValue();
        /*if (this.grid.sort.order) {
         url = this.singlePath + 'get-data/' + this.grid.sort.field + '-' +
         this.grid.sort.order + '/page-' + pageNum
         }*/
        this.request(url,
            postBody,
            this.processServerResponse.bind(this),
            null,
            this.processServerError.bind(this)
        );
    }
});
