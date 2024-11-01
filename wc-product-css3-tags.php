<?php

/*
Plugin Name: WooCommerce Product CSS3 Tags
Plugin URI: http://terrytsang.com/shop/shop/woocommerce-product-css3-tags/
Description: Apply CSS3 Tags style design for WooCommerce product meta (SKU, categories and tags)
Version: 1.0.4
Author: Terry Tsang
Author URI: http://terrytsang.com
*/

/*  Copyright 2012 Terry Tsang (email: terrytsang811@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


// Define plugin name.
define('wc_product_css3_tags_plugin_name', 'WooCommerce Product CSS3 Tags');

// Checks if the WooCommerce plugins is installed and active.
if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
	if(!class_exists('WooCommerce_Product_CSS3_Tags')){
		class WooCommerce_Product_CSS3_Tags{

			public static $plugin_prefix;
			public static $plugin_url;
			public static $plugin_path;
			public static $plugin_basefile;

			var $tab_name;
			var $hidden_submit;
			var $current_tab;
			var $color_types;
			
			/**
			 * initialize this plugin
			 */
			public function __construct(){
				global $woocommerce;
				
				self::$plugin_prefix = 'wc_product_css3_tags_';
				self::$plugin_basefile = plugin_basename(__FILE__);
				self::$plugin_url = plugin_dir_url(self::$plugin_basefile);
				self::$plugin_path = trailingslashit(dirname(__FILE__));
				
				$this->tab_name = 'wc-product-css3-tags';
				$this->hidden_submit = self::$plugin_prefix . 'submit';
				
				$this->color_schemes = array('default' => 'Default', 'red' => 'Red', 'orange' => 'Orange', 'blue' => 'Blue', 'green' => 'Green', 'lime' => 'Lime', 'lightblue' => 'Light Blue', 'silver' => 'Silver');
				
				add_action('woocommerce_init', array(&$this, 'init'));

			}

			
			/**
			 * Load stylesheet for the page
			 */
			public function custom_plugin_stylesheet() {
				wp_register_style( 'product-css3-stylesheet', plugins_url('/css/style.css', __FILE__) );
				wp_enqueue_style( 'product-css3-stylesheet' );
			}
			
			/**
			 * Load javascript for the page
			 */
			public function custom_plugin_scripts() {
				
				//only apply to product page
				if(is_product())
				{
					//show custom product meta
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
					add_action( 'woocommerce_single_product_summary', array( &$this, 'custom_template_single_meta' ), 40 );
				}
				
				//wp_enqueue_style( 'farbtastic' );
				//wp_enqueue_script( 'farbtastic' );
				//wp_enqueue_script( 'product-css3-script', plugins_url('/js/script.js', __FILE__));
			}

			
			/**
			 * Init WooCommerce Product CSS3 Tags
			 */
			public function init(){
				global $woocommerce;
				
				//load stylesheet
				add_action( 'wp_enqueue_scripts', array(&$this, 'custom_plugin_stylesheet') );
					
				//load javascript
				add_action( 'wp_enqueue_scripts', array( &$this, 'custom_plugin_scripts' ) );
					
				//add menu link
				add_action( 'admin_menu', array( &$this, 'add_menu_tags' ) );
			}
			
			
			/**
			 * Add a menu link to the woocommerce section menu
			 */
			function add_menu_tags() {
				$wc_page = 'woocommerce';
				$comparable_settings_page = add_submenu_page( $wc_page , __( 'Product CSS3 Tags', $this->tab_name ), __( 'Product CSS3 Tags', $this->tab_name ), 'manage_options', 'product-css3tags', array(
					&$this,
					'create_settings_tags'
				));
			}
			
			
			/**
			 * Create the settings page content
			 */
			public function create_settings_tags() {
			 
				// If form was submitted 
				if ( isset( $_POST['submitted'] ) ) 
				{			
					check_admin_referer( $this->tab_name );
					
					$options = array(
							'product_css3_tags_enabled' => '',
							'product_css3_tags_showsku' => '',
							'product_css3_tags_showcat' => '',
							'product_css3_tags_showtag' => '',
							'product_css3_tags_stitched' => '',
							'product_css3_tags_color' => '',
							'product_css3_tags_text_sku' => ''
					);
					
					$this->options['product_css3_tags_enabled'] = ! isset( $_POST['product_css3_tags_enabled'] ) ? '1' : $_POST['product_css3_tags_enabled'];
					$this->options['product_css3_tags_showsku'] = ! isset( $_POST['product_css3_tags_showsku'] ) ? '1' : $_POST['product_css3_tags_showsku'];
					$this->options['product_css3_tags_showcat'] = ! isset( $_POST['product_css3_tags_showcat'] ) ? '1' : $_POST['product_css3_tags_showcat'];
					$this->options['product_css3_tags_showtag'] = ! isset( $_POST['product_css3_tags_showtag'] ) ? '1' : $_POST['product_css3_tags_showtag'];
					$this->options['product_css3_tags_stitched'] = ! isset( $_POST['product_css3_tags_stitched'] ) ? '0' : $_POST['product_css3_tags_stitched'];
					$this->options['product_css3_tags_color'] = ! isset( $_POST['product_css3_tags_color'] ) ? 'default' : $_POST['product_css3_tags_color'];
					$this->options['product_css3_tags_text_sku'] = ! isset( $_POST['product_css3_tags_text_sku'] ) ? 'default' : $_POST['product_css3_tags_text_sku'];
					
					foreach($options as $field => $value)
					{
						$option = get_option( $field );
						
						if($option != $this->options[$field])
							update_option( $field, $this->options[$field] );
					}
					
					// Show message
					echo '<div id="message" class="updated fade"><p>' . __( 'Product CSS3 Tags options saved.', $this->tab_name ) . '</p></div>';
				} 
				
				$product_css3_tags_enabled 		= get_option( 'product_css3_tags_enabled' );
				$product_css3_tags_showsku  	= get_option( 'product_css3_tags_showsku' );
				$product_css3_tags_showcat 		= get_option( 'product_css3_tags_showcat' );
				$product_css3_tags_showtag 		= get_option( 'product_css3_tags_showtag' );
				$product_css3_tags_stitched 	= get_option( 'product_css3_tags_stitched' );
				$product_css3_tags_color		= get_option( 'product_css3_tags_color' );
				$product_css3_tags_text_sku		= get_option( 'product_css3_tags_text_sku' );
				
				$checked_value1 = '';
				$checked_value2 = '';
				$checked_value3 = '';
				$checked_value4 = '';
				$checked_value5 = '';
				
				if($product_css3_tags_enabled)
					$checked_value1 = 'checked="checked"';	
				
				if($product_css3_tags_showsku)
					$checked_value2 = 'checked="checked"';
					
				if($product_css3_tags_showcat)
					$checked_value3 = 'checked="checked"';
				
				if($product_css3_tags_showtag)
					$checked_value4 = 'checked="checked"';
				
				if($product_css3_tags_stitched)
					$checked_value5 = 'checked="checked"';
				
				$actionurl = $_SERVER['REQUEST_URI'];
				$nonce = wp_create_nonce( $this->tab_name );
				
						
				// Configuration Page
						
				?>
				<div id="icon-options-general" class="icon32"></div>
				<h3><?php _e( 'Product CSS3 Tags Options', $this->tab_name); ?></h3>
				
				
				<table width="90%" cellspacing="2">
				<tr>
					<td width="70%">
						<form action="<?php echo $actionurl; ?>" method="post">
						<table class="widefat fixed" cellspacing="0">
								<thead>
									<th width="35%">Option</th>
									<th>Setting</th>
								</thead>
								<tbody>
									<tr>
										<td>Enabled Product CSS3 Design</td>
										<td>
											<input class="checkbox" name="product_css3_tags_enabled" id="product_css3_tags_enabled" value="0" type="hidden">
											<input class="checkbox" name="product_css3_tags_enabled" id="product_css3_tags_enabled" value="1" <?php echo $checked_value1; ?> type="checkbox">
										</td>
									</tr>
									<tr>
										<td>Show Product SKU</td>
										<td>
											<input class="checkbox" name="product_css3_tags_showsku" id="product_css3_tags_showsku" value="0" type="hidden">
											<input class="checkbox" name="product_css3_tags_showsku" id="product_css3_tags_showsku" value="1" <?php echo $checked_value2; ?> type="checkbox">
										</td>
									</tr>
									<tr>
										<td>Show Product Categories</td>
										<td>
											<input class="checkbox" name="product_css3_tags_showcat" id="product_css3_tags_showcat" value="0" type="hidden">
											<input class="checkbox" name="product_css3_tags_showcat" id="product_css3_tags_showcat" value="1" <?php echo $checked_value3; ?> type="checkbox">
										</td>
									</tr>
									<tr>
										<td>Show Product Tags</td>
										<td>
											<input class="checkbox" name="product_css3_tags_showtag" id="product_css3_tags_showtag" value="0" type="hidden">
											<input class="checkbox" name="product_css3_tags_showtag" id="product_css3_tags_showtag" value="1" <?php echo $checked_value4; ?> type="checkbox">
										</td>
									</tr>
									<tr>
										<td>Use CSS3 Stitched Effect<br /><span style="color:#ccc;">(apply for SKU and categories)</span><br /><img src="<?php echo plugins_url('/images/stitched.png', __FILE__); ?>" title="CSS3 Stitched Effect" alt="CSS3 Stitched Effect" /></td>
										<td>
											<input class="checkbox" name="product_css3_tags_stitched" id="product_css3_tags_stitched" value="0" type="hidden">
											<input class="checkbox" name="product_css3_tags_stitched" id="product_css3_tags_stitched" value="1" <?php echo $checked_value5; ?> type="checkbox">
										</td>
									</tr>
									<tr>
										<td>Product Tags Color Scheme<br /><img src="<?php echo plugins_url('/images/css3-tag.png', __FILE__); ?>" title="CSS3 Tag Design" alt="CSS3 Tag Design" /></td>
										<td>
											<select name="product_css3_tags_color">
											<?php foreach($this->color_schemes as $scheme_option => $scheme_name): ?>
												<?php if($scheme_option == $product_css3_tags_color): ?>
													<option selected="selected" value="<?php echo $scheme_option; ?>"><?php echo $scheme_name; ?></option>
												<?php else: ?>
													<option value="<?php echo $scheme_option; ?>"><?php echo $scheme_name; ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
											</select>
										</td>
									</tr>
									<tr>
										<td>Change SKU text</td>
										<td>
											<input type="text" id="product_css3_tags_text_sku" name="product_css3_tags_text_sku" value="<?php echo $product_css3_tags_text_sku; ?>" />
										</td>
									</tr>
									<tr>
										<td colspan=2">
											<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options'); ?>" id="submitbutton" />
											<input type="hidden" name="submitted" value="1" /> 
											<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce; ?>" />
										</td>
									</tr>
								
								</tbody>
						</table>
						</form>
					
					</td>
					
					<td width="30%" style="background:#ececec;padding:10px 5px;">
						<p><b>WooCommerce Product CSS3 Tags</b> is a FREE woocommerce plugin developed by <a href="http://www.terrytsang.com" target="_blank" title="Terry Tsang - a php and symfony developer">Terry Tsang</a>. This plugin aims to implement pure CSS3 tag and stitched design for product SKU, categories and tags at the product page.</p>
						
						<?php
							$get_pro_image = self::$plugin_url . '/images/get-more-extensions.png';
						?>
						<div><a href="http://terrytsang.com/shop/" target="_blank" title="Free/Premium WooCommerce Extensions by Terry Tsang"><img src="<?php echo $get_pro_image; ?>" border="0" /></a></div>
					
						<p>Vist <a href="http://www.terrytsang.com/shop" target="_blank" title="Premium &amp; Free Extensions/Plugins for E-Commerce by Terry Tsang">My Shop</a> to get more free and premium extensions/plugins for your ecommerce platform.</p>
					
						<h3>Spreading the Word</h3>
	
						<ul style="list-style:dash">If you find this plugin helpful, you can:	
							<li>- Write and review about it in your blog</li>
							<li>- Share on your facebook, twitter, google+ and others</li>
							<li>- Or make a donation</li>
						</ul>
						
						<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LJWSJDBBLNK7W" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" alt="" /></a>

						<h3>Thank you for your support!</h3>
					</td>
					
				</tr>
				</table>
				
				
				<br />
				
			<?php
			}
			
			
			/**
			 * Create the settings page content
			 */
			function get_options() {
				$options = array(
						'product_css3_tags_enabled' => '',
						'product_css3_tags_showsku' => '',
						'product_css3_tags_showcat' => '',
						'product_css3_tags_showtag' => '',
  						'product_css3_tags_stitched' => '',
						'product_css3_tags_color' => '',
						'product_css3_tags_text_sku' => '',
				);
				$array_options = array();
					
				
				foreach($options as $field => $value)
				{
					$array_options[$field] = get_option( $field );
				}
					
				return $array_options;
			}
			
			/**
			 * Show customized product SKU, categories and tags
			 */
			function custom_template_single_meta()
			{
				global $post, $product;
					
				$this->options = $this->get_options();
				
				$product_css3_tags_enabled 		= $this->options['product_css3_tags_enabled'];
				$product_css3_tags_showsku  	= $this->options['product_css3_tags_showsku'];
				$product_css3_tags_showcat 		= $this->options['product_css3_tags_showcat'];
				$product_css3_tags_showtag 		= $this->options['product_css3_tags_showtag'];
				$product_css3_tags_stitched 	= $this->options['product_css3_tags_stitched'];
				$product_css3_tags_color		= $this->options['product_css3_tags_color'];
				$product_css3_tags_text_sku		= is_null($this->options['product_css3_tags_text_sku']) ? 'SKU' : $this->options['product_css3_tags_text_sku'];

			
				if( $product_css3_tags_enabled ):
				?>
					<div class="product_meta">
						
						<?php if($product_css3_tags_showsku): ?>
							<?php if ( $product->is_type( array( 'simple', 'variable' ) ) && get_option('woocommerce_enable_sku') == 'yes' && $product->get_sku() ) : ?>
								<?php if($product_css3_tags_stitched): ?>
									<div itemprop="productID" class="stitched-sku">
								<?php else: ?>
									<div itemprop="productID" class="sku">
								<?php endif; ?>
								
								<?php _e($product_css3_tags_text_sku.':', 'woocommerce'); ?> <?php echo $product->get_sku(); ?></div>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if($product_css3_tags_showcat): ?>
							<?php if($product_css3_tags_stitched): ?>
								<div style="display:block;" class="stitched-cat">
							<?php else: ?>
								<div style="display:block;">
							<?php endif; ?>
								<?php echo $product->get_categories( ', ', ' <span class="posted_in">'.__('Category:', 'woocommerce').' ', '.</span>'); ?>
							</div>
						<?php endif; ?>
						
						<?php if($product_css3_tags_showtag): ?>
							<div style="display:block;">
								<ul class="tags">
									<?php //echo $product->get_tags( ' ', ' <li>', '</li>'); ?>
									<?php 
										if($product_css3_tags_color != 'default')
											echo get_the_term_list( $post->ID, 'product_tag', '<li class="'.$product_css3_tags_color.'">', '</li><li class="'.$product_css3_tags_color.'">', '</li>' ); 
										else
											echo get_the_term_list( $post->ID, 'product_tag', '<li>', '</li><li>', '</li>' );
									?>
								</ul>
							</div>
						<?php endif; ?>
						
					</div>
					
				<?php
				else:
				?>
				<div class="product_meta">

					<?php if ( $product->is_type( array( 'simple', 'variable' ) ) && get_option('woocommerce_enable_sku') == 'yes' && $product->get_sku() ) : ?>
						<span itemprop="productID" class="sku"><?php _e('SKU:', 'woocommerce'); ?> <?php echo $product->get_sku(); ?>.</span>
					<?php endif; ?>
				
					<?php echo $product->get_categories( ', ', ' <span class="posted_in">'.__('Category:', 'woocommerce').' ', '.</span>'); ?>
				
					<?php echo $product->get_tags( ', ', ' <span class="tagged_as">'.__('Tags:', 'woocommerce').' ', '.</span>'); ?>
				
				</div>
				<?php
				endif;
			}
	
	
		}
	}

	/* 
	 * Instantiate plugin class and add it to the set of globals.
	 */
	$woocommerce_product_css3_tags = new WooCommerce_Product_CSS3_Tags();
}
else{
	add_action('admin_notices', 'wc_product_css3_tags_notice');
	function wc_product_css3_tags_notice(){
		global $current_screen;
		if($current_screen->parent_base == 'plugins'){
			echo '<div class="error"><p>For your information, '.__(wc_product_css3_tags_plugin_name.' requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be installed and activated. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.').'</p></div>';
		}
	}
}
?>