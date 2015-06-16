var ColorPicker = new Class({
    Implements: [Options],
    options: {
        defaultColor: '#FFFFFF',
        colorsPerLine: 8,
        changeOnHover: false,
        prefix: 'colorpicker',
        colors: ['#000000', '#444444', '#666666', '#999999', '#cccccc', '#eeeeee', '#f3f3f3', '#ffffff'
            , '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#0000ff', '#9900ff', '#ff00ff'
            , '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc'
            , '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd'
            , '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0'
            , '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79'
            , '#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47'
            , '#660000', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4C1130']
    },
    element: null,
    box: null,
    input: null,
    initialize: function (element, options) {
        this.setOptions(options);
        this.element = document.id(element);
        Asset.css('mootools-colorpicker.css');
        // Create element
        this.build();
    },
    build: function () {

        var self = this;

        // Build colorbox
        this.box = new Element('div', {
            'class': 'colorpicker-box',
            id: this.options.prefix + '-colorbox',
            events: {
                mouseleave: function () {
                    if (self.options.changeOnHover === true) {
                        self.setDefaultColor();
                    }
                }
            }
        });

        var colorBoxColors = new Element('ul');

        // Build color selection
        Array.each(this.options.colors, function (currentColor, i) {

            currentColor = currentColor.toUpperCase();

            var colorUnit = new Element('li', {
                styles: {
                    'background-color': currentColor
                },
                'class': 'colorpicker-color',
                title: currentColor,
                id: self.options.prefix + '-color-' + i,
                'data-color': currentColor,
                events: {
                    click: function () {
                        self.selectColor(currentColor);
                    },
                    mouseover: function () {
                        if (self.options.changeOnHover === true) {
                            self.hoverColor(currentColor);
                        }
                    }
                }
            });

            if (i % self.options.colorsPerLine === 0) {
                colorUnit.setStyle('clear', 'both');
            }

            colorUnit.inject(colorBoxColors);
        });

        // Build color input
        this.input = new Element('div', {
            'class': 'colorpicker-input clearfix',
            styles: {
                'background-color': this.options.defaultColor
            },
            'data-color': this.options.defaultColor,
            'title': this.options.defaultColor,
            events: {
                click: function () {
                    self.positionAndShowBox();
                }
            }
        });
        this.input.grab(
            new Element('i',
                {
                    'class': 'fa fa-eyedropper',
                    events: {
                        click: function (e) {
                            e.stopPropagation();
                            if ($(e.target).hasClass('fa-close')) {
                                self.resetColor();
                            }
                            else {
                                self.positionAndShowBox();
                            }

                        }
                    }
                }
            )
        );

        // Initialize default color
        this.setDefaultColor();

        // Onblur event
        $$('body').addEvent('click', function (e) {
            if (!$(e.target).hasClass('colorpicker-color') && !$(e.target).hasClass('colorpicker-input')) {
                self.setDefaultColor();
                self.box.hide();
            }
        });

        // Place elements
        this.input.inject(this.element, 'after');
        colorBoxColors.inject(this.box);
        this.box.inject(this.input, 'after');

        // Hide the colorbox
        this.box.hide();
        this.element.hide();

        return this;
    },
    selectColor: function (color) {
        this.box.hide();
        this.element.set('value', color);
        this.input.set('data-color', color).set('title', color).setStyle('background-color', color);
        this.input.getElement('i').removeClass('fa-eyedropper').addClass('fa-close');
        this.element.fireEvent('onSelectColor');
    },
    resetColor: function () {
        this.input.getElement('i').removeClass('fa-close').addClass('fa-eyedropper');
        this.element.erase('value');
        this.input.erase('value').erase('data-color').erase('title');
        this.input.setStyle('background-color', '');

        this.box.hide();
    },
    setDefaultColor: function () {
        var color = this.element.get('value');
        if (color) {
            this.input.set('data-color', color)
                .set('title', color)
                .setStyle('background-color', color);
            this.element.fireEvent('onSetDefaultColor');
            this.input.getElement('i').removeClass('fa-eyedropper').addClass('fa-close');
        }

    },
    hoverColor: function (color) {
        this.input.setStyle('background-color', color);
    },
    positionAndShowBox: function () {
        this.box.position({
            relativeTo: this.input,
            position: 'bottomLeft',
            edge: 'upperLeft'
        });
        this.box.show();
    }
});