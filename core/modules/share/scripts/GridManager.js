ScriptLoader.load(
        'Grid',
        'TabPane',
        'PageList',
        'Toolbar',
        'Overlay',
        'ModalBox'
        );

var GridManager = new Class({
    Implements:Energine.request,
    initialize: function(element) {
        this.element = element;

        this.filter = new GridManager.Filter(this);

        this.tabPane =
                new TabPane(this.element, { onTabChange: this.onTabChange.bind(this) });

        this.grid = new Grid(this.element.getElement('.grid'), {
            onSelect: this.onSelect.bind(this),
            onSortChange: this.changeSort.bind(this),
            onDoubleClick: this.onDoubleClick.bind(this)
        });
        this.pageList =
                new PageList({ onPageSelect: this.loadPage.bind(this) });
        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.pageList.getElement());
            this.tabPane.element.removeClass('e-pane-has-b-toolbar1');
            this.tabPane.element.addClass('e-pane-has-b-toolbar2');
        }
        else {
            this.tabPane.element.adopt(this.pageList.getElement());
        }
        this.overlay = new Overlay(this.element);
        this.singlePath = this.element.getProperty('single_template');
    },

    attachToolbar: function(toolbar) {
        this.toolbar = toolbar;
        var toolbarContainer = this.tabPane.element.getElement('.e-pane-b-toolbar');
        if (toolbarContainer) {
            toolbarContainer.adopt(this.toolbar.getElement());
        }
        else {
            this.tabPane.element.adopt(this.toolbar.getElement());
        }
        this.toolbar.disableControls();
        toolbar.bindTo(this);
        /*
         * Панель инструментов прикреплена, загружаем первую страницу.
         *
         * Делаем секундную задержку для надёжности:
         * пусть браузер распарсит стили и просчитает размеры элементов.
         */
        //this.reloadGrid.delay(1000, this);
    },

    onTabChange: function(tabData) {
        this.langId = tabData.lang;
        // Загружаем первую страницу только если панель инструментов уже прикреплена.
        this.filter.remove();
        this.reloadGrid();
    },

    onSelect: function() {

    },
    onDoubleClick: function() {
        this.edit();
    },
    changeSort: function() {
        this.loadPage.delay(10, this, 1);
    },

    reloadGrid: function() {
        this.loadPage.delay(10, this, 1);
    },

    loadPage: function(pageNum) {
        this.pageList.disable();

        this.toolbar.disableControls();
        this.overlay.show();
        this.grid.clear();
        var postBody = '', url = this.singlePath + 'get-data/page-' + pageNum;
        if (this.langId) postBody += 'languageID=' + this.langId + '&';
        postBody += this.filter.getValue();
        if (this.grid.sort.order) {
            url = this.singlePath + 'get-data/' + this.grid.sort.field + '-' +
                    this.grid.sort.order + '/page-' + pageNum
        }
        this.request(url,
                postBody,
                this.processServerResponse.bind(this),
                null,
                this.processServerError.bind(this)
                );
    },
    processServerResponse: function(result) {
        if (!this.initialized) {
            this.grid.setMetadata(result.meta);
            this.initialized = true;
        }
        this.grid.setData(result.data || []);
        this.grid.build();

        if (result.pager)
            this.pageList.build(result.pager.count, result.pager.current);

        this.overlay.hide();

        if (this.grid.isEmpty()) {
            if (control = this.toolbar.getControlById('add')) control.enable();
        }
        else {
            this.toolbar.enableControls();
            this.pageList.enable();
        }
    },
    processServerError: function(responseText) {
        alert(responseText);
        this.overlay.hide();
    },
    // Actions:

    view: function() {
        ModalBox.open({ url: this.singlePath +
                this.grid.getSelectedRecordKey() });
    },

    add: function() {
        ModalBox.open({
            url: this.singlePath + 'add/',
            onClose: function(returnValue) {
                if (returnValue) {
                    if (returnValue.afterClose == 'add') {
                        this.add();
                    }
                    else {
                        this.reloadGrid();
                    }
                }
            }.bind(this)
        });
    },

    edit: function() {
        ModalBox.open({
            url: this.singlePath + this.grid.getSelectedRecordKey() + '/edit',
            onClose: this.loadPage.pass(this.pageList.currentPage, this)
        });
    },

    del: function() {
        var MSG_CONFIRM_DELETE = Energine.translations.get('MSG_CONFIRM_DELETE') ||
                'Do you really want to delete selected record?';
        if (confirm(MSG_CONFIRM_DELETE)) {
            this.request(this.singlePath + this.grid.getSelectedRecordKey() +
                    '/delete/', null, this.loadPage.pass(this.pageList.currentPage, this));
        }
    },

    close: function() {
        ModalBox.close();
    },
    up: function() {
        this.request(this.singlePath + this.grid.getSelectedRecordKey() +
                '/up/', '', this.loadPage.pass(this.pageList.currentPage, this));
    },

    down: function() {
        this.request(this.singlePath + this.grid.getSelectedRecordKey() +
                '/down/', '', this.loadPage.pass(this.pageList.currentPage, this));
    },
    print: function() {
        window.open(this.element.getProperty('single_template') + 'print/');
    },
    csv: function() {
        document.location.href =
                this.element.getProperty('single_template') + 'csv/';
    }
});

