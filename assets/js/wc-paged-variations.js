(function( root, $, undefined ) {
    "use strict";

    $(function () {

        // Default blockUI settings
        window.blockui_settings = {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        };

        setupDefaults();

        // Custom save_attributes button for correct callback
        $('.save_attributes').after('<button type="button" class="button update_attributes">Save attributes</button>');
        $('.update_attributes').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('.product_attributes').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });

            var data = {
                post_id: 		woocommerce_admin_meta_boxes.post_id,
                data:			$('.product_attributes').find('input, select, textarea').serialize(),
                action: 		'woocommerce_save_attributes',
                security: 		woocommerce_admin_meta_boxes.save_attributes_nonce
            };

            $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

                setupDefaults();
                $('.product_attributes').unblock();

            });
        });
    });

    /**
     * Setup (or Re-Setup) the default parameters for the pages
     */
    function setupDefaults(){
        // Generate paged toolbars
        add_toolbars();

        // Set the page number global
        woocommerce_admin_meta_boxes_variations.page_number = 1;

        // Add the default page
        add_variation( 1 );
    }

    /**
     * Used to return the variations for page_number
     * @param page_number
     */
    function add_variation( page_number ){
        var variations_wrapper = $(".woocommerce_variations");

        variations_wrapper.block(window.blockui_settings);

        woocommerce_admin_meta_boxes_variations.page_number = page_number;


        var data = {
            action: 'woocommerce_paged_get_variations',
            post_id: woocommerce_admin_meta_boxes_variations.post_id,
            security: woocommerce_admin_meta_boxes_variations.add_variation_nonce,
            page_number: page_number
        };

        $.post( woocommerce_admin_meta_boxes_variations.ajax_url, data, function ( response ) {

            variations_wrapper.empty();

            variations_wrapper.append( response );

            $('#variable_product_options .close_all').click();

            variations_wrapper.unblock();

            $( '.tips' ).tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50
            });
        });
    }

    /**
     * Function to add the default toolbars for the paged variation plugins
     */
    function add_toolbars(){
        var variations_wrapper = $(".woocommerce_variations");

        if($( '.toolbar.variation_pages' ).length == 0){
            variations_wrapper.after( '<p class="toolbar variation_pages"></p>' );
        }

        var paged_toolbar = $( '.toolbar.variation_pages' );

        paged_toolbar.block(window.blockui_settings);

        // Get number of pages and populate toolbar
        var data = {
            action: 'woocommerce_paged_get_pages',
            post_id: woocommerce_admin_meta_boxes_variations.post_id,
            security: woocommerce_admin_meta_boxes_variations.add_variation_nonce
        };

        $.post( woocommerce_admin_meta_boxes_variations.ajax_url, data, function ( response ) {
            paged_toolbar.empty();

            // console.log(response);

            for(var x = 0; x < parseInt(response); x++){
                var primary_class = "";
                if(x == 0) primary_class = "button-primary";
                paged_toolbar.append( '<button type="button" class="button ' + primary_class + ' page_number" data-page_number="'+(x+1)+'">' + (x+1) + '</button>' );
            }

            paged_toolbar.find('.page_number').on( 'click' , function(){
                paged_toolbar.find('.page_number').removeClass('button-primary');
                add_variation( $(this).data('page_number') );
                $(this).addClass('button-primary');
            } );

            paged_toolbar.unblock();

        });

    }

} ( this, jQuery ));