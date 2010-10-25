ScriptLoader.load('ValidForm');
var CommentsForm = new Class({
	Extends: ValidForm,
	Implements: Energine.request,
	initialize : function(element) {
        this.parent(element)
        if(this.componentElement && this.componentElement.getParent('form')){
            this.form = this.componentElement.getParent('form').addClass('form');
            $$('div.comments div.comment_inputblock a.link_comment').addEvent('click', this.show_form_base.bind(this))
            this.form.getElement('a.btn_comment').addEvent('click', this.validateForm.bind(this))
            this.form.getElement('textarea').addEvent('keyup', this.countOut.bind(this));

            $$('li.comment_item div.comment_inputblock').each(function(el){
                el.getElements('a.btn.edit').addEvent('click', this.editComment.bind(this))
                el.getElements('a.btn.delete').addEvent('click', this.deleteComment.bind(this))
                el.getElements('a.btn.comment_do span.btn_content').addEvent('click', this.show_form.bind(this))
            }.bind(this))
        }
	},
    maxSymbol: 250,
    trans: null, 
    validateForm: function(event) {
    	this.parent(event);
        this.cancelEvent(event); 

        this.request(
            this.singlePath + 'save-comment/',
            this.form.toQueryString(),
            function(response) {
                if (response.mode == 'update') {
                    var li = $$('li.comment_item[id=' + response.data.comment_id + '_comment]')
                    li.getElement('div.hidden.comment_text').set('html', response.data.comment_name)

                    this.show_form_base(true)
                    this.form.getElement('textarea').set('value','')
                }
                else {
                    this.show_result(response)
                }
            }.bind(this)
        );
    },
    show_result: function(response){
    	if(response.errors){
    		alert(response.errors)
    	}
    	else if(response.data){
            var item = response.data[0]

            var li = $$('li.comment_item.hidden')[0].clone().removeClass('hidden')
            li.setAttribute('id', item['comment_id'] + '_comment')
            li.getElement('div.comment_text').set('html', item['comment_name'])
            li.getElement('div.comment_userpic a').grab(new Element('img', {'src':item['u_avatar_img'], 'width': 50, 'height':50}))
            li.getElement('div.comment_username a').set('text', item['u_nick'])
            li.getElement('div.comment_date').set('text', item['comment_created'])

            if(li.getElement('div.comment_inputblock')){
                if(item['is_tree']){
                    li.getElement('div.comment_inputblock span.btn_content').addEvent('click', this.show_form.bind(this))
                }
                else{
                    li.getElement('div.comment_inputblock').addClass('hidden')
                }
                li.getElements('a.btn.edit').addEvent('click', this.editComment.bind(this))
                li.getElements('a.btn.delete').addEvent('click', this.deleteComment.bind(this))
            }

        	if(item['comment_parent_id']){
        		var parentCommentLiName = item['comment_parent_id'] + '_comment'
        		var parentCommentLi = $$('div.comments ul li#'+parentCommentLiName+'')
        		var ul = parentCommentLi.getElement('ul')
        		if(!ul[0]){
        			ul = new Element('ul')
                    ul.setAttribute('class', 'comment_list')

                    var d = new Element('div')
                    d.addClass('comment_thread').grab(ul)

                    var i = new Element('i', {'class': 'icon20x20 comment_thread_icon'})
                    i.grab(new Element('i'))
                    d.grab(i)
                    parentCommentLi.grab(d)
        		}
        		ul.grab(li)
        	}
        	else $$('div.comments').show().getElement('ul').grab(li);
        	$$('div.comments span')[0].innerHTML = '('+ ($$('div.comments ul li').length - 1) + ')'

            this.form.addClass('hidden');
            $$('li.comment_item').getElement('div.comment_inputblock').removeClass('hidden')

            var t = this.componentElement.getElement('textarea[name=comment_name]')
        	t.value = ''
            t.fireEvent('keyup', {target: t}, 1)
    	}
    },
    show_form: function(event){
        this.preShowForm()
        var li = event.target.getParent('li')

        var text = li.getElement('div.comment_text')
        this.form.inject(text, 'after');

        this.form.getElement('textarea').focus()

        li.getChildren('div.comment_inputblock').addClass('hidden')

    	var parentId = this.form.getElement('input[name="parent_id"]')
    	if(!parentId){
	    	parentId = new Element('input', {'type':'hidden', 'name':'parent_id'})
	    	this.form.grab(parentId)
    	}
    	parentId.setProperty('value', parseInt(event.target.getParent('li').id))
    	this.showCancelBt()
    	return false
    },
    show_form_base: function(notSetFocus){
        this.preShowForm()

        if(this.form.getElements('div.comment_controlset a.btn_comment.cancel').length) {
            this.form.getElements('div.comment_controlset a.btn_comment.cancel').addClass('hidden')
        }

    	var parentId = this.form.getElement('input[name="parent_id"]')
    	if(parentId) parentId.dispose()
    	$$('div.comments').grab(this.form)

        if(!notSetFocus) this.form.getElement('textarea').focus()
    	return false
    },
    preShowForm: function(){
        this.setFormCId(0);
        this.form.removeClass('hidden');

        $$('li.comment_item div.hidden.comment_inputblock').removeClass('hidden')
        $$('li.comment_item div.comment_text').removeClass('hidden')
    },
    countOut: function(event){
        if(event.target.value.length >= this.maxSymbol){
            event.target.value = event.target.value.substr(0, this.maxSymbol)
        }
        event.target.form.getElements('span.note').set('text', this.countText(this.maxSymbol-event.target.value.length))
    },
    countText: function(num){
//        this.trans = ['символ', 'символа', 'символiв', 'Залишилось']
        if(!this.trans){
            this.trans = [this.form.get('comment_symbol1'), this.form.get('comment_symbol2'), this.form.get('comment_symbol3'),this.form.get('comment_remain')]
        }
        var symbol = this.trans[1];
        if(num.toString().length > 1)
            var c1 = num.toString().substring(num.toString().length - 2, 1);
        if(num.toString().length > 0)
            var c2 = num.toString().substring(num.toString().length - 1);
        if(c1==1 || c2==0 || (c1!=1 && c2>4)) symbol = this.trans[2];
        if(c2==1) symbol = this.trans[0];

        return this.trans[3] + ' ' + num +' ' + symbol;
    },
    editComment: function(event) {
        this.show_form(event)
        var li = event.target.getParent('li')
        li.getElement('div.comment_text').addClass('hidden')

        this.form.getElement('textarea').set('html', li.getElement('div.comment_text').get('html'))

        this.setFormCId(parseInt(li.id))
        return false
    },
    showCancelBt: function(){
        if(this.form.getElements('div.comment_controlset a.btn_comment.hidden.cancel').length) {
            this.form.getElements('div.comment_controlset a.btn_comment.hidden.cancel').removeClass('hidden')
        }
        else if(!this.form.getElements('div.comment_controlset a.btn_comment.cancel').length) {
            var a = this.form.getElement('div.comment_controlset a.btn_comment').clone()
            a.addClass('cancel')
            a.addEvent('click', this.show_form_base.bind(this))
            a.getElement('span.btn_content').set('text', 'Отменить')
            this.form.getElement('div.comment_controlset').grab(a)
        }
    },
    setFormCId: function(id){
        var cId = null;
        if (!(cId = this.form.getElement('input[name=comment_id]'))) {
            cId = new Element('input', {'type':'hidden', 'name':'comment_id'})
            this.form.grab(cId)
        }
        cId.set('value', id)
    },
    deleteComment: function(event) {
        if (confirm($(this.form).get('comment_realy_remove'))) {
            if (this.isEditState) this.showBaseForm();
            var event = new Event(event || window.event);
            var cId = parseInt(event.target.getParent('li').id)
            this.request(
                    this.singlePath + 'delete-comment/',
            {'comment_id': cId},
                    function(response) {
                        if (response.mode == 'delete') {
                            $(event.target.getParent('li')).destroy()
                            var spanNum = $$('div.comments .comments_title span.figure')[0]
                            var num = parseInt((spanNum.get('text')).substr(1))
                            spanNum.set('text', '('+ (num?num-1:0) +')')
                        }
                    }.bind(this),
                    function() {
                        this.showError(response)
                    }.bind(this)
                    );
        }
        return false
    }
})
	