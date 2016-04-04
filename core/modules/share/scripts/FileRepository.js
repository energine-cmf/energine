/**
 * @file Contain the description of the next classes:
 * <ul>
 *     <li>[FileRepository]{@link FileRepository}</li>
 *     <li>Implementation of the methods [popImage]{@link Grid#popImage} and overriding [iterateFields]{@link Grid#iterateFields} for the class [Grid]{@link Grid}</li>
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
 * File cookie name.
 * @type {string}
 */
var FILE_COOKIE_NAME = 'NRGNFRPID';

Grid.implement(/** @lends Grid# */{
    /**
     * Pop the image.
     *
     * @function
     * @public
     * @param {string} path Path to the image.
     * @param {Element} tmplElement Template element.
     */
    popImage: function (path, tmplElement) {
        var popUpImg = new Element('img', {'src': Energine.resizer + 'w298-h224/' + path, 'width': 60, 'height': 45,
            'styles': {
                border: '1px solid gray',
                'border-radius': '10px',
                'z-index': 1
            },
            'events': {
                'click': function (e) {
                    this.destroy()
                },
                'mouseleave': function (e) {
                    this.destroy();
                }
            }
        }).inject(document.body)
            .position({'relativeTo': tmplElement, 'position': 'center'})
            .set('morph', {duration: 'short', transition: 'linear'});

        var p = popUpImg.getPosition();
        popUpImg.morph({width: 298, height: 224, left: p.x, top: p.y});
    },

    // overridden
    iterateFields: function (fieldName, record, row) {
        // Пропускаем невидимые поля.
        if (!this.metadata[fieldName].visible || this.metadata[fieldName].type == 'hidden') {
            return;
        }

        var fieldValue = '',
            cell = new Element('td').inject(row);

        switch (fieldName) {
            case 'upl_path':
                cell.setStyles({ 'text-align': 'center', 'vertical-align': 'middle' });

                var image = new Element('img', {src: 'about:blank'}),
                    tmt,
                    dimensions = {'width': 40, 'height': 40},
                    container = new Element('div', {'class': 'thumb_container'}).inject(cell);

                switch (record['upl_internal_type']) {
                    case 'folder':
                        image.setProperty('src', 'images/icons/icon_folder.png');			
			dimensions = {'width': 50, 'height': 50};
                        break;

                    case 'repo':
                        image.setProperty('src', 'images/icons/icon_repository.gif');
                        break;

                    case 'folderup':
                        image.setProperty('src', 'images/icons/icon_folder_up2.png');
			dimensions = {'width': 50, 'height': 38};
                        break;

                    case 'video':
                    case 'image':
                        dimensions = {'width': 60, 'height': 45};			
                        image.setProperty('src', Energine.resizer + 'w60-h45/' + record[fieldName])
                            .addEvents({
                                'error': function () {
                                    image.setProperty('src', 'http://placehold.it/60x45/');
                                    container.removeEvents('mouseenter').removeEvents('mouseleave');
                                }
                            })
                            .setStyles({
                                'border-radius': '5px',
                                'border': '1px solid transparent'
                            });

                        container.addEvents({
                            'mouseenter': function (e) {
                                var el = $(e.target);
                                if (el.get('tag') != 'img') {
                                    el = el.getElement('img');
                                }
                                el.setStyle('border', '1px solid gray');
                                tmt = this.popImage.delay(700, this, [record[fieldName], el]);
                            }.bind(this),
                            'mouseleave': function (e) {
                                var el = $(e.target);
                                if (el.get('tag') != 'img') {
                                    el = el.getElement('img');
                                }
                                el.setStyle('border', '1px solid transparent');
                                if (tmt) {
                                    clearTimeout(tmt);
                                }
                            }
                        });

                        if (record['upl_internal_type'] == 'video') {
                            container.grab(new Element('div', {'class': 'video_file'}));
                        }
                        
                        cell.requestFilesSize= new XMLHttpRequest();
			cell.requestFilesSize.ImageProps=cell;
			cell.requestFilesSize.open('HEAD',  record[fieldName], true);
			cell.requestFilesSize.onreadystatechange = function() 
			  {	
			    if (this.readyState == 4) 
				{
				  if (this.status == 200) 
				      {
					  var props=this.ImageProps.parentNode.getElementsByClassName('properties');
					  if (props.length>0) {
					      var size=this.getResponseHeader('Content-Length');
					      var size_abbr='B';
					      if (size > 1024) {
						    size=size/1024;size_abbr="KiB"; 
						    if (size > 1024) {
						      size=size/1024;size_abbr="MiB";
						      if (size > 1024) {
							size=size/1024;size_abbr="GiB";
						      }
						    }
					      }
					      size=(size).toPrecision(3);					      
					      new Element('tr').inject(props[0].getElementsByTagName("tbody")[0]).adopt([
						new Element('td', {'html': Energine.translations.get('TXT_FILE_SIZE')+":"}),
						new Element('td', {'html': size+" "+size_abbr})
					      ]);
					  }
				      }
				}
			  };
			cell.requestFilesSize.send(null);

                        break;

                    default:
                        dimensions = {'width': 39, 'height': 48};
                        image.setProperty('src', 'images/icons/icon_undefined.gif');
                        break;
                }
                image.setProperties(dimensions).inject(container);		
                break;

            case 'upl_publication_date':
                if (record[fieldName]) {
                    fieldValue = record[fieldName].clean();
                }
                cell.set('html', fieldValue);
                break;

            case 'upl_properties':
                var propsTable = new Element('tbody');

                cell.addClass('properties')
                    .grab(new Element('table')
                        .grab(propsTable));

                if (!record['upl_internal_type'].test('folder|repo')) {
                    /*new Element('tr').inject(propsTable).adopt([
                     new Element('td', {'colspan': 2, 'html':'<a href="#">'+ record['upl_path'] + '</a>'}),
                     ]
                     );*/
                    if (!record['upl_is_ready']) {
                        new Element('tr').inject(propsTable).adopt([
                            new Element('td', {'html': this.metadata['upl_is_ready'].title + ' :'}),
                            new Element('td', {'html': Energine.translations['TXT_NOT_READY']})
                        ]);
                    }
                    if (record['upl_mime_type']) {
                        var video_types = [];
                        if (record['upl_is_mp4'] && record['upl_is_mp4'] == '1') {
                            video_types.push('mp4');
                        }
                        if (record['upl_is_webm'] && record['upl_is_webm'] == '1') {
                            video_types.push('webm');
                        }
                        if (record['upl_is_flv'] && record['upl_is_flv'] == '1') {
                            video_types.push('flv');
                        }

                        new Element('tr').inject(propsTable).adopt([
                            new Element('td', {'html': this.metadata['upl_mime_type'].title + ' :'}),
                            new Element('td', {'html': (video_types.length) ? video_types.join(', ') : record['upl_mime_type']})
                        ]);
                    }

                    switch (record['upl_internal_type']) {
                        case 'video':
                            if (record['upl_duration']) {
                                new Element('tr').inject(propsTable).adopt([
                                    new Element('td', {'html': this.metadata['upl_duration'].title + ' :'}),
                                    new Element('td', {'html': record['upl_duration']})
                                ]);
                            }
                            break;
                        case 'image':
                            if (record['upl_width']) {
                                new Element('tr').inject(propsTable).adopt([
                                    new Element('td', {'html': this.metadata['upl_width'].title + ' :'}),
                                    new Element('td', {'html': record['upl_width']})
                                ]);
                            }
                            if (record['upl_height']) {
                                new Element('tr').inject(propsTable).adopt([
                                    new Element('td', {'html': this.metadata['upl_height'].title + ' :'}),
                                    new Element('td', {'html': record['upl_height']})
                                ]);
                            }

                            break;

                        default :
                            break;
                    }
                }
                break;

            case 'upl_title':
                if (record[fieldName]) {
                    fieldValue = record[fieldName].clean();
                }

                if (!record['upl_internal_type'].test('folder|repo')) {
                    cell.set('html', '<a target="_blank" href="' + Energine.media + record['upl_path'] + '">' + fieldValue + '</a>')
                } else {
                    cell.set('html', fieldValue);
                }
                break;

            default :
                break;
        }
    }.protect()
});

