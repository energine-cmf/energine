ScriptLoader.load(
    'GridManager'
);
var FILE_COOKIE_NAME = 'NRGNFRPID';

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
                    image.setProperty('src', 'images/icons/icon_repository.gif');
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
        var r = this.grid.getSelectedRecord(), control;
        this.toolbar.enableControls();
        var openBtn = this.toolbar.getControlById('open');
        switch (r.upl_internal_type) {
            case 'folder':
                if(openBtn)openBtn.enable();
                break;
            case 'folderup':
                this.toolbar.disableControls();
                if(openBtn)openBtn.enable();
                this.toolbar.getControlById('addDir').enable();
                this.toolbar.getControlById('add').enable()
                break;
            case 'repo':
                this.toolbar.disableControls();
                if(openBtn)openBtn.enable();
                break;
            default:
                //this.toolbar.getControlById('open').disable();
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
        if (this.currentPID)
            Cookie.write(FILE_COOKIE_NAME, this.currentPID, {path:new URI(Energine.base).get('directory'), duration:1});
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

        var controlsEnabledByDefault = ['add'];
        for (var i = 0, l = controlsEnabledByDefault.length; i < l; i++) {
            if (control = this.toolbar.getControlById(controlsEnabledByDefault[i])) control.enable();
        }

        this.grid.build();
        this.overlay.hide();
    },
    open:function () {
        var r = this.grid.getSelectedRecord();
        switch (r.upl_internal_type) {
            case 'repo':
            case 'folder':
                this.currentPID = r.upl_id;
                this.filter.remove();
                this.loadPage(1);
                break;
            case 'folderup':
                this.currentPID = r.upl_id;
                this.loadPage(1);
                break;
            default:
                ModalBox.setReturnValue(r);
                ModalBox.close();
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
            //onClose:this._processAfterCloseAction.bind(this)
            onClose: function(response){
                if(response && response.result){
                    this.currentPID = response.data;
                    this._processAfterCloseAction(response);
                }

            }.bind(this)
        });
    },
    uploadZip: function(data){
        this.request(this.singlePath + 'upload-zip', 'PID='+this.grid.getSelectedRecord().upl_pid+'&data='+encodeURIComponent(data.result), function(response){
            console.log(response)
        });
        //this.singlePath + 'upload-zip',
    },
    loadPage:function (pageNum) {
        this.pageList.disable();
        this.toolbar.disableControls();
        this.overlay.show();
        this.grid.clear();
        var level = '', cookiePID;

        if (this.currentPID === 0) {
            level = '';
        }
        else if (this.currentPID) {
            level = this.currentPID + '/';
        }
        else if (cookiePID = Cookie.read(FILE_COOKIE_NAME)) {
            this.currentPID = cookiePID;
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
