(function ( element, blocks, editor, components )
{
	var el					= element.createElement,
		Fragment			= element.Fragment,
		registerBlockType	= blocks.registerBlockType,
		RichText			= editor.RichText,
		InspectorControls	= editor.InspectorControls,
		CheckboxControl		= components.CheckboxControl,
		RadioControl		= components.RadioControl,
		TextControl			= components.TextControl,
		TextareaControl			= components.TextareaControl,
		ToggleControl		= components.ToggleControl,
		SelectControl		= components.SelectControl;

	var appearances = [ {value: -1, label: 'Default'}, {value: -1, label: '-----'} ];
	appearances = appearances.concat(BookneticData.appearances);

	var staff = [ {value: 0, label: '-----'}, {value: 'any', label: 'Any staff'} ];
	staff = staff.concat(BookneticData.staff);

	var services = [ {value: 0, label: '-----'} ];
	services = services.concat(BookneticData.services);

	var service_categs = [ {value: 0, label: '-----'} ];
	service_categs = service_categs.concat(BookneticData.service_categs);

	var locations = [ {value: 0, label: '-----'} ];
	locations = locations.concat(BookneticData.locations);

	const show_service_options = [ { value: 0, label: 'false' }, { value: 1, label: 'true' } ];

	var iconEl = el('svg', { width: 14, height: 18 },
		el(
			'g',
			{},
			el( 'path', { fill: '#FB3E6E', d: "M10,4.5 C10,6.98517258 8.1168347,9 5.7963921,9 L0.000671974239,9 L0.000671974239,1.99951386 C-0.0270736763,0.924525916 0.807830888,0.0298036698 1.86650604,0 L5.7963921,0 C6.92240656,0.00400258212 7.99707785,0.478775271 8.76750662,1.31259115 C9.56615869,2.17496304 10.0074388,3.31626599 10,4.5 Z" } ) ,
			el( 'path', { fill: '#6C70DC', d: "M12.4778547,8.65245263 C11.5268442,7.63485938 10.1979969,7.05491011 8.80519796,7.04959388 L1.94478295,7.04959388 C0.84260796,7.01900347 -0.0269497359,6.10225269 0.000699371406,5 L0.000699371406,7.04959388 L0.000699371406,7.04959388 L0.000699371406,15.9506232 C-0.0281989986,17.0533556 0.842062899,17.9708488 1.94478295,18.0002171 L8.80519796,18.0002171 C11.6741804,18.0002171 14,15.548786 14,12.5293953 C14.008613,11.0896923 13.4637038,9.70170097 12.4778547,8.65245263 Z" } )
		)
	);

	registerBlockType( 'booknetic/popup-booking',
	{
		title: 'Booking Panel in Popup',
		icon: iconEl,
		category: 'booknetic',

		attributes:
		{
			caption: {
				type: 'string',
				default: '',
			},
			class: {
				type: 'string',
				default: '',
			},
			style: {
				type: 'string',
				default: '',
			},
			theme: {
				type: 'integer',
				default: -1
			},
			staff: {
				type: 'string',
				default: '0'
			},
			service: {
				type: 'integer',
				default: 0
			},
			service_categ: {
				type: 'integer',
				default: 0
			},
			location: {
				type: 'integer',
				default: 0
			},
			show_service: {
				type: 'integer',
				default: 0
			},
			limited_booking_days: {
				type: 'integer',
				default: 365
			},
			stepsOrder: {
				type: 'string',
				default: '',
			},
			shortCode: {
				type: 'string',
				default: '[booknetic-booking-button]'
			}
		},

		edit: function( props )
		{
			function onChangeClass( name )
			{
				props.setAttributes( { class: name } );
			}
			function onChangeCaption( name )
			{
				props.setAttributes( { caption: name } );
			}
			function onChangeStyle( css )
			{
				props.setAttributes( { style: css } );
			}
			function onChangeTheme( themeId )
			{
				props.setAttributes( { theme: parseInt( themeId ) } );
			}
			function onChangeStaff( staffId )
			{
				if ( staffId !== 'any' && isNaN( parseInt( staffId ) ) ) return;

				props.setAttributes( { staff: staffId } );
			}
			function onChangeService( serviceId )
			{
				if ( serviceId == 0 )
				{
					props.setAttributes( { show_service: 0 } );
				}

				props.setAttributes( { service: parseInt( serviceId ) } );
			}
			function onChangeServiceCateg( categId )
			{
				props.setAttributes( { service_categ: parseInt( categId ) } );
			}
			function onChangeLocation( locationId )
			{
				props.setAttributes( { location: parseInt( locationId ) } );
			}
			function onChangeShowService( val )
			{
				props.setAttributes( { show_service: val ? 1 : 0 } );
			}
			function onChangeLimitedBookingDays( val )
			{
				if ( parseInt( val ) >= 0 )
					props.setAttributes( { limited_booking_days: parseInt( val ) } );
			}

			function shortCode( props )
			{
				var attrs = [];
				if( props.attributes.theme > 0 )
				{
					attrs.push( 'theme=' + props.attributes.theme )
				}
				if( props.attributes.style !=='' && props.attributes.style !==undefined )
				{

					// console.log(props.attributes.style.toString().replaceAll('"' , "'" , props.attributes.style))
					css = props.attributes.style.replaceAll('"' , "'" , props.attributes.style);
					attrs.push( 'style="' + css +'"' )
				}
				if( props.attributes.caption !=='' )
				{
					let caption = props.attributes.caption
					caption = caption.replaceAll('"' , "'" , caption);
					attrs.push( 'caption="' + caption +'"' )
				}
				if( props.attributes.class !=='' )
				{
					let clazz = props.attributes.class
					clazz = clazz.replaceAll('"' , "'" , clazz);
					attrs.push( 'class="' + clazz +'"' )
				}
				if( props.attributes.staff === 'any' || parseInt( props.attributes.staff ) > 0 )
				{
					attrs.push( 'staff=' + props.attributes.staff )
				}
				if( props.attributes.service > 0 )
				{
					attrs.push( 'service=' + props.attributes.service )
				}
				if( props.attributes.service_categ > 0 )
				{
					attrs.push( 'category=' + props.attributes.service_categ )
				}
				if( props.attributes.location > 0 )
				{
					attrs.push( 'location=' + props.attributes.location )
				}
				if( props.attributes.show_service !== 0 )
				{
					attrs.push( 'show_service=' + props.attributes.show_service )
				}
				if( parseInt( props.attributes.limited_booking_days ) >= 0 && parseInt( props.attributes.limited_booking_days ) !== 365 )
				{
					attrs.push( 'limited_booking_days=' + props.attributes.limited_booking_days )
				}
				if (props.attributes.stepsOrder)
				{
					attrs.push( 'steps_order=' + props.attributes.stepsOrder )
				}

				var shortCode = '[booknetic-booking-button' + (attrs.length ? ' ' : '') + attrs.join(' ') + ']';

				props.setAttributes( { shortCode: shortCode } );

				return shortCode;
			}

			/* steps */
			const stepNames = {
				location: 'Locations',
				staff: 'Staffs',
				service: 'Services',
				service_extras: 'Service extras',
				date_time: 'Date & Time',
				information: 'Information',
			}
			const order = props.attributes.stepsOrder || 'location,staff,service,service_extras,date_time,information';
			let steps = order.split(',');

			function onStepOrderChange () {
				props.setAttributes( { stepsOrder: steps.join(',') } );
			}

			(function () {
				function waitForElm (selector) {
					return new Promise(resolve => {
						const elm = document.querySelector(selector)

						if (elm) {
							return resolve(elm);
						}

						const observer = new MutationObserver(mutations => {
							const elm = document.querySelector(selector)

							if (elm) {
								resolve(elm);
								observer.disconnect();
							}
						});

						observer.observe(document.body, {
							childList: true,
							subtree: true
						});
					});
				}

				const up = function (step) {
					const index = steps.indexOf(step);

					if (index === 0) return;

					steps.splice(index, 1);
					steps.splice(index - 1, 0, step);

					renderHTML();

					onStepOrderChange( steps );
				}

				const checkIsDisabled = function (step, btn) {
					if ( step === 'date_time' && btn === 'up' ) {
						return steps.indexOf('service') + 1 === steps.indexOf('date_time');
					} else if ( step === 'service' && btn === 'down' ) {
						const serviceIndex = steps.indexOf('service');

						return serviceIndex + 1 === steps.indexOf('service_extras') || serviceIndex + 1 === steps.indexOf('date_time')
					} else if ( step === 'service_extras' && btn === 'up' ) {
						return steps.indexOf('service') + 1 === steps.indexOf('service_extras');
					}

					return false;
				}

				const down = function (step) {
					const index = steps.indexOf(step);

					if (index === steps.length - 1) return;

					steps.splice(index, 1);
					steps.splice(index + 1, 0, step);

					renderHTML();

					onStepOrderChange( steps );
				}

				const renderHTML = function () {
					const stepsElm = document.getElementById('bkntc_steps');

					stepsElm.innerText = '';

					steps.forEach(function (el) {
						const bkntcStep = document.createElement('div');
						bkntcStep.className = 'bkntc-step';

						const stepName = document.createElement('div');
						stepName.innerText = stepNames[el]

						const stepButtons = document.createElement('div');
						stepButtons.className = 'bkntc-step-btns';

						const isDisabledUp = checkIsDisabled(el, 'up')

						const stepButtonUp = document.createElement('div');
						stepButtonUp.className = 'bkntc-step-btn';

						if (isDisabledUp) {
							stepButtonUp.className += ' bkntc-step-btn-disabled';
							stepButtonUp.title = 'This step can\'t be before Services step.';
						} else {
							stepButtonUp.onclick = function () { up(el) };
						}

						stepButtonUp.innerHTML = '🔼';

						// -------------------------- //

						const isDisabledDown = checkIsDisabled(el, 'down');

						const stepButtonDown = document.createElement('div');
						stepButtonDown.className = 'bkntc-step-btn';

						if (isDisabledDown) {
							stepButtonDown.className += ' bkntc-step-btn-disabled';
							stepButtonDown.title = 'Services step should come before Service extras and Date & time steps.';
						} else {
							stepButtonDown.onclick = function () { down(el) };
						}

						stepButtonDown.innerHTML = '🔽';

						stepButtons.append(stepButtonUp)
						stepButtons.append(stepButtonDown)

						bkntcStep.append(stepName)
						bkntcStep.append(stepButtons)

						stepsElm.append(bkntcStep)
					});

					return true;
				}

				/** wait until #bkntc_steps element becomes available & call renderHTML */

				// if ( document.getElementById('bkntc_steps') === null ) {
				waitForElm('#bkntc_steps').then(renderHTML)
				// }
			})();


			return (
				el(
					Fragment,
					null,
					el(
						InspectorControls,
						null,
						el(
							TextControl,
							{
								label: 'Caption',
								value: props.attributes.caption,
								onChange: onChangeCaption
							}
						),
						el(
							SelectControl,
							{
								label: 'Appearance',
								value: props.attributes.theme,
								options: appearances,
								onChange: onChangeTheme
							}
						),
						el(
							SelectControl,
							{
								label: 'Service filter',
								value: props.attributes.service,
								options: services,
								onChange: onChangeService
							}
						),
						el(
							SelectControl,
							{
								label: 'Service category filter',
								value: props.attributes.service_categ,
								options: service_categs,
								onChange: onChangeServiceCateg
							}
						),
						el(
							SelectControl,
							{
								label: 'Staff filter',
								value: props.attributes.staff,
								options: staff,
								onChange: onChangeStaff
							}
						),
						el(
							SelectControl,
							{
								label: 'Location filter',
								value: props.attributes.location,
								options: locations,
								onChange: onChangeLocation
							}
						),
						el(
							Fragment,
							null,
							el(
								TextControl,
								{
									label: 'Limited booking days:',
									type: 'number',
									value: props.attributes.limited_booking_days,
									onChange: onChangeLimitedBookingDays
								}
							)
						),
						el(
							ToggleControl,
							{
								label: 'Show service step',
								checked: props.attributes.show_service,
								options: show_service_options,
								onChange: onChangeShowService,
								disabled: props.attributes.service == 0
							}
						),
						el(
							'div',
							null,
							[
								el(
									'style',
									null,
									'.bkntc-steps-title{padding:8px;font-weight:500;font-size:16px;border-bottom:1px solid #e0e0e0;}.bkntc-steps{padding:8px}.bkntc-step{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;user-select:none;font-weight: 500;text-transform: capitalize;}.bkntc-step:first-child .bkntc-step-btn:first-child{opacity:.4}.bkntc-step:last-child{margin-bottom:0}.bkntc-step:last-child .bkntc-step-btn:last-child{opacity:.4}.bkntc-step-btns{display:flex;align-items:center;border-radius:4px}.bkntc-step-btn{cursor:pointer;padding:4px 0;border-radius:4px;transition:.5s}.bkntc-step-btn:first-child{margin-right:4px}.bkntc-step-btn-disabled{cursor:not-allowed;}'
								),
								[
									el(
										'div',
										{
											className: 'bkntc-steps-title',
											onClick: function () {
												renderHm
											}
										},
										'Change steps\' order'
									),
									el(
										'div',
										{
											id: 'bkntc_steps',
											className: 'bkntc-steps',
										}
									),
								]
							]
						),
						el(
							TextControl,
							{
								label: 'Class(es)',
								value: props.attributes.class,
								onChange: onChangeClass
							}
						),
						el(
							TextareaControl,
							{
								label: 'Style',
								value: props.attributes.style,
								onChange: onChangeStyle
							}
						),
					),
					el(
						'div',
						null,
						shortCode( props )
					)
				)
			);
		},

		save: function( props ) {

			return el(
				'div',
				null,
				props.attributes.shortCode
			);
		},
	} );

})(
	wp.element,
	wp.blocks,
	wp.blockEditor,
	wp.components
);

