ScriptLoader.load('GridManager');
var WidgetGridManager = new Class({
    Extends: GridManager,
    initialize: function(element){
        this.parent(element);
    },
    onDoubleClick: function() {
        this.insert();
    },
    insert: function(){
        ModalBox.setReturnValue(
            new WidgetGridManager
                .Macros(this.grid.getSelectedRecord().widget_xml)
                .replace({
                    'rand': Math.floor(Math.random() * 10001),
                    'sitename': new URI(window.location.href).get('host').replace(/\./g, ''),
                    'title': this.grid.getSelectedRecord().widget_name
                })
        );
        ModalBox.close();
    }
});

/**
 * Замена макросов при вставке нового виджета
 */
WidgetGridManager.Macros = new Class({
    initialize: function (xml_string) {
        this.xml_string = xml_string;
    },

    replace: function (patterns) {
        var result = this.xml_string;
        Object.each(patterns, function(rule, pattern){
             result = result.replace(new RegExp("\\[" + pattern + "\\]", 'g'), rule.toString());
        }.bind(this));
        return result;
    }
});