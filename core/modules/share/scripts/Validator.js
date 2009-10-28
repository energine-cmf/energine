var Validator = new Class({

    initialize: function(form, tabPane) {
        this.form = $(form);
        this.tabPane = tabPane || null;
        this.prepareFloatFields();
    },
    prepareFloatFields: function(){
		var prepareFunction = function(event){
        		event= new Event(event || window.event);
        		event.target.value = event.target.value.replace(/\,/, '.');
        };
	    //Для всех field type=float(class=float)
        //меняем , на .
        this.form.getElements('.float').each(function(element){
        	element.removeEvent('change', prepareFunction);
        	element.addEvent('change', prepareFunction);
        });
	},
    removeError: function(field){
        if (field.hasClass('invalid')) {
            field.removeClass('invalid');
            var errorDiv;
            if(errorDiv = field.getParent().getParent().getElement('div.error')){
                errorDiv.dispose();
            }
        }    
    },
	showError: function(field, message){
        this.removeError(field);
        field.addClass('invalid');
        new Element('div').addClass('error').appendText('^ ' + message).injectAfter(field.parentNode);
	},
	scrollToElement: function(field){
		var scroll = new Fx.Scroll(window, {
			offset: {'x': -30, 'y': -20},
			transition: Fx.Transitions.linear
		});

		scroll.toElement(field).chain(function(){field.focus()});
	},
    validateElement: function(field){
        var result = true;
        var pattern, message;
        field = $(field);
        if (
            (pattern = field.getProperty('nrgn:pattern')) 
            && 
            (message = field.getProperty('nrgn:message')) 
            && 
            !field.getProperty('disabled')
        ) {
            if (!eval('field.value.match('+pattern+');')) {
                //Выводим информацию об ошибке
                this.showError(field, message);
                result = false;
            }
            else{
                //Убираем информацию о предыдущей ошибке
                this.removeError(field);
            }
        }    
        return result;
    },
    validate: function() {
		//Массив ошибочных полей
        var errorFields =[];
        
        //заполняем массив ошибочных полей
        new Elements(this.form.elements).each(function(field){
            if(!this.validateElement(field)){
                errorFields.push(field);       
            }
        }, this);
        
        //Если есть ошибки
        if(errorFields.length){
            //Нас интересует только первое поле
            var firstField = errorFields[0];
            //Если мы внутри табов
            //определяем таб первого ошибочного поля и переключаемся на этот таб
            if(this.tabPane){
                this.tabPane.show(this.tabPane.whereIs(firstField))
            }
            //Скроллируем
            this.scrollToElement(firstField);
            try {
                firstField.focus()
            }
            catch (e) {};
        }

        return !(errorFields.length);
    }
});