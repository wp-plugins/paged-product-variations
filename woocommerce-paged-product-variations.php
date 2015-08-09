<?php

/*
	Plugin Name: WooCommerce Paged Product Variations
	Plugin URI: http://designloaf.com
	Description: A plugin designed to make variation boxes more manageable for large store owners.
	Version: 1.0.2
	Author: Logan Graham
	Author URI: http://twitter.com/loganpgraham
	License: GPL2
*/

if(!defined('ABSPATH')) exit;

class WooCommerce_Paged_Product_Variations {

	function __construct() {
		// Return no posts if on the product edit screen
		if( is_admin() && static::is_product_edit_screen() ){
			add_action( 'pre_get_posts' , function($query){
				/* @var $query WP_Query */
				if( $query->get('post_type','product_variation') ){
					$query->set('post_parent',-1);
				}
			} );

			add_action( 'admin_enqueue_scripts' , array($this,'enqueue_scripts'));
		}

		add_action( 'wp_ajax_woocommerce_paged_get_pages', array( $this, 'get_number_of_pages' ) );
		add_action( 'wp_ajax_woocommerce_paged_get_variations', array( $this, 'get_variations_by_page' ) );
	}

	/**
	 * Return number of pages needed for the toolbars
	 */
	function get_number_of_pages(){

		$post_id = intval( $_POST['post_id'] );

		$args = array(
			'post_type'      => 'product_variation',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => apply_filters( 'paged_variations_posts_per_page', 10 ),
			'orderby'        => 'menu_order',
			'order'          => 'asc',
			'post_parent'    => $post_id
		);

		$variations = new WP_Query( $args );

		wp_reset_postdata();

		echo $variations->max_num_pages;

		die();
	}

