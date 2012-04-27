<?php
/*
Plugin Name: Featured Link Image
Plugin URI: 
Text Domain: feature-link-image
Description: Add a meta box in the link add/edit page for easily select/upload an image for the Bookmark
Author: Rodolfo Buaiz
Author URI: http://rodbuaiz.com/
Version: 1.0
License: GPL
*/


/**
 * Init
 */
if (is_admin()) {
	add_action('load-link-manager.php', 'brsfl_make_bookmarkImageColumn');
	add_action('load-link-add.php', 'brsfl_call_bookmarkImageMetaBox');
	add_action('load-link.php', 'brsfl_call_bookmarkImageMetaBox');
	add_action('admin_head', 'brsfl_make_wpAdminHead');
}

/*
 * Init Image Columns
 */
function brsfl_make_bookmarkImageColumn() {
	add_filter('manage_link-manager_columns','brsfl_add_link_id_column');
	add_action('manage_link_custom_column','brsfl_add_link_id_column_data',10,2);

	/* TODO: THE 'REQUEST' IS NOT WORKING FOR LINK-MANAGER.PHP */
	//	add_filter( 'request', 'brsfl_thumb_column_orderby' );
	//	add_filter( 'manage_link-manager_sortable_columns', 'brsfl_thumb_column_register_sortable' );
}

/**
 * Init Meta Box
 */
function brsfl_call_bookmarkImageMetaBox() {
	return new brsfl_bookmarkImageMetaBox();
}


/*
 * Media Upload iframe styling
 */
function brsfl_make_wpAdminHead() {
	global $current_screen;

	// Load only in Media Upload window and if fli_type defined
	if ($current_screen->id == 'media-upload' && isset($_GET['fli_type'])) {

		// Hide many image details and From Url insert option
		echo '<style type="text/css">#media-upload-header #sidemenu li#tab-type_url,tr.post_excerpt,tr.post_content,tr.url,tr.align,tr.image_alt, tr.post_title.form-required{display: none !important;}</style>';

		// Refresh upload screen every half second
		// Changes the "Insert into post" text and hides button "Save all changes"
		$select = __("Select link image", 'fli');
		$tab     = isset($_GET['tab']) ? $_GET['tab'] : "type";
		$refresh = ($tab == 'type') ? 'var mtt_t = setInterval(function(){$("#media-items").each(setButtonNames); $("p.savebutton").css("display", "none");}, 500);' : '';

		$js = <<<EOM
					<script type="text/javascript">
					function setButtonNames() {
						jQuery(this).find('.savesend .button').val('{$select}');
					}
					jQuery(document).ready(function($){
						$('#media-items').each(setButtonNames);
						{$refresh}
					});
					</script>
EOM;
		echo $js;
	}

}



/**
 * Thumbnail Column functions
 */
function brsfl_add_link_id_column($link_columns) {
	$link_columns['thumbnail'] = __('Thumbnail', 'fli');

	return $link_columns;
}

function brsfl_add_link_id_column_data($column_name, $id) {
	if ($column_name == 'thumbnail') {
		$val = get_bookmark_field( 'link_image', $id);
		$img = '<img src="'.$val.'" style="max-width:50px">';
		echo $img;
	}
}

function brsfl_thumb_column_register_sortable( $columns ) {
	$columns['thumbnail'] = 'thumbnail';

	return $columns;
}

function brsfl_thumb_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'thumbnail' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'link_image',
			'orderby' => 'meta_value_num'
		) );
	}

	return $vars;
}




/**
 * Meta Box Class
 */
class brsfl_bookmarkImageMetaBox {

	public function __construct() {
		define('THIS_PLUG_DIR', plugin_dir_url(__FILE__));

		load_plugin_textdomain('fli', null, 'featured-link-image/lang');

		add_action('add_meta_boxes', array(&$this, 'add_fli_meta_box'));
		add_action('add_meta_boxes_link', array(&$this, 'add_fli_meta_box'));

		add_action('admin_print_scripts', array(&$this, 'my_admin_scripts'));
		add_action('admin_print_styles', array(&$this, 'my_admin_styles'));
	}

	public function my_admin_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script('fli-uplaod-js', THIS_PLUG_DIR . 'js/uploader.js', array('jquery', 'media-upload', 'thickbox'));
		wp_enqueue_script('fli-uplaod-js');
	}

	public function my_admin_styles() {
		wp_enqueue_style('thickbox');
		wp_register_style('fli-uplaod-css', THIS_PLUG_DIR . 'css/uploader.css');
		wp_enqueue_style('fli-uplaod-css');
	}

	/**
	 * Adds the meta box container
	 */
	public function add_fli_meta_box() {
		add_meta_box(
			'featured_link_image_meta_box'
			, __('Featured Link Image', 'fli')
			, array(&$this, 'render_meta_box_content')
			, 'link'
			, 'side'
			, 'default'
		);
	}


	/**
	 * Render Meta Box content
	 */
	public function render_meta_box_content() {
		global $link;
		global $firephp;$firephp->log(isset($link->link_image),'ISSET');
		$img            = (isset($link->link_image) && '' !== $link->link_image) ? '<img src="' . $link->link_image . '" class="link-featured-image">' : '';
		$class_hide   = ('' === $img) ? 'hide-image-text' : '';
		$class_show      = ('' !== $img) ? 'hide-image-text' : '';
		$spanimg        = sprintf('<div id="my-link-img">%s</div>', $img);
		?>
	<table>
	<tr>
		<td><span class="link-help-text <?php echo $class_show; ?>"><?php _e('After selecting/uploading, the image address will be inserted inside the Advanced->Image Address field.', 'fli'); ?></span></td>
	</tr>
	<tr>
		<td><a id="upload_image_button" class="<?php echo $class_show; ?>" href="#"><?php _e('Set link image', 'fli'); ?></a></td>
	</tr>
	<tr>
		<td><?php echo $spanimg; ?></td>
	</tr>
	<tr>
		<td><a href="#" id="remove-image-text" class="<?php echo $class_hide; ?>"><?php _e('Remove image', 'fli'); ?></a></td>
	</tr>
	</table>
	<?php
	}
}
