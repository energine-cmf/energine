ScriptLoader.load('Form.js');

var ProductForm = new Class({
    Extends: Form,
    Implements: [Form.Label, Form.Attachments],
	initialize: function(element){
		this.parent(element);
        this.prepareLabel('show-tree/', !($('product_id').get('value')));
        this.productTypeSelector = $('pt_id');
        //this.paramsRepository = new Hash();
        var loadParamsFunc = function(){
                this.loadParams(
                    this.productTypeSelector.getSelected().getLast().get('value'),
                    $('product_id').get('value')
                );
        }.bind(this);
        
        loadParamsFunc();
        this.productTypeSelector.addEvent(
            'change',
            loadParamsFunc
        );
	},
    attachToolbar : function(toolbar) {
        this.parent(toolbar);
        var afterSaveActionSelect; 
        if(afterSaveActionSelect = this.toolbar.getControlById('after_save_action')){
            var savedActionState = Cookie.read('after_product_add_default_action');
            if(savedActionState){
                afterSaveActionSelect.setSelected(savedActionState);
            }
        }
    },
    loadParams: function(productTypeId, productId){
        var data = 'pt_id='+productTypeId;
        if(!productId){
            productId = 0;
        }
        data += '&product_id=' + productId;
        this.request(
            this.singlePath + 'load-params/',
            data,
            this.buildParamsTable.bind(this)       
        );
    },
    save : function() {
        this.richEditors.each(function(editor) {
                    editor.onSaveForm();
                });
        
        if (!this.validator.validate()) {
            return false;
        }

        this.request(
            this.singlePath + 'save/', 
            this.form.toQueryString(),
                function(response) {
                    if (response.mode == 'insert') {
                        var nextActionSelector;
                        if(nextActionSelector = this.toolbar.getControlById('after_save_action')){
                            Cookie.write('after_product_add_default_action', nextActionSelector.getValue(), {path:new URI(Energine.base).get('directory'), duration:1});
                            switch (nextActionSelector.getValue()){
                                case 'add':
                                    ModalBox.setReturnValue('add'); 
                                    break;
                                case 'close':
                                    ModalBox.setReturnValue(true); 
                                    break;
                            }
                            this.close();
                        }                    
                    }
                    else {
                        ModalBox.setReturnValue(true); this.close();
                    }
                }.bind(this));
    },    
    buildParamsTable: function(response){
        var data = response.data;
        var body = this.componentElement.getElement('#product_params tbody');
        
        body.empty();
        //this.paramsRepository.empty();
        
        var tr, td, controlName;
        data.each(function(param){
            controlName = 'shop_product_param_values[' + param.pp_id + ']';
            tr = new Element('tr');
            tr.grab(new Element('td'));
            tr.grab(new Element('td').set('text', param.pp_name));
            tr.grab(new Element('td').grab(
                new Element('input', 
                    {
                        'type':'text', 
                        'id': param.pp_id, 
                        'value': param.pp_value, 
                        'name': controlName/*,
                        'events':{
                            'change': function(event){
                                var event = new Event(event || window.event);
                                var el = $(event.target);
                                this.paramsRepository.set(
                                    el.getProperty('name'), el.get('value')
                                );
                            }.bind(this)
                    }*/}
                )
            ));
            body.grab(tr);
            //this.paramsRepository.set(controlName, param.ppv_value);
        }, this);
        
    }

});
