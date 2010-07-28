ScriptLoader.load('ValidForm');
var CommentsForm = new Class({
	Extends: ValidForm,
	Implements: Energine.request,
	initialize : function(element) {
		this.parent(element)
		$$('div.comments ul li span a').addEvent('click', this.show_form.bind(this))
	},
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
        	var li = new Element('li', {'id': item['comment_id'] + '_comment'})
        	var span = new Element('span'); span.appendText(item['comment_created'] + ' - '+ item['u_fullname'])
        	li.grab(span)
        	if(item['u_avatar_img']){
        		li.grab(new Element('img', {'src':item['u_avatar_img'], 'width': 50, 'height':50}))
        	}
        	li.grab((new Element('p')).appendText(item['comment_name']))
        	
        	if(item['is_tree']){
        		var l = new Element('a', {'href': '#', 'html': 'Комментировать'})
        		l.addEvent('click', this.show_form.bind(this))
        		li.grab(l)
        	}
        	if(item['comment_parent_id']){
        		var parentCommentLiName = item['comment_parent_id'] + '_comment'
        		var parentCommentLi = $$('div.comments ul li#'+parentCommentLiName+'')
        		var ul = parentCommentLi.getElement('ul')
        		if(!ul[0]){
        			ul = new Element('ul')
        			parentCommentLi.grab(ul)
        		}
        		ul.grab(li)
        	}
        	else $$('div.comments').show().getElement('ul').grab(li);
        	$$('div.comments span')[0].innerHTML = $$('div.comments ul li').length
        	
        	this.componentElement.getElement('textarea[name=comment_name]').value = ''
    	}
    },
    show_form: function(event){
    	this.try_add_link_to_comment_all()
    	event.target.getParent().grab(this.form)
    	var parentId = this.form.getElement('input[name="parent_id"]')
    	if(!parentId){
	    	parentId = new Element('input', {'type':'hidden', 'name':'parent_id'})
	    	this.form.grab(parentId)
    	}
    	parentId.setProperty('value', parseInt(event.target.getParent('li').id))
    	
    	return false
    },
    try_add_link_to_comment_all: function(){
    	var c = $$('div.comments div.baseForm a')
    	if(!c[0]){
    		c = new Element('div', {'class':'baseForm'})
    		var l = new Element('a', {'href': '#', 'html': 'Комментировать Новость'})
    		l.addEvent('click', this.show_form_base.bind(this))
    		c.grab(l)
    		$$('div.comments').grab(c)
    	}
    	else{
    		$$('div.comments div').show()
    	}
    },
    show_form_base: function(){
    	$$('div.comments div.baseForm').hide()
    	var parentId = this.form.getElement('input[name="parent_id"]')
    	if(parentId) parentId.dispose()
    	$$('div.comments').grab(this.form)
    	
    	return false
    }
})
	