GridManager.Filter = new Class({
    initialize:function(gridManager) {
        this.gm = gridManager;
        this.element = this.gm.element.getElement('.filter');
        this.fields = false;
        this.inputs = false;
        this.active = false;
        if (this.element) {
            var applyButton = this.element.getElement('.f_apply'), resetLink = this.element.getElement('.f_reset');
            this.fields = this.element.getElement('.f_fields');
            applyButton.addEvent('click', function() {
                this.use();
                this.gm.reloadGrid.apply(this.gm);
            }.bind(this));
            resetLink.addEvent('click', function(e) {
                Energine.cancelEvent(e);
                this.remove();
                this.gm.reloadGrid.apply(this.gm);
            }.bind(this));

            this.inputs =
                    new GridManager.Filter.QueryControls(this.element.getElements('.f_query_container'), applyButton);
            this.condition = this.element.getElement('.f_condition');

             /*var prepareInputs = function(){
             //this.inputs.empty();
             if(['date', 'datetime', 'time'].contains(this.fields.options[this.fields.selectedIndex].getAttribute('type'))){
                this.inputs.asDateSelector();
             }
             else {
                this.inputs.asTextSelector();
             }
             }.bind(this);
             prepareInputs();

             this.fields.addEvent('change', prepareInputs);*/


            this.condition.addEvent('change', function(event) {
                //prepareInputs();
                var condition = $(event.target).get('value');

                if (condition == 'between') {
                    this.inputs.asPeriod();
                }
                else {
                    this.inputs.asScalar();
                }
            }.bind(this));
        }
    },
    remove: function() {
        if (this.element) {
            this.inputs.empty();
            this.element.removeClass('active');
            this.active = false;
        }
    },
    use: function() {
        var reloadOnExit = true;
        if (this.inputs.hasValues()) {
            this.element.addClass('active');
            this.active = true;
        }
        else if (this.active) {
            this.remove();
        }
        else {
            reloadOnExit = false;
        }

        return reloadOnExit;
    },
    getValue: function() {
        var result = '';
        if (this.active && this.inputs.hasValues()) {
            var
                    fieldName = this.fields.options[this.fields.selectedIndex].value,
                    fieldCondition = this.condition.options[this.condition.selectedIndex].value;
            result = this.inputs.getValues('filter' + fieldName) +
                    '&filter[condition]=' + fieldCondition + '&';
        }
        return result;
    }
});

GridManager.Filter.QueryControls = new Class({
    initialize: function(els, applyAction) {
        this.containers = els;
        this.inputs = [];
        this.dps = [];
        this.containers.each(function(el) {
            this.inputs.push(el.getElement('input'));
            this.dps.push(el.getElement('.f_datepicker'));
        }.bind(this));

        this.inputs = new Elements(this.inputs);
        this.dps = new Elements(this.dps);
        /*this.dps.each(function(el, index){
            Energine._createDatePickerObject(el, {
                format:'j-m-Y',
                allowEmpty: true,
                inputOutputFormat: 'Y-m-d',
                toggleElements: this.inputs[index]
            })
        }.bind(this));*/


        this.inputs.addEvent('keydown', function(event) {
            event = new Event(event);
            if ((event.key == 'enter') && (event.target.value != '')) {
                Energine.cancelEvent(event);
                applyAction.click();
            }
        });
    },
    hasValues: function() {
        return this.inputs.some(function(el) {
            return ($(el)) ? el.get('value') : false
        });
    },
    empty:function() {
        this.inputs.each(function(el) {
            el.set('value', '')
        });
    },
    getValues: function(fieldName) {
        var str = '';
        this.inputs.each(function(el, index, els) {
            if (el.get('value')) str += fieldName + '[]=' + el.get('value');
            if (index != (els.length - 1)) str += '&';
        });
        return str;
    },
    asDateSelector: function() {
        //this.dps.removeClass('hidden').setStyle('display', '');
    },
    asTextSelector:function() {
        //this.dps.addClass('hidden');
    },
    asPeriod: function() {
        this.containers.removeClass('hidden');
        this.inputs.addClass('small');
    },
    asScalar: function() {
        this.containers[1].addClass('hidden');
        this.inputs.removeClass('small');
    }
});
