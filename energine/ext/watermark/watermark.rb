#!/usr/bin/env ruby

require 'RMagick'

class Watermark
	attr_writer :rotation
	def initialize(image)
		@image = image
		@mark = Magick::Image.new(@image.columns, @image.rows){self.background_color = "blue"}
		@gc = Magick::Draw.new{    
			self.font = 'font.ttf'
			self.gravity = Magick::SouthGravity
			self.pointsize = 60
		}
		@gc.rotation = 0
		@gc.stroke = 'none'
	end

	def printWatermark(text)
		if @image.rows > @image.columns*2
			@gc.rotation = 90
			@gc.gravity = Magick::CenterGravity
		end
		@gc.annotate(@mark, 0, 0, 0, 0, text)
		@mark = @mark.shade(true, 310, 30)
		@image.composite!(@mark, Magick::CenterGravity, Magick::HardLightCompositeOp)
		return @image
	end
end