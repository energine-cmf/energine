/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[WidgetGridManager]{@link WidgetGridManager}</li>
 * </ul>
 *
 * @requires GridManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('GridManager');

/**
 * WidgetGridManager
 *
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var WidgetGridManager = new Class(/** @lends WidgetGridManager# */{
    Extends: GridManager,

    // constructor
    initialize: function(element){
        this.parent(element);
    },

    // todo: Why insert(), not edit()?
    /**
     * Overridden parent [onDoubleClick]{@link GridManager#onDoubleClick} event handler.
     * @function
     * @public
     */
    onDoubleClick: function() {
        this.insert();
    },

    // todo: What does this method?
    /**
     * Insert the widget.
     * @function
     * @public
     */
    insert: function(){
        ModalBox.setReturnValue(
            new WidgetGridManager.Macros(this.grid.getSelectedRecord().widget_xml)
                .replace({
                    'rand': Math.floor(Math.random() * 10001),
                    'sitename': new URI(window.location.href).get('host').replace(/\./g, ''),
                    'title': this.grid.getSelectedRecord().widget_name
                })
        );
        ModalBox.close();
    }
});

// todo: This is a simple class. Can it be merged to the WidgetGridManager?
/**
 * Замена макросов при вставке нового виджета
 *
 * @constructor
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