/**
 * File repository.
 *
 * @augments GridManager
 *
 * @constructor
 * @param {Element|string} element The main holder element.
 */
var FileRepository = new Class(/** @lends FileRepository# */{
    Extends: GridManager,

    // constructor
    initialize: function (element) {
        this.parent(element);

        /**
         * List of paths.
         * @type {PathList}
         */
        this.pathBreadCrumbs = new PathList(this.element.getElementById('breadcrumbs'));
        /**
         * Current PID (Parent ID).
         * @type {string|number}
         */
        this.currentPID = '';
    },

    /**
     * Overridden parent [onDoubleClick]{@link GridManager#onDoubleClick} event handler.
     * @function
     * @public
     */
    onDoubleClick: function () {
        this.open();
    },

    /**
     * Overridden parent [onSelect]{@link GridManager#onSelect} event handler.
     * @function
     * @public
     */
    onSelect: function () {
        this.toolbar.enableControls();

        var r = this.grid.getSelectedRecord(),
            openBtn = this.toolbar.getControlById('open');

        switch (r.upl_internal_type) {
            case 'folder':
                if (openBtn) {
                    openBtn.enable();
                }
                break;

            case 'folderup':
                this.toolbar.disableControls();
                if (openBtn) {
                    openBtn.enable();
                }
                if(this.toolbar.getControlById('addDir'))
                    this.toolbar.getControlById('addDir').enable();
                if(this.toolbar.getControlById('add'))
                    this.toolbar.getControlById('add').enable();
                break;

            case 'repo':
                this.toolbar.disableControls();
                if (openBtn && r.upl_is_ready) {
                    openBtn.enable();
                }
                break;

            default:
                break;
        }

        var btn_map = {
            'addDir': 'upl_allows_create_dir',
            'add': 'upl_allows_upload_file',
            'edit': (r.upl_internal_type == 'folder') ? 'upl_allows_edit_dir' : 'upl_allows_edit_file',
            'delete': (r.upl_internal_type == 'folder') ? 'upl_allows_delete_dir' : 'upl_allows_delete_file'
        };

        for (var btn in btn_map) {
            if (r[btn_map[btn]] && this.toolbar.getControlById(btn) && !this.toolbar.getControlById(btn).disabled()) {
                this.toolbar.getControlById(btn).enable();
            } else if(this.toolbar.getControlById(btn)){
                this.toolbar.getControlById(btn).disable();
            }
        }
    },

    /**
     * Overridden parent [processServerResponse]{@link GridManager#processServerResponse} method.
     *
     * @function
     * @public
     * @param {Object} result Response from the server.
     */
    processServerResponse: function (result) {
        // todo: It is better to set this width as fixed over CSS. @29.10.13: I found that the grid's table is already has an class 'fixed_columns'
        // todo: Possible fix: remove the class 'fixed_columns' -- @18.11.13: moved back
        this.grid.headOff.getElement('th:index(0)').setStyle('width', '100px');
        if (!this.initialized) {
            this.grid.setMetadata(result.meta);
            this.initialized = true;
        }
        if (!result.data) {
            result.data = [];
        }
        if (this.currentPID) {
            Cookie.write(FILE_COOKIE_NAME, this.currentPID, {path: new URI(Energine.base).get('directory'), duration: 1});
        }

        this.grid.setData(result.data);

        if (result.pager) {
            this.pageList.build(result.pager.count, result.pager.current);
        }


        if (!this.grid.isEmpty()) {
            this.toolbar.enableControls();
            this.pageList.enable();
        }

        this.pathBreadCrumbs.load(result.breadcrumbs, function (upl_id) {
            this.currentPID = upl_id;
            if (this.filter) {
                this.filter.remove();
            }
            this.loadPage(1);
        }.bind(this));

        this.grid.build();
        this.overlay.hide();
    },

    /**
     * Open action.
     * @function
     * @public
     */
    open: function () {
        var r = this.grid.getSelectedRecord();
        switch (r.upl_internal_type) {
            case 'repo':
            case 'folder':
                this.currentPID = r.upl_id;
                if (this.filter) {
                    this.filter.remove();
                }
                this.loadPage(1);
                break;

            case 'folderup':
                this.currentPID = r.upl_id;
                this.loadPage(1);
                break;

            default:
                if (r.upl_is_ready) {
                    if (this.toolbar.getControlById('open')) {
                        if(r['upl_path']){
                            var t = r['upl_path'].split('?');
                            r['upl_path'] = t[0];
                        }
                        ModalBox.setReturnValue(r);
                        ModalBox.close();
                    } else {
                        this.edit();
                    }
                } else {
                    alert(Energine.translations['ERR_UPL_NOT_READY']);
                }
                break;
        }
    },

    /**
     * Overridden parent [add]{@link GridManager#add} action.
     * @function
     * @public
     */
    add: function () {
        var pid = this.grid.getSelectedRecord().upl_pid;
        if (pid) {
            pid += '/';
        }

        ModalBox.open({
            url: this.singlePath + pid + 'add/',
            onClose: this.processAfterCloseAction.bind(this)
        });
    },

    /**
     * Add directory action.
     * @function
     * @public
     */
    addDir: function () {
        var pid = this.grid.getSelectedRecord().upl_pid;
        if (pid) {
            pid += '/';
        }

        ModalBox.open({
            url: this.singlePath + pid + 'add-dir/',
            onClose: function (response) {
                if (response && response.result) {
                    this.currentPID = response.data;
                    this.processAfterCloseAction(response);
                }
            }.bind(this)
        });
    },

    /**
     * Upload zip-file.
     *
     * @function
     * @public
     * @param {Object} data Data.
     */
    uploadZip: function (data) {
        Energine.request(this.singlePath + 'upload-zip', 'PID=' + this.grid.getSelectedRecord().upl_pid + '&data=' + encodeURIComponent(data.result), function (response) {
            console.log(response)
        });
    },

    /**
     * Overridden parent [buildRequestURL]{@link GridManager#buildRequestURL} method.
     *
     * @param {number|string} pageNum Page number.
     * @returns {string}
     */
    buildRequestURL: function(pageNum) {
        var url = '',
            level = '';

        var cookiePID = Cookie.read(FILE_COOKIE_NAME);
        if (this.currentPID === 0) {
            level = '';
        } else if (this.currentPID) {
            level = this.currentPID + '/';
        } else if (cookiePID) {
            this.currentPID = cookiePID;
            level = this.currentPID + '/';
        }

        if (this.grid.sort.order) {
            url = this.singlePath + level + 'get-data/' + this.grid.sort.field + '-'
                + this.grid.sort.order + '/page-' + pageNum + '/';
        } else {
            url = this.singlePath + level + 'get-data/' + 'page-' + pageNum + '/';
        }

        return url;
    },

    /**
     * Overridden parent [buildRequestPostBody]{@link GridManager#buildRequestPostBody} method.
     *
     * @returns {string}
     */
    buildRequestPostBody: function() {
        var postBody = '';

        if (this.filter) {
            postBody += this.filter.getValue();
        }

        return postBody;
    }
});

/**
 * List of paths.
 *
 * @constructor
 * @param {Element|string} el The main element holder.
 */
var PathList = new Class(/** @lends PathList# */{
    // constructor
    initialize: function (el) {
        /**
         * Main element.
         * @type {Element}
         */
        this.element = $(el);
    },

    /**
     * Load the list.
     *
     * @function
     * @public
     * @param {Object} data
     * @param {Function} loader
     */
    load: function (data, loader) {
        this.element.empty();

        Object.each(data, function (title, id) {
            this.element.adopt([
                new Element('a', {
                    href: '#',
                    'text': title,
                    'events': {
                        'click': function (e) {
                            e.stop();
                            loader(id);
                        }
                    }
                }),
                new Element('span', {'text': ' / '})
            ])
        }, this);
    }
});