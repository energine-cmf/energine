ScriptLoader.load('Form');
var ProductForm = new Class({
    Extends: Form,
    initialize: function (el) {
        this.parent(el);
    },
    onTabChange: function () {
        this.currentTab.loaded = false;
        var segment, typeID;
        if (segment = this.currentTab.getProperty('data-segment')) {
            typeID = this.element.getElementById('pt_id').get('value').toInt();
            this.currentTab.setProperty('data-src', this.currentTab.getProperty('data-url') + typeID + this.currentTab.getProperty('data-segment'));
        }

        this.parent();

    }
});
