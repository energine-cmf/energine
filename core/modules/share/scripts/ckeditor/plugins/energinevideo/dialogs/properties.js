CKEDITOR.dialog.add( 'videoPropertiesDialog', function (editor) {
    return {
        title: 'Video Properties',
        minWidth: 200,
        minHeight: 200,
        contents: [
            {
                id: 'properties',
                elements: [
                    {
                        type: 'text',
                        id: 'width',
                        label: 'Width',
                        validate: CKEDITOR.dialog.validate.integer('Width must be numerical')
                    },
                    {
                        type: 'text',
                        id: 'height',
                        label: 'Height',
                        validate: CKEDITOR.dialog.validate.integer('Height must be numerical')
                    }
                ]
            }
        ],
        onOk: function() {

        }
    };
});