var Calendar = new Class({

    getOptions: function() {
        var today = new Date;
        return {
            currYear: today.getFullYear(),
            currMonth: today.getMonth() + 1,
            currDay: today.getDate(),
            year: null,
            month: null,
            day: null,
            minYear: 1,
            maxYear: 9999,
            minMonth: 1,
            maxMonth: 12,
            weekdayNames: ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'],
            monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            onSelect: Class.empty,
            fieldName: null
        };
    },

    initialize: function(options) {
        Asset.css('calendar.css');
        this.setOptions(this.getOptions(), options);

        this.year = $pick(this.options.year, this.options.currYear);
        this.month = $pick(this.options.month, this.options.currMonth);
        this.day = $pick(this.options.month, this.options.currMonth);

        this.element = new Element('table').setProperties({ 'cellSpacing': '1' }).addClass('calendar');
        var thead = new Element('thead').injectInside(this.element);
        var tbody = new Element('tbody').injectInside(this.element);

        new Element('tr').adopt(new Element('th').setProperties({ 'id': 'calendar_prevMonth' }).addEvent('click', this.prevMonth.bind(this)))
            .adopt(this.monthName = new Element('th').setProperties({ 'colSpan': '5' }))
            .adopt(new Element('th').setProperties({ 'id': 'calendar_nextMonth' }).addEvent('click', this.nextMonth.bind(this)))
            .injectInside(thead);

        var weekdays = new Element('tr').addClass('weekdays').injectInside(thead);
        this.options.weekdayNames.each(function(weekdayName) {
            new Element('th').appendText(weekdayName).injectInside(weekdays);
        });

        var selectDateListener = this.selectDate.bindWithEvent(this);
        var toggleHighlightListener = this.toggleHighlight.bindWithEvent(this);

        this.monthGrid = [];
        for (var i = 0; i < 6; i++) {
            var week = [];
            var row = new Element('tr').injectInside(tbody);
            for (var j = 0; j < 7; j++) {
                var day = new Element('td').injectInside(row);
                day.addEvents({
                    'click': selectDateListener,
                    'mouseover': toggleHighlightListener,
                    'mouseout': toggleHighlightListener
                });
                week.push(day);
            }
            this.monthGrid.push(week);
        }
        this.build();
    },

    build: function() {
        this.monthName.setHTML(this.options.monthNames[this.month - 1]+' '+this.year);

        var isCurrMonth = (this.year == this.options.currYear && this.month == this.options.currMonth);
        var daysInMonth = this.getDaysInMonth(this.year, this.month);
        var monthFirstWeekday = this.getWeekday(this.year, this.month, 1);
        for (var i = 1; i <= 42; i++) { // 6 weeks * 7 days
            var day = this.monthGrid[(Math.ceil(i / 7) - 1)][((i - 1) % 7)];
            day.setHTML('').className = '';
            var dayNum = i - monthFirstWeekday + 1;
            if (0 < dayNum && dayNum <= daysInMonth) {
                day.setHTML(dayNum).addClass('thisMonth');
                if (isCurrMonth && dayNum == this.options.currDay) day.addClass('currDay');
            }
            else day.addClass('otherMonth');
        }
    },

    destroy: function() {
        this.element.remove();
        delete FormCalendar.calendars[this.options.fieldName];
    },

    selectDate: function(event) {
        if (!event.target.hasClass('thisMonth')) return;
        var day = event.target.innerHTML.toInt();
        this.fireEvent('onSelect', { year: this.year, month: this.month, day: day });
        this.destroy();
    },

    toggleHighlight: function(event) {
        if (event.target.hasClass('thisMonth')) {
            event.target.toggleClass('highlighted');
        }
    },

    changeMonth: function(direction) {
        switch (direction) {
            case 'prev':
                if (this.year == this.options.minYear) {
                    if (this.month == this.options.minMonth) return;
                    this.month--;
                }
                else {
                    if (this.month == 1) {
                        this.month = 12;
                        this.year--;
                    }
                    else this.month--;
                }
                break;
            case 'next':
                if (this.year == this.options.maxYear) {
                    if (this.month == this.options.maxMonth) return;
                    this.month++;
                }
                else {
                    if (this.month == 12) {
                        this.month = 1;
                        this.year++;
                    }
                    else this.month++;
                }
                break;
            default:
                return;
        }
        this.build();
    },

    prevMonth: function() { this.changeMonth('prev'); },
    nextMonth: function() { this.changeMonth('next'); },

    getDaysInMonth: function(year, month) {
        switch (month) {
            case 1: case 3: case 5: case 7: case 8: case 10: case 12:
                return 31; break;
            case 4: case 6: case 9: case 11:
                return 30; break;
            case 2:
                return (this.year % 4 == 0 && (this.year % 100 != 0 || this.year % 400 == 0)) ? 29 : 28; break;
            default: // not reached
        }
    },

    getWeekday: function(year, month, day) {
        var date = new Date(year, month - 1, day);
        return (date.getDay() || 7);
    }
});

Calendar.implement(new Events);
Calendar.implement(new Options);

var FormCalendar = {
	setDate: function(fieldName){
		var getDateString = function(date, destinationField){

			new Ajax(this.singlePath + 'format-date/', {
				method:'post',
				data:Object.toQueryString({'date':date}),
				onSuccess: function(responseText){
					var result = Json.evaluate(responseText);
					$(destinationField).value = result;

			}}).request();
		}.bind(this);

		getDateString($(fieldName).getValue(), 'date_'+fieldName);
	},
	showCalendar: function(fieldName, event) {
	    if (FormCalendar.calendars[fieldName]) return;
	    var field = $(fieldName);
	    var calendOptions = {
	        fieldName: fieldName,
	        onSelect: function(date) {
	            field.value = date.year+'-'+date.month+'-'+date.day;
	            this.setDate(fieldName);
	        }.bind(this)
	    };
	    var currDate = field.getValue();
	    if (currDate != '') {
	        currDate = currDate.split('-', 3);
	        calendOptions = Object.extend(calendOptions, {
	            currYear: parseInt(currDate[0]),
	            currMonth: parseInt(currDate[1]),
	            currDay: parseInt(currDate[2])
	        });
	    }
	    var calend = new Calendar(calendOptions);
	    FormCalendar.calendars[fieldName] = calend;

	    var target = event.target || $(window.event.srcElement);

	    calend.element.setStyles(
	    	{
	    		position: 'absolute',
	    		top: target.getTop(($E('.pane'))?[$E('.pane')]:[])+'px',
	    		left: target.getLeft(($E('.pane'))?[$E('.pane')]:[])+'px'
	    	}
	    ).injectInside(document.body);
	},
	calendars:{}
};
