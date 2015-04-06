/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[DivTree]{@link DivTree}</li>
 * </ul>
 *
 * @requires DivManager
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('DivManager');

/**
 * DivTree.
 *
 * @augments DivManager
 *
 * @constructor
 * @param {Element|string} el The main holder element.
 */
var DivTree = new Class(/** @lends DivTree# */{
    Extends: DivManager,

    /**
     * Current ID.
     * @type {number}
     */
    currentID: 0,

    // constructor
    initialize: function (el) {
        this.parent(el);

        var iframes = window.top.document.getElementsByTagName('iframe'),
            srcWindows = [window.top],
            result = false,
            i;

        for (i = 0; i < iframes.length; i++) {
            if (iframes[i].contentWindow) {
                srcWindows.push(iframes[i].contentWindow);
            }
        }

        for (i = 0; i < srcWindows.length; i++) {
            try {
                result = srcWindows[i].document.getElementById('smap_id');
                if (result) {
                    this.currentID = result.value.toInt();
                    break;
                }
            }
            catch (e) {
            }
        }
    },

    /**
     * Extend parent [onSelectNode]{@link DivManager#onSelectNode} method.
     *
     * @function
     * @public
     * @param {TreeView.Node} node Node that will be selected.
     */
    onSelectNode: function (node) {
        this.parent(node);

        var btnSelect = this.toolbar.getControlById('select');
        if (this.currentID) {
            if (this.currentID == node.id) {
                if (btnSelect) {
                    btnSelect.disable();
                }
            } else {
                var p = node.getParents(), l;
                if (l = p.length) {
                    for (var i = 0; i < l; i++) {
                        if (p[i].id == this.currentID) {
                            if (btnSelect) {
                                btnSelect.disable();
                            }
                            break;
                        }
                    }
                }
            }
        } else {
            if (btnSelect) {
                btnSelect.enable();
            }
        }
    }
});
