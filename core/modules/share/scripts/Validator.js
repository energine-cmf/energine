var Validator = new Class({

    initialize: function(form, tabPane) {
        this.form = $(form);
        this.tabPane = tabPane || null;
    },
	showError: function(field, message){
		if (!field.hasClass('invalid')) {
	        field.addClass('invalid');
	        new Element('div').addClass('error').appendText('^ ' + message).injectAfter(field.parentNode);
	    }
	},
	scrollToElement: function(field){
		var scroll = new Fx.Scroll(window, {
			offset: {'x': -30, 'y': -20},
			transition: Fx.Transitions.linear
		});

		scroll.toElement(field).chain(function(){field.focus()});
	},
    validate: function() {
		var firstFailure = null;
		var failed = false;
		var firstTab = null;

        for (var i = 0; i < this.form.elements.length; i++) {
            var field = $(this.form.elements[i]);
            if (field.getProperty('pattern') && field.getProperty('message')) {
                if (field.hasClass('invalid')) {
                    field.removeClass('invalid');
                    var errorDiv;
                    if(errorDiv = $E('div.error', field.parentNode.parentNode)){
                        errorDiv.remove();
                    }
                }

                if (!eval('field.value.match('+field.getProperty('pattern')+');')) {
                    if (this.tabPane && !firstTab) {
                        firstTab = this.tabPane.whereIs(field);
                    }
					if (this.tabPane) {
                        var tab = this.tabPane.whereIs(field);
                    }
                    this.showError(field, field.getProperty('message'));

					if (!firstFailure) {
						firstFailure = field;
					}

					failed = true;
                }
                else {
                    field.removeClass('invalid');
                    var error = $(field.parentNode).getElement('div.error');
                    if (error) error.remove();
                }
            }
        }
		if (failed) {

			failed = false;

			if (firstTab) {
				this.tabPane.show(firstTab);
			}
			if (firstFailure) {

				if (this.tabPane) {
					try {
						firstFailure.focus()
					}
					catch (e) {};

				} else {
					this.scrollToElement(firstFailure);
				}

			}

			this.firstFailure = null;
			firstTab = null;

			return false;

		} else {
			/*inserting cleaner for Opera*/
			if (window.opera) {
				/*getting all fields with pattern attribute*/
				$ES('input[pattern], textarea[pattern]', this.form).each(function(item){
				   item.removeProperty('pattern');
				});
			}

			return true;
		}
    }
});