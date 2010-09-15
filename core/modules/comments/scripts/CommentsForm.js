ScriptLoader.load('ValidForm');
var CommentsForm = new Class({
	Extends: ValidForm,
	Implements: Energine.request,
	initialize : function(element) {
		this.parent(element)
        this.form = this.componentElement.getParent('form').addClass('form');
		$$('li.comment_item span.btn_content').addEvent('click', this.show_form.bind(this))
		$$('div.comments div.comment_inputblock a.link_comment').addEvent('click', this.show_form_base.bind(this))
		this.form.getElement('a.btn_comment').addEvent('click', this.validateForm.bind(this))
        this.form.getElement('textarea').addEvent('keyup', this.countOut.bind(this))
	},
    maxSymbol: 250,
    validateForm: function(event) {
    	this.parent(event);
        this.cancelEvent(event); 
        
        var data = {target_id:this.componentElement.getElement('input[name=target_id]').value, 
					comment_name:this.componentElement.getElement('textarea[name=comment_name]').value}
        
        var parentId = this.form.getElement('input[name="parent_id"]')
        if(parentId){
        	data['parent_id'] = parentId.value
        }
	
	    this.request(
	    	this.singlePath + 'save-comment/',
	        data,
	        function(response){this.show_result(response)}.bind(this),
	        function(){this.show_error(response)}.bind(this)
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
            li.getElement('div.comment_username a').set('text', item['u_fullname'])
            li.getElement('div.comment_date').set('text', item['comment_created'])

            if(item['is_tree'] &&  li.getElement('div.comment_inputblock')){
                li.getElement('div.comment_inputblock span.btn_content').addEvent('click', this.show_form.bind(this))
            }
            else{
                li.getElement('div.comment_inputblock').addClass('hidden')
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
        this.form.removeClass('hidden');
    	event.target.getParent('li').grab(this.form)
        this.form.getElement('textarea').focus()
        $$('li.comment_item').getElement('div.comment_inputblock').removeClass('hidden')
    	event.target.getParent('div.comment_inputblock').addClass('hidden')
    	var parentId = this.form.getElement('input[name="parent_id"]')
    	if(!parentId){
	    	parentId = new Element('input', {'type':'hidden', 'name':'parent_id'})
	    	this.form.grab(parentId)
    	}
    	parentId.setProperty('value', parseInt(event.target.getParent('li').id))
    	
    	return false
    },
    show_form_base: function(){
        this.form.removeClass('hidden');
        $$('li.comment_item').getElement('div.comment_inputblock').removeClass('hidden')
    	var parentId = this.form.getElement('input[name="parent_id"]')
    	if(parentId) parentId.dispose()
    	$$('div.comments').grab(this.form)
        this.form.getElement('textarea').focus()
    	
    	return false
    },
    countOut: function(event){
        if(event.target.value.length >= this.maxSymbol){
            event.target.value = event.target.value.substr(0, this.maxSymbol)
        }
        event.target.form.getElements('span.note span')[0].set('text', this.maxSymbol-event.target.value.length)
    }
})
	