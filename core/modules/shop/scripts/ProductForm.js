ScriptLoader.load('Form.js');

var ProductForm = new Class({
    Extends: Form,
    Implements: [Form.Label, Form.Attachments],
	initialize: function(element){
		this.parent(element);
        this.prepareLabel('show-tree/', !($('product_id').get('value')));
        this.productTypeSelector = $('pt_id');

        var loadParamsFunc = function(){
            var f = this.productTypeSelector.getSelected();
            if(f.length)
                this.loadParams(
                    f.getLast().get('value'),
                    $('product_id').get('value')
                );
        }.bind(this);
        
        loadParamsFunc();
        this.productTypeSelector.addEvent(
            'change',
            loadParamsFunc
        );
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
