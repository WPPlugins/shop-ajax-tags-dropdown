<?php
/*
Plugin Name: Shop Ajax Tags Dropdown
Plugin URI: http://webtechdatasolution.com/shop-ajax-tags-dropdown.zip
Description: WooCommerce Ajax Tags adds an AJAX tag Dropdown widget to your WooCommerce shop.
Author: Alexhal
Author URI: http://www.poolgab.com/
Version: 0.1
*/
class ShopAjaxTagsDropdownWidget extends WP_Widget
{
function ShopAjaxTagsDropdownWidget()
{
	$widget_ops = array('classname' => 'ShopAjaxTagsDropdownWidget', 'description' => __( 'Shop Ajax Tags sorts products based on tags', 'shop-atags-dd' ) );
    $this->WP_Widget('ShopAjaxTagsDropdownWidget', __( 'Shop Ajax Tags Dropdown', 'shop-atags-dd' ), $widget_ops);
}

function form($instance)
{
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
	$display_type = isset( $instance['display_type'] ) ? (bool) $instance['display_type'] : false;
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'shop-atags-dd') ?></label>
		<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if (isset ( $instance['title'])) echo esc_attr( $instance['title'] );?>"  /></p>
	</p>
   	
<?php
}
 
function update($new_instance, $old_instance)
{
    $instance = $old_instance;
	$instance['title'] = strip_tags(stripslashes($new_instance['title']));
    return $instance;
}

function widget($args, $instance)
{	
	extract($args, EXTR_SKIP);
	global $woocommerce;
	$_attributes_array=array();
	if ( version_compare( WOOCOMMERCE_VERSION, "2.0.99" ) >= 0 ) {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
	} else {
		$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
	}

	if ( $attribute_taxonomies ) {
		foreach ( $attribute_taxonomies as $tax ) {

		   	$attribute = sanitize_title( $tax->attribute_name );
			if ( version_compare( WOOCOMMERCE_VERSION, "2.0.99" ) >= 0 ) {
				$taxonomy = wc_attribute_taxonomy_name( $attribute );
			} else {
				$taxonomy = $woocommerce->attribute_taxonomy_name( $attribute );
			}
	
			// create an array of product attribute taxonomies
			$_attributes_array[] = $taxonomy;
		}
	}
	
	if ( !is_post_type_archive('product') && !is_tax( array_merge( $_attributes_array, array('product_cat', 'product_tag') ) )) return;
	
	if( is_tax('product_tag')) return;
	echo $before_widget;
	
	if (!empty($instance['title'])) echo $before_title . $instance['title'] .$after_title;

	
	// Tag list
	$tags=get_terms('product_tag');
	
	$html = '<div class="woo-ajax-tags taglist">
	<select  class=tags  name=tag style="padding: 8px 10px;font-size: 16px;color: #555555;font-weight: 400;text-align: left;text-transform: capitalize;">
	<option>'. __('Select Tag','shop-atags-dd').'</option>';
	foreach ( $tags as $tag ) {
		$term_id=(int)$tag->term_id;
		$tag_link=get_term_link($term_id, 'product_tag');
		$html.="<option value='{$tag->slug}' id='{$tag->slug}' class='pakode' for='{$tag->slug}'>{$tag->name}</option>";
	}
	$html .= '</select></div>';
	echo $html;
    echo $after_widget;
  
}

}

/**
* Contents Wrapper
*
* Helps us know what elements to update with new content
**/
if(!function_exists('add_before_products_div')) { 
add_action('woocommerce_before_shop_loop','add_before_products_div');
add_action('woocommerce_after_shop_loop','add_after_products_div');
function add_before_products_div() {
	echo '<section id="products">';
}
function add_after_products_div() {
	echo '</section>';
}
}

function woo_ajax_tags_scripts() { ?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(function() {
			jQuery('.woo-ajax-tags .tags').on("change",function() {
				var allVals = [];
				jQuery('.woo-ajax-tags .pakode:selected').each(function() {
					allVals.push(jQuery(this).val());
				});

	 if(allVals=="") { var pathname = window.location.pathname; } else { pathname = '<?php echo site_url();?>/product-tag/'+allVals; }
	 var max = 0;
		max = jQuery('#products').outerHeight();
		jQuery('#products').fadeOut("fast", function() {
			jQuery('#products').html('<center style="min-height:'+max+'px;"><p>Loading...<br><?php 	echo '<img src="' . plugins_url( 'img/loading.gif' , __FILE__ ) . '"  alt="'.__('Loading...', 'shop-atags-dd') .'">';?></p></center>');
			jQuery('#products').css({'height':max}).fadeIn("slow", function() {});
		});
		jQuery('#products').load(pathname+'/#products #products');
	jQuery(this).addClass('clicked');
		});
	});
});
</script>
<?php }
add_action('wp_footer','woo_ajax_tags_scripts');

load_plugin_textdomain('shop-atags-dd', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
add_action( 'widgets_init', create_function('', 'return register_widget("ShopAjaxTagsDropdownWidget");') );
?>