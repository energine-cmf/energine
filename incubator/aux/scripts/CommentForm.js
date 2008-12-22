ScriptLoader.load('ValidForm.js');

var CommentForm = ValidForm.extend({
	
	initialize: function (element){
		this.singleTemplate = element.getProperty('single_template');
		this.linkedComponent = $(element.getProperty('linked'));
		this.parent(element);
		this.form.removeEvent('submit', this.validateForm.bind(this));
		this.form.addEvent('submit', this.send.bind(this));
		
	},
	send:function(event){
		if(this.validateForm(event)){
			event.preventDefault();
			this.request(
				this.singleTemplate + this.form.getProperty('action'), 
				this.form.toQueryString(),
				this.addComment.bind(this)
			);
		}
	},
	addComment: function(response){
		window.location.href = window.location.href;
		/*var li = new Element('li');
		var div;
		var dl = new Element('dl',{
			'class':'clearfix'
		}).injectInside(li);
		
		new Element('dt').setHTML(response.data.comment_email).injectInside(dl);
		new Element('dd').setHTML(response.data.comment_username).injectInside(li);
		new Element('div').setHTML(response.data.comment_text).injectInside(li);
		li.injectTop(this.linkedComponent);
*/
	}
	
});

CommentForm.implement(Request);