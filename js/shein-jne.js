jQuery( '#billing_address_2' ).on( 'change', function () {
    jQuery( 'body' ).trigger( 'update_checkout' );
});

jQuery( '#billing_city' ).on( 'change', function () {
    jQuery( 'body' ).trigger( 'update_checkout' );
});

jQuery( '#billing_state' ).on( 'change', function () {
    jQuery( 'body' ).trigger( 'update_checkout' );
});

jQuery( '#billing_shipping_type' ).on( 'change', function () {
    jQuery( 'body' ).trigger( 'update_checkout' );
});

jQuery( '.track-awb' ).on( 'click', function (event) {
    event.preventDefault();

    var url = jQuery(this).attr( 'href' );

    jQuery.get( url, function( data ) {
        jQuery( 'body' ).append( data );
    });
});

var billing_city_data = [];

jQuery( '#billing_city' ).children().each(function () {
    billing_city_data.push(jQuery(this).attr('value'));

    jQuery(this).remove();
});

jQuery( '#billing_state' ).on( 'change', function () {
    jQuery( '#billing_city' ).children().each(function () {
        jQuery(this).remove();
    });

    for (var i = 0; i < billing_city_data.length; i++) {
        if (billing_city_data[i].includes( jQuery(this).val() + '-' )) {
            jQuery( '#billing_city' ).append( '<option value="' + billing_city_data[i] + '">' + billing_city_data[i].split( '-' )[1] + '</option>' );
        }
    }

    jQuery( '#billing_city' ).select2();

    jQuery( '#billing_city' ).trigger( 'change' );
});

var billing_address_2_data = [];

jQuery( '#billing_address_2' ).children().each(function () {
    billing_address_2_data.push( jQuery(this).attr( 'value' ) );

    jQuery(this).remove();
});

jQuery( '#billing_city' ).on( 'change', function () {
    jQuery( '#billing_address_2' ).children().each(function () {
        jQuery(this).remove();
    });

    for (var i = 0; i < billing_address_2_data.length; i++) {
        if (billing_address_2_data[i].includes( jQuery(this).val().split( '-' )[1] + '-' )) {
            jQuery( '#billing_address_2' ).append( '<option value="' + billing_address_2_data[i] + '">' + billing_address_2_data[i].split( '-' )[2] + '</option>' );
        }
    }

    jQuery( '#billing_address_2' ).select2();
});
