(function ($)
{
	"use strict";

	$(document).ready(function()
	{

		booknetic.initMultilangInput( $( "#input_location_name" ), 'locations', 'name' );
		booknetic.initMultilangInput( $( "#input_address" ), 'locations', 'address' );
		booknetic.initMultilangInput( $( "#input_note" ), 'locations', 'notes' );

		$('.fs-modal').on('click', '#addLocationSave', function ()
		{
			var location_name	= $("#input_location_name").val(),
				phone			= $("#input_phone").val(),
				address			= $("#input_address").val(),
				note			= $("#input_note").val(),
				image			= $("#input_image")[0].files[0];

			if( location_name === '' )
			{
				booknetic.toast(booknetic.__('fill_all_required'), 'unsuccess');
				return;
			}

			var data = new FormData();

			data.append('id', $("#add_new_JS").data('location-id'));
			data.append('location_name', location_name);
			data.append('address', address);
			data.append('phone', phone);
			data.append('note', note);
			data.append('image', image);
			data.append('latitude', marker.getPosition() ? marker.getPosition().lat() : '');
			data.append('longitude', marker.getPosition() ? marker.getPosition().lng() : '');
			data.append('translations', booknetic.getTranslationData( $( '.fs-modal' ).first() ));

			booknetic.ajax( 'locations.save_location', data, function()
			{
				booknetic.modalHide($(".fs-modal"));
				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		}).on('click', '#hideLocationBtn', function ()
		{
			booknetic.ajax('hide_location', { location_id: $(".fs-modal #add_new_JS").data('location-id') }, function ()
			{
				booknetic.modalHide($(".fs-modal"));

				booknetic.dataTable.reload( $("#fs_data_table_div") );
			});
		});

		var position = new google.maps.LatLng( $('#add_new_JS').data('latitude') , $('#add_new_JS').data('longitude') );

		var map = new google.maps.Map(document.getElementById('divmap'), {
			center: position,
			zoom: $('#add_new_JS').data('zoom'),
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			streetViewControl: false,
		});

		var marker = new google.maps.Marker({
			position: position,
			map: map,
			title: $('#input_location_name').val()
		});

		google.maps.event.addListener(map, "click", function (event)
		{
			var latitude	= event.latLng.lat();
			var longitude	= event.latLng.lng();

			var position	= new google.maps.LatLng(latitude,longitude);

			// Center of map
			map.panTo(position);

			// update marker
			marker.setPosition(position);
		});

	});

})(jQuery);
