ScriptLoader.load('Form');

var GroupForm = new Class({
    Extends: Form,
    initialize:function(element) {
        this.parent(element);
        this.componentElement.getElements('.groupRadio').addEvent('click', this.checkAllRadioInColumn);
//        this.componentElement.getElements('input[type=radio]').addEvent('change', this.uncheckGroupRadio);
    },
    checkAllRadioInColumn:function(event) {
        var radio = $(event.target);
        radio.getParent('tbody').getElements('td.' +
                radio.getParent('td').getProperty('class') +
                ' input[type=radio]').setProperty('checked', 'checked');
    },
    uncheckGroupRadio: function(event) {
        if (!radio.hasClass('groupRadio')) {
            //console.log(radio.getParent('tbody').getElement('tr.section_name td.' + radio.getParent('td').getProperty('class') + ' input[type=radio]'));
            radio.getParent('tbody').getElement('tr.section_name td.' +
                    radio.getParent('td').getProperty('class') +
                    ' input[type=radio]').removeProperty('checked');
        }
    }
});