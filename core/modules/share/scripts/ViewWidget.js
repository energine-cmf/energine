/**
 * Абстрактный класс для визуальных виджетов
 * типа Grid и DirView
 */
var ViewWidget = new Class({
    /**
     * DOMElement на базе которого строится виджет
     */
    element: false,
    /**
     * Объект с помощью которого передаются параметры в виджет
     */
    options: {},  
    /**
     * Конструктор
     * @param element DOMElement элемент на базе которого строится виджет
     * @param options Array массив опций, с его помощью можно передавать в виджет ф-ции из менеджера
     * @access public
     */
    initialize: function(element, options) {
        this.element = $(element);
		this.selectedItem = false;
		this.metadata = false;
		this.data = false;
        this.options = Object.extend(
            {onSelect: $empty},
            options || {}
        );
    },
    /**
     * Устанавливает метаданные
     * @param meta object Метеданные переданные из менеджера
     * @access public
     * @return void
     */
    setMetadata: function(meta) {
        this.metadata = meta;
    },
    /**
     * Возвращает мета данные 
     *
     * @access public
     * @return object
     */
     getMetadata: function() {
        return this.metadata;
    },
    /**
     * Устанавливает данные
     * @param data object Данные
     * @access public
     * @return void
     */
    setData: function(data) {
        if (!this.metadata) {
            alert('Cannot add data without specified metadata.');
            return false;
        }
        this.data = data;    
    },
    /**
     * Возвращает данные 
     *
     * @access public
     * @return object
     */
     getData: function() {
        return this.data;
    },
    /**
     * Возвращает выделенный елемент
     *
     * @access public
     * @return DOMElement
     */
    getSelectedItem: function() {
        return this.selectedItem;
    },
    /**
     * Устанавливает текущий выделенный элемент
     * @param DOMElement выделенный елемент
     * @access public
     * @return mixed
     */
    setSelectedItem : function(element) {
        if (arguments.length == 0) {
            this.selectedItem = false;                    
        }
        else {
            this.selectedItem = $(element);    
        }

        return this.selectedItem;
    },
    /**
     * Выделяет  переданный елемент
     * @param obj DOMElement 
     * @access public
     * @return void
     */
	select : function(obj) {
		if (this.getSelectedItem()) {
	        this.getSelectedItem().removeClass('selected');				
		}
        obj.addClass('selected');
        this.setSelectedItem(obj);
        this.options.onSelect();
	}
});