	/**
	 * Echo the variations for passed post_id for AJAX usage
	 */
	function get_variations_by_page(){

		$post_id = intval( $_POST['post_id'] );

		$page_number = intval( $_POST['page_number'] );

		// Get Attributes
		$attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

		// Setup Tax Classes
		$tax_classes           = WC_Tax::get_tax_classes();
		$tax_class_options     = array();
		$tax_class_options[''] = __( 'Standard', 'woocommerce' );

		if ( $tax_classes ) {

			foreach ( $tax_classes as $class ) {
				$tax_class_options[ sanitize_title( $class ) ] = esc_attr( $class );
			}
		}

		// Backorder and Stock Status
		$backorder_options = array(
			'no'     => __( 'Do not allow', 'woocommerce' ),
			'notify' => __( 'Allow, but notify customer', 'woocommerce' ),
			'yes'    => __( 'Allow', 'woocommerce' )
		);

		$stock_status_options = array(
			'instock'    => __( 'In stock', 'woocommerce' ),
			'outofstock' => __( 'Out of stock', 'woocommerce' )
		);


		// Get parent data
		$parent_data = array(
			'id'                   => $post_id,
			'attributes'           => $attributes,
			'tax_class_options'    => $tax_class_options,
			'sku'                  => get_post_meta( $post_id, '_sku', true ),
			'weight'               => wc_format_localized_decimal( get_post_meta( $post_id, '_weight', true ) ),
			'length'               => wc_format_localized_decimal( get_post_meta( $post_id, '_length', true ) ),
			'width'                => wc_format_localized_decimal( get_post_meta( $post_id, '_width', true ) ),
			'height'               => wc_format_localized_decimal( get_post_meta( $post_id, '_height', true ) ),
			'tax_class'            => get_post_meta( $post_id, '_tax_class', true ),
			'backorder_options'    => $backorder_options,
			'stock_status_options' => $stock_status_options
		);

		if ( ! $parent_data['weight'] ) {
			$parent_data['weight'] = wc_format_localized_decimal( 0 );
		}

		if ( ! $parent_data['length'] ) {
			$parent_data['length'] = wc_format_localized_decimal( 0 );
		}

		if ( ! $parent_data['width'] ) {
			$parent_data['width'] = wc_format_localized_decimal( 0 );
		}

		if ( ! $parent_data['height'] ) {
			$parent_data['height'] = wc_format_localized_decimal( 0 );
		}

		// Get variations
		$args = array(
			'post_type'      => 'product_variation',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => apply_filters( 'paged_variations_posts_per_page', 10 ),
			'orderby'        => 'ID',
			'order'          => 'asc',
			'post_parent'    => $post_id,
			'paged'          => $page_number
		);

		$variations = get_posts( $args );
		$loop = 0;

		if ( $variations ) {

			foreach ( $variations as $variation ) {
				$variation_id     = absint( $variation->ID );
				$variation_meta   = get_post_meta( $variation_id );
				$variation_data   = array();
				$shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
				$variation_fields = array(
					'_sku'                   => '',
					'_stock'                 => '',
					'_regular_price'         => '',
					'_sale_price'            => '',
					'_weight'                => '',
					'_length'                => '',
					'_width'                 => '',
					'_height'                => '',
					'_download_limit'        => '',
					'_download_expiry'       => '',
					'_downloadable_files'    => '',
					'_downloadable'          => '',
					'_virtual'               => '',
					'_thumbnail_id'          => '',
					'_sale_price_dates_from' => '',
					'_sale_price_dates_to'   => '',
					'_manage_stock'          => '',
					'_stock_status'          => '',
					'_backorders'            => null,
					'_tax_class'             => null
				);

				foreach ( $variation_fields as $field => $value ) {
					$variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
				}

				// Add the variation attributes
				foreach ( $variation_meta as $key => $value ) {
					if ( false !== strpos( $key, 'attribute_' ) ) {
						$variation_data[ $key ] = $value;
					}
				}

				// Formatting
				$variation_data['_regular_price'] = wc_format_localized_price( $variation_data['_regular_price'] );
				$variation_data['_sale_price']    = wc_format_localized_price( $variation_data['_sale_price'] );
				$variation_data['_weight']        = wc_format_localized_decimal( $variation_data['_weight'] );
				$variation_data['_length']        = wc_format_localized_decimal( $variation_data['_length'] );
				$variation_data['_width']         = wc_format_localized_decimal( $variation_data['_width'] );
				$variation_data['_height']        = wc_format_localized_decimal( $variation_data['_height'] );
				$variation_data['_thumbnail_id']  = absint( $variation_data['_thumbnail_id'] );
				$variation_data['image']          = $variation_data['_thumbnail_id'] ? wp_get_attachment_thumb_url( $variation_data['_thumbnail_id'] ) : '';
				$variation_data['shipping_class'] = $shipping_classes && ! is_wp_error( $shipping_classes ) ? current( $shipping_classes )->term_id : '';

				// Stock BW compat
				if ( '' !== $variation_data['_stock'] ) {
					$variation_data['_manage_stock'] = 'yes';
				}

				// Minimal backwards compat for older versions
				if(defined('WC_VERSION') && version_compare(WC_VERSION, '2.3.0','<')){
					// Add some backwards compatability for additional fields (post status, etc)
					$variation_data['variation_post_status'] = $variation->post_status;

					// Add back additional meta fields which were available prior to 2.3.x
					$variation_data = array_merge($variation_meta,$variation_data);
					$variation_data['image_id'] = absint( $variation_data['_thumbnail_id'] );

					// Fill fields like 2.3.x and include some additional ones for pre 2.3.x
					extract($variation_data);
				}

				$file = WP_PLUGIN_DIR . '/woocommerce/includes/admin/meta-boxes/views/html-variation-admin.php' ;
				include($file);

				$loop++;
			}
		}

		die();
	}

	/**
	 * Add scripts required for paged variations
	 */
	function enqueue_scripts(){
		wp_enqueue_script( 'wc_paged_variations' , plugins_url( '/assets/js/wc-paged-variations.js' , __FILE__ ) , array('jquery') , '1.0.1' );
		?>
		<style>
			.toolbar.variation_pages {
				text-align: center;
			}
			.toolbar.variation_pages button {
				margin: 0 0 5px 5px;
			}
			.toolbar.variation_pages button:first-child {
				margin-left: 0;
			}
			#product_attributes .save_attributes {
				display: none;
			}
		</style>
		<?php
	}


	/**
	 *  Function used to remove the default meta boxes by WooCommerce
	 */
	function replace_meta_boxes(){
		remove_meta_box( 'woocommerce-product-data' , 'product' , 'normal' );
		add_meta_box( 'woocommerce-product-data', __( 'Product Data', 'wc-paged-variations' ), 'WC_Paged_Meta_Box_Product_Data::output', 'product', 'normal', 'high' );
	}

	/**
	 * Function to determine if the current edit screen is a WC_Product edit screen
	 * @return bool
	 */
	static function is_product_edit_screen(){
		return ( isset( $_GET['action'] ) && $_GET['action'] == "edit" && get_post_type( ( isset( $_GET['post'] ) ) ? $_GET['post'] : 0 ) == "product" );
	}
}

$wc_paged_variations = new WooCommerce_Paged_Product_Variations();