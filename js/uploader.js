jQuery(document).ready(function($) {

	$('#upload_image_button').click(function() {
		formfield = $('#link_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});

	window.send_to_editor = function(html) {
		imgurl = $('img',html).attr('src');
		$('#link_image').val(imgurl);
		imgsrc = '<img src="'+imgurl+'" class="link-featured-image">';
		$('#my-link-img').html(imgsrc);
		tb_remove();
	}
	
	$('#my-link-img .remove_image').live('click', function(){
		
		$('#my-link-img').html('');
		$('#link_image').val('');
		return false;
		
	});
});
