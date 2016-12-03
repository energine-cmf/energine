/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[DivForm]{@link DivForm}</li>
 * </ul>
 *
 * @requires Form
 * @requires ModalBox
 *
 * @author Pavel Dubenko, Valerii Zinchenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('Form', 'ModalBox');

/**
 * DivForm.
 *
 * @augments Form
 *
 * @borrows Form.Label.setLabel as DivForm#setLabel
 * @borrows Form.Label.prepareLabel as DivForm#prepareLabel
 * @borrows Form.Label.restoreLabel as DivForm#restoreLabel
 * @borrows Form.Label.showTree as DivForm#showTree
 *
 * @constructor
 * @param {Element|string} element The form element.
 */
var DivForm = new Class(/** @lends DivForm# */{
    Extends: Form,

    // constructor
    initialize: function (element) {
        this.parent(element);
        this.prepareLabel($('site_id').get('value') + '/list/');

        var contentSelector = this.element.getElementById('smap_content'),
            layoutSelector = this.element.getElementById('smap_layout'),
            segmentInput = this.element.getElementById('smap_segment'),
            contentFunc;

        //чтоб ради одного вызова не биндится на this
        var t = this;

        contentFunc = function () {
            var segment, layout;

            if (segmentInput) {
                if (segment = contentSelector.getSelected()[0].getProperty('data-segment')) {
                    segmentInput.setProperty('readOnly', 'readOnly');
                    segmentInput.set('value', segment);
                }
                else {

                    segmentInput.removeProperty('readOnly');
                }
            }

            if ((layout = contentSelector.getSelected()[0].getProperty('data-layout')) && (layout != '*')/* && (!new Boolean(layoutSelector.get('value').toInt()).valueOf())*/) {
                layoutSelector.set('value', layout);
            }

            t.clearContentXML();
        };
        contentSelector.addEvent('change', contentFunc);
    },

    /**
     * Reset the page content template.
     * @function
     * @public
     */
    resetPageContentTemplate: function () {
        this.request(
            this.singlePath + 'reset-templates/' + this.element.getElementById('smap_id').get('value') + '/',
            null,
            function (response) {
                if (response.result) {
                    var select = this.element.getElementById('smap_content'),
                        option = select.getChildren()[select.selectedIndex],
                        optionText = option.get('text');

                    option.set('text', optionText.substring(0, optionText.lastIndexOf('-')));
                    this.clearContentXML();
                }
            }.bind(this)
        )
    },

    /**
     * Clear XML content.
     * @function
     * @public
     */
    clearContentXML: function () {
        if (this.codeEditors.length) {
            //Тут мы перполагаем что на форме только одно поле типа код... Пока что это так
            this.codeEditors[0].setValue('');
            this.codeEditors[0].getInputField().getParent('div.field').addClass('hidden');
        }
    },

    /**
     * Overridden parent [save]{@link Form#save} action.
     * @function
     * @public
     */
    save: function () {
        this.richEditors.each(function (editor) {
            editor.onSaveForm();
        });
        this.codeEditors.each(function (editor) {
            editor.save();
        });
        if (!this.validator.validate()) {
            return false;
        }

        var tabs = this.tabPane.getTabs();
        var valid = true;
        tabs.each(function (tab) {
            if (tab.data.lang) {
                var checkbox = tab.pane.getElement('input[type="checkbox"]');
                if(checkbox) {
                    var disabled = checkbox.name.test(/share_sitemap_translation\[\d+\]\[smap_is_disabled\]/) ? checkbox.checked : false;
                    if (!disabled) {
                        if (tab.pane.getElement('input[type="text"]').value.trim().length == 0) {
                            valid = false;
                        }
                    }
                }
            }
        });


        if (!valid) {
            alert(Energine.translations.get('ERR_NO_DIV_NAME'));
            return false;
        }
        this.request(
            this.singlePath + 'save',
            this.form.toQueryString(),
            this.processServerResponse.bind(this)
        );
    }
});
DivForm.implement(Form.Label);