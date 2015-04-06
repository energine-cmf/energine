/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[Words]{@link Words}</li>
 *     <li>[ActiveList]{@link ActiveList}</li>
 *     <li>[DropBoxList]{@link DropBoxList}</li>
 *     <li>[AcplField]{@link Tags}</li>
 * </ul>
 *
 * @author Pavel Dubenko
 *
 * @version 1.0.1
 */

ScriptLoader.load('DropBoxList');
/**
 * Words.
 *
 * @constructor
 * @param {string} initialValue String line.
 * @param {string} sep Delimiter.
 */
var Words = function(initialValue, sep) {
    /**
     * Delimiter.
     * @type {string}
     */
    this.separator = sep;

    /**
     * Delimited string line.
     * @type {Array}
     * @private
     */
    this._elements = initialValue.split(this.separator);

    /**
     * Current word index.
     * @type {number}
     */
    this.currentIndex = 0;

    /**
     * Set the word index.
     *
     * @param {number} index Word index.
     */
    this.setCurrentIndex = function(index) {
        if (this._elements[index]) {
            this.currentIndex = index;
        }
    };

    /**
     * Return the initial string line.
     *
     * @returns {string}
     */
    this.asString = function() {
        return this._elements.join(this.separator);
    };

    /**
     * Get the amount of delimited words.
     *
     * @returns {number}
     */
    this.getLength = function() {
        return this._elements.length;
    };

    /**
     * Get the word at the specified index.
     *
     * @param {number} index Word index.
     * @returns {string}
     */
    this.getAt = function(index) {
        return (index < this._elements.length && index >= 0)
            ? this._elements[index]
            : '';
    };

    /**
     * Reset the delimited word at the specific index.
     *
     * @param {number} index Word index.
     * @param {string} value New word.
     */
    this.setAt = function(index, value) {
        this._elements[index.toInt()] = value
    };

    /**
     * Find the word.
     *
     * @param {number} curPos Position of the looked word.
     * @returns {Object} Object with properties: index {number}, str {string}.
     */
    this.findWord = function(curPos) {
        var leftMargin = 0,
            rightMargin = 0;

        for (var i = 0; i < this._elements.length; i++) {
            rightMargin = leftMargin + this._elements[i].length;

            if (curPos >= leftMargin && curPos <= rightMargin) {
                return {
                    index: i,
                    str: this._elements[i]
                };
            }

            leftMargin += this._elements[i].length + 1;
        }

        return {
            index: this._elements.length,
            str: ''
        };
    }
};

/**
 * Acpl field. From MooTools it implements: Options, Events.
 *
 * @constructor
 * @param {Element|string} element The main element.
 * @param {Object} [options] [Options]{@link Tags#options}.
 */
var Tags = new Class(/** @lends Tags# */{
    Implements: [Options,Events],

    /**
     * Words.
     * @type {Words}
     */
    words: null,

    /**
     * Value.
     * @type {string}
     */
    value: '',

    /**
     * Queue.
     * @type {Array}
     */
    queue: [],

    /**
     * Options.
     * @type {Object}
     *
     * @property {number} [startForm = 1] Start form number.
     */
    options: {
        startFrom: 1
    },

    // constructor
    initialize: function(element, options) {
        /**
         * The main element for the field.
         * @type {Element}
         */
        this.element = $(element);

        this.setOptions(options);

        /**
         * Container for the field.
         * @type {Element}
         */
        this.container = new Element('div', {'class': 'with_append', 'styles': {'position': 'relative'}}).wraps(this.element);

        if (this.element.get('name') == 'tags') {
            /**
             * Appended button for input field.
             * @type {Element}
             */
            this.button = new Element('button', {
                type: 'button',
                link: 'tags',
                style: 'height:18px;',
                onclick: this.element.get('component_id') + '.openTagEditor(this);',
                html: '...'
            }).inject(this.container);

            new Element('div', {'class': 'appended_block'}).wraps(this.button);
        }

        /**
         * Drop box list.
         * @type {DropBoxList}
         */
        this.list = new DropBoxList(this.element);
        this.list.addEvent('choose', this.select.bind(this));
        this.list.get().inject(this.element, 'after');
        /**
         * URL.
         * @type {string}
         */
        this.url = this.element.getProperty('data-url');

        /**
         * Words delimiter.
         * @type {string}
         */
        this.separator = this.element.getProperty('data-separator');

        //Вешаем на keyup для того чтобы у нас было реальное value поля
        this.element.addEvent('keyup', this.enter.bind(this));
    },

    /**
     * Event handler. Enter.
     *
     * @param {Object} e Event.
     */
    enter: function(e) {
        if (!this.url) {
            return;
        }

        var val = this.element.value;

        switch (e.key) {
            case 'esc':
                this.list.hide();
                this.list.empty();
                break;

            case 'up':
            case 'down':
            case 'enter':
                this.list.keyPressed.call(this.list, e);
                break;

            default :
                this.value = val;
                this.words = new Words(this.value, this.separator);

                var word = this.words.findWord((function(el) {
                    if (el.selectionStart) {
                        return el.selectionStart;
                    } else if (document.selection) {
                        var r = document.selection.createRange();
                        if (r == null) {
                            return 0;
                        }

                        var re = el.createTextRange(),
                            rc = re.duplicate();

                        re.moveToBookmark(r.getBookmark());
                        rc.setEndPoint('EndToStart', re);

                        return rc.text.length;
                    }
                    return 0;
                })(this.element));

                if (word.str.length > this.options.startFrom) {
                    this.words.setCurrentIndex(word.index);
                    this.putInQueue(word.str, this.value);
                }
        }
    },

    /**
     * Put in queue.
     *
     * @param {string} str One word.
     * @param {string} val The whole string.
     */
    putInQueue: function(str, val) {
        if (this.value == val) {
            this.requestValues(str);
        }
    },

    /**
     * Send the POST request.
     *
     * @param {string} str Data string.
     */
    requestValues: function(str) {
        new Request.JSON({
            url: this.url,
            onSuccess: this.rebuild.bind(this)
        }).send({
            method: 'post',
            data: 'value=' + str
        });
    },

    /**
     * Reset the items in the [list]{@link Tags#list}.
     *
     * @param {Array} data Data array.
     */
    rebuild: function(data) {
        if(data.result && data.data){
            this.list.update(data.data, this.value);
            this.list.show();
        }
        else {
            this.list.hide();
        }
    },

    /**
     * Select an item from the [list]{@link Tags#list}.
     *
     * @param {HTMLLIElement} li Element that will be selected.
     */
    select: function(li) {
        var text = li.get('text');

        if ((this.list.selected !== false) && this.list.items[this.list.selected]) {
            this.words.setAt(this.words.currentIndex, text);
            this.element.set('value', this.words.asString());
        }

        this.list.hide();
    }
});