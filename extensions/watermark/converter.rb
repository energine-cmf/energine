#!/usr/bin/env ruby

require 'RMagick'
require 'optparse'
require 'ostruct'
require '/home/pavka/htdocs/malinar/core/ext/watermark/watermark.rb'

options = OpenStruct.new
options.recursive = false
options.destination = false
options.text = 'energine.org'


opts = OptionParser.new{
	|opts|
	opts.banner  = 'Usage: converter [options] FILE_NAME'
	opts.on('-r', '--recursive', 'Recursive walking'){
		|opt| 
		options.recursive  = true
	}

	opts.on('-t', '--text=WATERMARK TEXT', String, 'Watermark text'){
		|opt|
		options.text = opt
	}
	opts.on_tail('-?', "-h", "--help", "Show this usage statement") do |h|
		puts opts 
		exit!
	end

}
begin 
	opts.parse!(ARGV)

	if ARGV.empty? 
		raise Exception.new('No file specified')
	elsif !File.exists?(options.fileName = ARGV[0]) 
		raise Exception.new('File does not exists')
	end

	ImgList = Magick::ImageList.new
	Images = if File.directory?(options.fileName) then Dir.glob(File.join(options.fileName,'**','[^product_thumb_img_]*.{jpg,png,gif}'))
				else [options.fileName]
				end

	
		Images.each{
			|fileName|
			if File.writable?(fileName)
				ImgList.read(fileName) 
			else
				puts 'Notice: file '<<fileName<<' is not writable'
			end
		}
		

	ImgList.each{
		|image|
			watermark = Watermark.new(image)
			watermark.printWatermark(options.text).write(image.filename){ self.quality = 100;}
			puts image.filename << "\r\n"
	}
rescue Exception => e
	puts 'Error:'<<e.message
	puts opts
	exit
end