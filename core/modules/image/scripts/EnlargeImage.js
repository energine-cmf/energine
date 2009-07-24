var EnlargeImage = new Class({
	/* 	This Mootools class is made by the fifth generation Lutz, wizzard in the Algo solarsystem.
		Use this with caution for it is entangled with magic from the old times.

		This Mootools class is licensed under the MIT License.

		v. 1.1
	*/
	initialize: function(element,options) {
		this.element = element;
		this.options = options;
		this.enlarge();
	},
	enlarge: function() {
		// There must only be one.
		if($("EnlargedImage")) $("EnlargedImage").dispose();

		// Collect position and size information from the element thou clicked upon.
		var coordinates = $(this.element).getCoordinates();
		var coordinatesTop = coordinates.top;
		var coordinatesLeft = coordinates.left;
		var coordinatesWidth = coordinates.width;
		var coordinatesHeight = coordinates.height;

		// Create an element which will shine upon thee and be embraced by the browser as a larger image.
		var image = new Asset.image(this.element.getProperty('main'), {
			'styles': {
				'position': 'absolute',
				'top': '0px',
				'z-index': '-99',
				'cursor': 'pointer'
			},
			'id': 'EnlargedImage'
		});
		// Let the image turn alive and appear into your browser.
		if(document.body.appendChild(image)) {
			// The size of the image is needed for the grand plan and will be saved here.
			imageWidth = this.element.getProperty('real_width');
			imageHeight = this.element.getProperty('real_height');

			// As will the width of the browser.
			var browserWidth = window.getWidth();

			// Let new styles shine down and entagle the image.
			image.setStyles({
				'top': coordinatesTop+"px",
				'left': coordinatesLeft+"px",
				'width': coordinatesWidth+"px",
				'height': coordinatesHeight+"px",
				'border': '1px solid silver',
				'z-index': '+99'
			})
			// Calculates the image position if though choose too.
			var position;
			if(this.options.position == "this") position = Math.abs((coordinatesTop-(imageHeight/2)+(coordinatesHeight/2)))+'px';
			else position = this.options.position+"px";

			// Make it grow,
			// make it rise,
			// it must flow,
			// really nice.
			var animationDuration = this.options.duration;
			var transStyle = Fx.Transitions.Back.easeInOut;
			var enlargeImage = new Fx.Morph(image,{duration: animationDuration,transition:transStyle, onComplete: function() {

				image.addEvent('click', function() {
					var contractImage = new Fx.Morph(image,{duration: animationDuration,transition:transStyle,onComplete: function() {
						image.dispose();
					}});
					contractImage.start({
						'top': [position,coordinatesTop],
						'left': [((browserWidth/2)-(imageWidth/2)),coordinatesLeft],
						'width': [imageWidth,coordinatesWidth],
						'height': [imageHeight,coordinatesHeight]
					});
				});
			}});
			enlargeImage.start({
				'top': [coordinatesTop,position],
				'left': [coordinatesLeft,((browserWidth/2)-(imageWidth/2))],
				'width': [coordinatesWidth,imageWidth],
				'height': [coordinatesHeight,imageHeight]
			});
		}
	}
});