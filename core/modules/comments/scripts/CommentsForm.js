/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[CommentsForm]{@link CommentsForm}</li>
 * </ul>
 *
 * @requires share/Energine
 * @requires share/ValidForm
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.0
 */

ScriptLoader.load('ValidForm', 'Overlay');

// todo: FOR ALL TODOs: leave as is.
/**
 * CommentsForm
 *
 * @augments ValidForm
 *
 * @constructor
 * @param {Element|string} element The main element.
 */
var CommentsForm = new Class(/** @lends CommentsForm# */{
    Extends: ValidForm,

    /**
     * @see Energine.request
     * @deprecated Use Energine.request instead.
     */
    request: Energine.request,

    /**
     * Maximum symbols.
     * @type {number}
     */
    maxSymbol: 250,

    //todo: What is it?
    /**
     * @type {Array}
     */
    trans: [],

    // constructor
    initialize : function(element) {
        Asset.css('comments.css');

        this.parent(element);

        if (this.componentElement && this.form) {
            this.form.addClass('form');

            $$('div.comments div.comment_inputblock a.link_comment').addEvent('click', this.show_form_base.bind(this));
            this.form.getElement('a.btn_comment').addEvent('click', this.validateForm.bind(this));
            this.form.getElement('a.btn_cancel').addEvent('click', this.show_form_base.bind(this));
            this.form.getElement('textarea').addEvent('keyup', this.countOut.bind(this));

            $$('li.comment_item div.comment_inputblock').each(function(el) {
                el.getElements('a.btn_edit').addEvent('click', this.editComment.bind(this));
                el.getElements('a.btn_delete').addEvent('click', this.deleteComment.bind(this));
                el.getElements('a.btn_comment').addEvent('click', this.show_form.bind(this));
            }.bind(this));
        }
    },

    /**
     * Overridden parent [validateForm]{@link ValidForm#validateForm} method.
     * @function
     * @public
     * @param {Object} event Event.
     */
    validateForm: function(event) {
        if (this.parent(event)) {
            this.showOverlay();
            event.stop()

            Energine.request(
                this.singlePath + 'save-comment/',
                this.form.toQueryString(),
                function(response) {
                    this.overlay.hide();
                    if (response.mode == 'update') {
                        var li = $$('li.comment_item[id=' + response.data.comment_id + '_comment]');
                        li.getElement('div.hidden.comment_text').set('html', response.data.comment_name);

                        this.show_form_base(true);
                        this.form.getElement('textarea').set('value', '');
                    } else {
                        this.show_result(response);
                    }
                }.bind(this),
                function(response) {
                    this.overlay.hide();
                }.bind(this)
            );
        }
    },

    /**
     * Show result.
     *
     * @fires CommentsForm#keyup
     *
     * @function
     * @public
     * @param {Object} response Server response.
     */
    show_result: function(response) {
        try {
            Recaptcha.reload();
        } catch (err) {
            console.warn(err)
        }

        if (response.errors) {
            alert(response.errors);
        } else if (response.data) {
            var item = response.data[0];

            var li = $$('li.comment_item.hidden')[0].clone().removeClass('hidden');
            li.setAttribute('id', item['comment_id'] + '_comment');
            li.getElement('div.comment_text').set('html', item['comment_name']);
            li.getElement('div.comment_username').set('text', item['u_nick']);
            li.getElement('div.comment_date').set('text', item['comment_created']);

            if (li.getElement('div.comment_inputblock')) {
                if (item['is_tree']) {
                    li.getElement('div.comment_inputblock .btn_comment').addEvent('click', this.show_form.bind(this));
                } else {
                    li.getElement('div.comment_inputblock').addClass('hidden');
                }

                if (item['u_id']) {
                    li.getElements('a.btn_edit').addEvent('click', this.editComment.bind(this));
                    li.getElements('a.btn_delete').addEvent('click', this.deleteComment.bind(this));
                } else {
                    li.getElements('a.btn_edit').addClass('hidden');
                    li.getElements('a.btn_delete').addClass('hidden');
                }
            }

            if (item['comment_parent_id']) {
                var parentCommentLiName = item['comment_parent_id'] + '_comment',
                    parentCommentLi = $$('div.comments ul li#' + parentCommentLiName + ''),
                    ul = parentCommentLi.getElement('ul');

                if (!ul[0]) {
                    ul = new Element('ul');
                    ul.addClass('comment_list');

                    var d = new Element('div');
                    d.addClass('comment_thread').grab(ul);

                    var i = new Element('i', {'class': 'icon20x20 comment_thread_icon'});
                    i.grab(new Element('i'));
                    d.grab(i);
                    parentCommentLi.grab(d);
                }
                ul.grab(li);
            } else {
                $$('div.comments').show().getElement('ul').grab(li);
            }

            $$('div.comments span')[0].innerHTML = '(' + ($$('div.comments ul li').length - 1) + ')';

            if (li.getPrevious('li') && !(li.getPrevious('li').hasClass('hidden'))) {
                li.getPrevious('li').removeClass('last_item');
            } else {
                li.addClass('first_item');
            }

            if (!(li.getNext('li'))) {
                li.addClass('last_item');
            }

            this.form.addClass('hidden');
            $$('ul.comment_list div.comment_inputblock').removeClass('hidden');

            var t = this.componentElement.getElement('textarea[name=comment_name]');
            t.value = '';

            /**
             * Key up event.
             * @event CommentsForm#keyup
             * @param {{target}} anonym Object withe 'target' property
             * @param {number} 1
             */
            t.fireEvent('keyup', {target: t}, 1);

            new Fx.Scroll(
                document.getElement('.e-mainframe')
                    ? document.getElement('.e-mainframe')
                    : window
            ).toElement(li);
        }
    },

    /**
     * Event handler. Show form.
     *
     * @function
     * @public
     * @param {Event} event Event.
     */
    show_form: function(event) {
        this.preShowForm();

        var li = $(event.target).getParent('li'),
            text = li.getElement('div.comment_text');

        this.form.inject(text, 'after');
        this.form.getElement('textarea').focus();

        li.getChildren('div.comment_inputblock').addClass('hidden');

        var parentId = this.form.getElement('input[name="parent_id"]');
        if (!parentId) {
            parentId = new Element('input', {'type':'hidden', 'name':'parent_id'});
            this.form.grab(parentId);
        }
        parentId.setProperty('value', parseInt(event.target.getParent('li').id));
        this.form.getElements('div.comment_controlset a.btn_cancel.hidden.').removeClass('hidden');
    },

    /**
     * Show base form.
     *
     * @function
     * @public
     * @param {boolean} notSetFocus
     */
    show_form_base: function(notSetFocus) {
        this.preShowForm();

        this.form.getElements('div.comment_controlset a.btn_cancel').addClass('hidden');

        var parentId = this.form.getElement('input[name="parent_id"]');
        if (parentId) {
            parentId.dispose();
        }

        $$('div.comments').grab(this.form);

        if (!notSetFocus) {
            this.form.getElement('textarea').focus();
        }
    },

    /**
     * Function before showing a form.
     * @function
     * @public
     */
    preShowForm: function() {
        this.setFormCId(0);
        this.form.getElement('textarea').set('value', '');
        this.form.removeClass('hidden');

        $$('li.comment_item div.hidden.comment_inputblock').removeClass('hidden');
        $$('li.comment_item div.comment_text').removeClass('hidden');
    },

    /**
     * Event handler. Count out.
     *
     * @function
     * @public
     * @param {Object} event Event.
     */
    countOut: function(event) {
        if (event.target.value.length >= this.maxSymbol) {
            event.target.value = event.target.value.substr(0, this.maxSymbol);
        }
        event.target.form.getElements('span.note').set('text', this.countText(this.maxSymbol - event.target.value.length));
    },

    // todo: What is num?
    /**
     * Count text.
     *
     * @function
     * @public
     * @param {number|string} num
     * @returns {string}
     */
    countText: function(num) {
        var c1 = '',
            c2 = '',
            symbol;

        num = num.toString();

        if (!this.trans) {
            this.trans = [
                this.form.get('comment_symbol1'),
                this.form.get('comment_symbol2'),
                this.form.get('comment_symbol3'),
                this.form.get('comment_remain')
            ];
        }

        symbol = this.trans[1];

        if (num.length > 1) {
            c1 = num.substring(num.length - 2, 1);
        }
        if (num.length > 0) {
            c2 = num.substring(num.length - 1);
        }

        if (c1 == 1 || c2 == 0
            || (c1 != 1 && c2 > 4))
        {
            symbol = this.trans[2];
        }
        if (c2 == 1) {
            symbol = this.trans[0];
        }

        return this.trans[3] + ' ' + num + ' ' + symbol;
    },

    /**
     * Event handler. Edit comment.
     *
     * @function
     * @public
     * @param {Object} event Event.
     */
    editComment: function(event) {
        this.show_form(event);

        var li = event.target.getParent('li');
        li.getElement('div.comment_text').addClass('hidden');

        this.form.getElement('textarea').set('value', li.getElement('div.comment_text').get('html'));

        this.setFormCId(parseInt(li.id));
    },

    //todo: Why ...CId?
    /**
     * Set form id.
     *
     * @function
     * @public
     * @param {number} id ID.
     */
    setFormCId: function(id) {
        var cId = this.form.getElement('input[name=comment_id]');
        if (!cId) {
            cId = new Element('input', {'type':'hidden', 'name':'comment_id'});
            this.form.grab(cId);
        }
        cId.set('value', id);
    },

    /**
     * Event Handler. Delete comment.
     *
     * @function
     * @public
     * @param {Object} event Event.
     */
    deleteComment: function(event) {
        if (!confirm($(this.form).get('comment_realy_remove'))) {
            return;
        }

        this.showOverlay();

        if (this.isEditState) {
            this.showBaseForm();
        }

        var commentLi = $(event.target).getParent('li'),
            cId = parseInt(event.target.getParent('li').id);

        Energine.request(
            this.singlePath + 'delete-comment/',
            {'comment_id': cId},
            function(response) {
                if (response.mode == 'delete') {
                    if (commentLi.getSiblings('li').length) {
                        if ((!commentLi.getNext() || commentLi.getNext().hasClass('hidden'))
                            && (commentLi.getPrevious() && !commentLi.getPrevious().hasClass('hidden')))
                        {
                            commentLi.getPrevious().addClass('last_item');
                        }
                        if ((!commentLi.getPrevious() || commentLi.getPrevious().hasClass('hidden'))
                            && (commentLi.getNext() && !commentLi.getNext().hasClass('hidden')))
                        {
                            commentLi.getNext().addClass('first_item');
                        }
                        commentLi.destroy();
                    } else {
                        commentLi.getParents('.comment_thread')[0].destroy();
                    }

                    var spanNum = $$('div.comments .comments_title span.figure')[0],
                        num = parseInt((spanNum.get('text')).substr(1));
                    spanNum.set('text', '(' + (num ? num - 1 : 0) + ')');
                }
                this.overlay.hide();
            }.bind(this),
            function(response) {
                this.overlay.hide();
                this.showError(response);
            }.bind(this)
        );
    },

    /**
     * Show overlay.
     * @function
     * @public
     */
    showOverlay: function() {
        if (!this.overlay) {
            /**
             * Overlay.
             * @type {Overlay}
             */
            this.overlay = new Overlay(document.getElement('.comments'));
        }
        this.overlay.show();
    }
});
