<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Core\Permission;

/**
 * @var mixed $parameters
 */

?>
<div id="booknetic_settings_area">
	<link rel="stylesheet" href="<?php echo Helper::assets('css/booking_panel_labels_settings.css', 'Settings')?>">
	<script type="application/javascript" src="<?php echo Helper::assets('js/booking_panel_labels_settings.js', 'Settings')?>"></script>
	<link rel="stylesheet" href="<?php echo Helper::assets('css/booknetic.light.css', 'Settings')?>" type="text/css">

	<div class="actions_panel clearfix">
		<button type="button" class="btn btn-lg btn-success settings-save-btn float-right"><i class="fa fa-check pr-2"></i> <?php echo bkntc__('SAVE CHANGES')?></button>
	</div>

	<div class="settings-light-portlet">
		<div class="ms-title">
			<?php echo bkntc__('Front-end panels')?>
			<span class="ms-subtitle"><?php echo bkntc__('Labels')?></span>
		</div>
		<div class="ms-content">

			<div class="select_langugage_section">
				<div>
					<?php
					echo str_replace( ['<select name=', 'value=""'], ['<select class="form-control" name=', 'value="en_US"'], wp_dropdown_languages([
						'id'        => 'language_to_translate',
						'echo'      => false,
                        'languages' => get_available_languages(),
						'selected'  => Helper::isSaaSVersion() ? Helper::getOption('default_language', '') : ''
					]));
					?>
					<button type="button" class="btn btn-default" id="start_transaltion"><?php echo bkntc__('TRANSLATE')?></button>
					<?php if( Helper::isSaaSVersion() ):?>
						<button type="button" class="btn btn-primary" id="set_default_langugage"><?php echo bkntc__('SET AS DEFAULT LANGUAGE')?></button>
					<?php endif;?>
				</div>
			</div>

			<div class="label_settings_container">

				<img id="translate_edit_icon" src="<?php echo Helper::icon('translate-edit.svg', 'Settings');?>" />
				<img id="translate_save_icon" src="<?php echo Helper::icon('translate-save.svg', 'Settings');?>" />
				<img id="translate_cancel_icon" src="<?php echo Helper::icon('translate-cancel.svg', 'Settings');?>" />

				<div id="booknetic_panel_area" class="hidden">
					<div class="booknetic_appointment">
						<div class="booknetic_appointment_steps">
							<div class="booknetic_appointment_steps_body nice-scrollbar-primary">
								<div data-step-id="location" class="booknetic_appointment_step_element<?php echo (\BookneticApp\Providers\Core\Capabilities::tenantCan( 'locations' ) == false ? '_ hidden' : '')?>"><span class="booknetic_badge">1</span> <span class="booknetic_step_title" data-translate="Location"></span></div>
								<div data-step-id="staff" class="booknetic_appointment_step_element<?php echo (\BookneticApp\Providers\Core\Capabilities::tenantCan( 'staff' ) == false ? '_ hidden' : '')?>"><span class="booknetic_badge">2</span> <span class="booknetic_step_title" data-translate="Staff"></span></div>
								<div data-step-id="service" class="booknetic_appointment_step_element<?php echo (\BookneticApp\Providers\Core\Capabilities::tenantCan( 'services' ) == false ? '_ hidden' : '')?>"><span class="booknetic_badge">3</span> <span class="booknetic_step_title" data-translate="Service"></span></div>
								<div data-step-id="service_extras" class="booknetic_appointment_step_element<?php echo (\BookneticApp\Providers\Core\Capabilities::tenantCan( 'services' ) == false ? '_ hidden' : '')?>"><span class="booknetic_badge">4</span> <span class="booknetic_step_title" data-translate="Service Extras"></span></div>
								<div data-step-id="date_time" class="booknetic_appointment_step_element"><span class="booknetic_badge">5</span> <span class="booknetic_step_title" data-translate="Date & Time"></span></div>
								<div data-step-id="information" class="booknetic_appointment_step_element"><span class="booknetic_badge">6</span> <span class="booknetic_step_title" data-translate="Information"></span></div>
                                <div data-step-id="cart" class="booknetic_appointment_step_element"><span class="booknetic_badge">8</span> <span class="booknetic_step_title" data-translate="Cart"></span></div>
                                <div data-step-id="confirm_details" class="booknetic_appointment_step_element"><span class="booknetic_badge">7</span> <span class="booknetic_step_title" data-translate="Confirmation"></span></div>
								<div data-step-id="finish" class="booknetic_appointment_step_element"><span class="booknetic_badge">9</span> <span class="booknetic_step_title" data-translate="Finish"></span></div>
								<div data-step-id="other" class="booknetic_appointment_step_element"><span class="booknetic_badge">10</span> <span class="booknetic_step_title"><?php echo bkntc__('Other')?></span></div>
							</div>
							<div class="booknetic_appointment_steps_footer">
								<div class="booknetic_appointment_steps_footer_txt1"><?php echo Helper::getOption('company_phone', '') == '' ? '' : ('<div class="d-inline-block" data-translate="Have any questions?"></div>')?></div>
								<div class="booknetic_appointment_steps_footer_txt2"><?php echo Helper::getOption('company_phone', '')?></div>
							</div>
						</div>
						<div class="booknetic_appointment_container">

							<div class="booknetic_appointment_container_header hidden" data-step-id="location"><span data-translate="Select location"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="staff"><span data-translate="Select staff"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="service"><span data-translate="Select service"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="service_extras"><span data-translate="Select service extras"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="information"><span data-translate="Fill information"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="date_time"><span data-translate="Select Date & Time"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="cart"><span data-translate="Add to cart"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="confirm_details"><span data-translate="Confirm Details"></span></div>
							<div class="booknetic_appointment_container_header hidden" data-step-id="other"></div>

							<div class="booknetic_appointment_container_body">

								<div class="hidden" data-step-id="location">
									<?php
									foreach ( $parameters['locations'] AS $location )
									{
										?>
										<div class="booknetic_card">
											<div class="booknetic_card_image">
												<img src="<?php echo Helper::profileImage($location->image, 'Locations')?>">
											</div>
											<div class="booknetic_card_title">
												<div><?php echo htmlspecialchars($location->name)?></div>
												<div class="booknetic_card_description<?php echo Helper::getOption('hide_address_of_location', 'off') == 'on' ? ' hidden' : ''?>"><?php echo htmlspecialchars($location->address)?></div>
											</div>
										</div>
										<?php
									}
									?>
								</div>

								<div class="hidden" data-step-id="service">
									<?php
									$lastCategoryPrinted = null;
									foreach ( $parameters['services'] AS $serviceInf )
									{
										if( $lastCategoryPrinted != $serviceInf->category_id )
										{
											echo '<div class="booknetic_service_category">' . htmlspecialchars($serviceInf->category()->fetch()->name) . '</div>';
											$lastCategoryPrinted = $serviceInf->category_id;
										}
										?>
										<div class="booknetic_service_card">
											<div class="booknetic_service_card_image">
												<img src="<?php echo Helper::profileImage($serviceInf->image, 'Services')?>">
											</div>
											<div class="booknetic_service_card_title">
												<span><?php echo htmlspecialchars($serviceInf->name)?></span>
												<span<?php echo $serviceInf->hide_price==1 ? ' class="hidden"' : ''?>><?php echo Helper::secFormat($serviceInf->duration*60)?></span>
											</div>
											<div class="booknetic_service_card_description">
												<?php echo htmlspecialchars(Helper::cutText( $serviceInf->notes, 65 ))?>
											</div>
											<div class="booknetic_service_card_price<?php echo $serviceInf->hide_price==1 ? ' hidden' : ''?>">
												<?php echo Helper::price( $serviceInf->real_price == -1 ? $serviceInf->price : $serviceInf->real_price )?>
											</div>
										</div>
										<?php
									}
									?>
								</div>

								<div class="hidden" data-step-id="staff">
									<?php
									foreach ( $parameters['staff'] AS $staffInf )
									{
										$footer_text_option = Helper::getOption('footer_text_staff', '1');
										?>
										<div class="booknetic_card">
											<div class="booknetic_card_image">
												<img src="<?php echo Helper::profileImage($staffInf->profile_image, 'Staff')?>">
											</div>
											<div class="booknetic_card_title">
												<div><?php echo htmlspecialchars($staffInf->name)?></div>
												<div class="booknetic_card_description">
													<?php
													if( $footer_text_option == '1' || $footer_text_option == '2' )
													{
														?>
														<div><?php echo htmlspecialchars($staffInf->email)?></div>
														<?php
													}
													if( $footer_text_option == '1' || $footer_text_option == '3' )
													{
														?>
														<div><?php echo htmlspecialchars($staffInf->phone_number)?></div>
														<?php
													}
													?>
												</div>
											</div>
										</div>
										<?php
									}
									?>
								</div>

								<div class="hidden" data-step-id="service_extras">
									<?php
									foreach ( $parameters['service_extras'] AS $extraInf )
									{
										?>
										<div class="booknetic_service_extra_card">
											<div class="booknetic_service_extra_card_image">
												<img src="<?php echo Helper::profileImage($extraInf->image, 'Services')?>">
											</div>
											<div class="booknetic_service_extra_card_title">
												<span><?php echo htmlspecialchars($extraInf->name)?></span>
												<span><?php echo $extraInf->duration ? Helper::secFormat($extraInf->duration*60) : ''?></span>
											</div>
											<div class="booknetic_service_extra_card_price">
												<?php echo Helper::price( $extraInf->price )?>
											</div>
											<div class="booknetic_service_extra_quantity">
												<div class="booknetic_service_extra_quantity_dec">-</div>
												<input type="text" class="booknetic_service_extra_quantity_input" value="0" data-max-quantity="<?php echo $extraInf->max_quantity?>">
												<div class="booknetic_service_extra_quantity_inc">+</div>
											</div>
										</div>
										<?php
									}
									?>
								</div>

								<div class="hidden" data-step-id="date_time">
									<div class="booknetic_date_time_area">
										<div class="booknetic_calendar_div">
											<div class="booknetic_calendar_head">
												<div class="booknetic_prev_month"> < </div>
												<div class="booknetic_month_name"></div>
												<div class="booknetic_next_month"> > </div>
											</div>
											<div id="booknetic_calendar_area"></div>
										</div>
										<div class="booknetic_time_div">
											<div class="booknetic_times_head"><span data-translate="Time"></span></div>
											<div class="booknetic_times">
												<div class="booknetic_times_title"><span data-translate="Select date"></span></div>
												<div class="booknetic_times_list">
													<div>
														<div>09:00</div>
														<div>10:00</div>
													</div>
													<div>
														<div>10:00</div>
														<div>11:00</div>
													</div>
													<div>
														<div>11:00</div>
														<div>12:00</div>
													</div>
													<div>
														<div>12:00</div>
														<div>13:00</div>
													</div>
													<div>
														<div>13:00</div>
														<div>14:00</div>
													</div>
													<div>
														<div>14:00</div>
														<div>15:00</div>
													</div>
													<div>
														<div>15:00</div>
														<div>16:00</div>
													</div>
													<div>
														<div>16:00</div>
														<div>17:00</div>
													</div>
													<div>
														<div>17:00</div>
														<div>18:00</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="hidden" data-step-id="information">
									<div class="form-row">
										<div class="form-group col-md-6">
											<label><span data-translate="Name"></span></label>
											<input type="text" id="bkntc_input_name" class="form-control" name="name">
										</div>
										<div class="form-group col-md-6">
											<label><span data-translate="Surname"></span></label>
											<input type="text" id="bkntc_input_surname" class="form-control" name="surname">
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-md-6">
											<label><span data-translate="Email"></span></label>
											<input type="text" id="bkntc_input_email" class="form-control" name="email">
										</div>
										<div class="form-group col-md-6">
											<label><span data-translate="Phone"></span></label>
											<input type="text" id="bkntc_input_phone" class="form-control" name="phone">
										</div>
									</div>

                                    <div id="booknetic_bring_someone_section">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <input type="checkbox" id="">
                                                <label for=""><span data-translate="Bring People with You"></span></label>
                                            </div>

                                            <div class="form-group col-md-6 booknetic_number_of_brought_customers ">
                                                <label for=""><span data-translate="Number of people:"></span></label>
                                            </div>
                                        </div>
                                    </div>

								</div>

                                <div class="hidden" data-step-id="cart">
                                    <div class="booknetic-cart-holder">
                                        <div class="booknetic-cart">
                                            <div class="booknetic-cart-col" data-index="0"  >
                                                    <div class="booknetic-cart-item">
                                                        <div class="booknetic-cart-item-header">
                                                            <span><?php echo isset($parameters['services'][0]->name) ? htmlspecialchars($parameters['services'][0]->name) : '-' ?></span>
                                                            <button class="booknetic-cart-item-more">
                                                                <img src="<?php echo Helper::icon('more-vertical.svg','front-end') ?>" alt="">
                                                            </button>
                                                            <div class="booknetic-cart-item-btns ">

                                                                <button class="booknetic-cart-item-edit">
                                                                    <img src="<?php echo Helper::icon('edit-2.svg','front-end') ?>" >
                                                                    <span><div><span data-translate="Edit"></span></div></span>
                                                                </button>
                                                                <button class="booknetic-cart-item-remove">
                                                                    <img src="<?php echo Helper::icon('trash-2.svg','front-end') ?>" alt="">
                                                                    <span><div><span data-translate="Remove"></span></div></span>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="booknetic-cart-item-body">
                                                            <div class="booknetic-cart-item-body-row">
                                                                <span class="booknetic-cart-item-body-cell"><?php echo bkntc__( 'Staff' ); ?>:</span>
                                                                <span class="booknetic-cart-item-body-cell"><?php echo isset($parameters['staff'][0]->name) ? htmlspecialchars($parameters['staff'][0]->name) : '-' ?></span>
                                                            </div>
                                                            <div class="booknetic-cart-item-body-row">
                                                                <span class="booknetic-cart-item-body-cell"><?php echo bkntc__( 'Location' ); ?>:</span>
                                                                <span class="booknetic-cart-item-body-cell"><?php echo isset($parameters['locations'][0]->name) ? htmlspecialchars($parameters['locations'][0]->name) : '-' ?></span>
                                                            </div>
                                                            <div class="booknetic-cart-item-body-row">
                                                                <span class="booknetic-cart-item-body-cell"><?php echo bkntc__( 'Date & Time' ); ?>:</span>
                                                                <span class="booknetic-cart-item-body-cell"><?php echo date('Y-m-d') . ' / ' . date('H:00') . '-' . date('H:00' ,strtotime('+1 hour')) ?></span>
                                                            </div>
                                                            <div class="booknetic-cart-item-body-row">

                                                                <span class="booknetic-cart-item-body-cell"><div><span data-translate="Amount"></span></div>:</span>
                                                                <span class="booknetic-cart-item-body-cell amount">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Helper::price(100); ?></span>
                                                                <span class="booknetic-cart-item-body-cell">
                                                                    <button class="booknetic-cart-item-info">
                                                                        <img src="<?php echo Helper::icon('info.svg' ,'front-end') ?>" alt="">
                                                                        <div class="booknetic-cart-item-info-details-arrow"></div>
                                                                    </button>
                                                                </span>
                                                            </div>
                                                            <div class="booknetic-cart-item-error ">
                                                                <div class="booknetic-cart-item-error-header">
                                                                    <div>
                                                                        <img src="<?php echo Helper::icon('alert-triangle.svg','front-end')?>" alt="">
                                                                        <span><?php echo bkntc__( 'Error' ) ?></span>
                                                                    </div>
                                                                </div>
                                                                <div class="booknetic-cart-item-error-body">
                                                                    Lorem ipsum dolor sit amet lorem ipsum dolor sit amet
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <button class="bkntc_again_booking">
                                            <img src="<?php echo Helper::icon('plus-2.svg' ,'front-end')?>" alt="">
                                            <span><div><span data-translate="Add new Booking"></span></div></span>
                                        </button>
                                    </div>

                                </div>

								<div class="hidden" data-step-id="confirm_details">
									<div class="booknetic_confirm_step_body">

										<div class="booknetic_confirm_sum_body<?php echo $parameters['hide_payments'] ? ' booknetic_confirm_sum_body_full_width' : '';?>">
											<div class="booknetic_portlet">

												<div class="booknetic_confirm_details">
													<div class="booknetic_confirm_details_title"><?php echo isset( $serviceInf ) ? $serviceInf->name : '???' ?></div>
													<div class="booknetic_confirm_details_price"><?php echo Helper::price( 100 )?></div>
												</div>

												<div class="booknetic_confirm_details" data-price-id="discount">
													<div class="booknetic_confirm_details_title"><span data-translate="Discount"></span></div>
													<div class="booknetic_confirm_details_price"><?php echo Helper::price(0)?></div>
												</div>

												<div class="booknetic_confirm_sum_price">
													<div><span data-translate="Total price"></span></div>
													<div class="booknetic_sum_price"><?php echo Helper::price(100)?></div>
												</div>

											</div>
										</div>

										<div class="booknetic_confirm_deposit_body<?php echo $parameters['hide_payments'] ? ' hidden' : '';?>">

											<div class="booknetic_portlet">
												<div class="booknetic_payment_methods">
													<?php
													$order_num = 0;
													foreach ( $parameters['gateways_order'] AS  $payment_method )
													{
														if( !isset( $parameters['payment_gateways'][ $payment_method ] ) )
															continue;
														?>
														<div class="booknetic_payment_method<?php echo !$order_num ? ' booknetic_payment_method_selected' : ''?>">
															<img src="<?php echo Helper::icon($payment_method . '.svg', 'front-end')?>">
															<span data-translate="<?php echo $parameters['payment_gateways'][ $payment_method ]['trnslt']?>"><?php echo $parameters['payment_gateways'][ $payment_method ]['title']?></span>
														</div>
														<?php
														$order_num++;
													}
													?>
												</div>

												<div class="booknetic_hr mt-3"></div>

												<div class="booknetic_deposit_radios">
													<div>
														<input type="radio" id="input_deposit_2" name="input_deposit" value="1" checked><label><span data-translate="Deposit"></span></label>
													</div>
													<div>
														<input type="radio" id="input_deposit_1" name="input_deposit" value="0"><label><span data-translate="Full amount"></span></label>
													</div>
												</div>

												<div class="booknetic_deposit_price">
													<div><span data-translate="Deposit"></span>:</div>
													<div class="booknetic_deposit_amount_txt">20%, <?php echo Helper::price( 20)?></div>
												</div>

											</div>

										</div>

									</div>
								</div>

								<div class="hidden" data-step-id="finish">
									<div class="booknetic_appointment_finished">
										<div class="booknetic_appointment_finished_icon"><img src="<?php echo Helper::icon('status-ok.svg', 'front-end')?>"></div>
										<div class="booknetic_appointment_finished_title" data-translate="Thank you for your request!"></div>
										<div class="booknetic_appointment_finished_subtitle" data-translate="Your confirmation number:"></div>
										<div class="booknetic_appointment_finished_code">0123</div>
										<div class="booknetic_appointment_finished_actions">
											<button type="button" id="booknetic_add_to_google_calendar_btn" class="booknetic_btn_secondary<?php echo Helper::getOption('hide_add_to_google_calendar_btn', 'off') == 'on' ? ' booknetic_hidden' : ''?>"><img src="<?php echo Helper::icon('calendar.svg', 'front-end')?>"> <span data-translate="ADD TO GOOGLE CALENDAR"></span></button>
											<button type="button" id="booknetic_start_new_booking_btn" class="booknetic_btn_secondary<?php echo Helper::getOption('hide_start_new_booking_btn', 'off') == 'on' ? ' booknetic_hidden' : ''?>"><img src="<?php echo Helper::icon('plus.svg', 'front-end')?>"> <span data-translate="START NEW BOOKING"></span></button>
											<button type="button" id="booknetic_finish_btn" class="booknetic_btn_secondary" data-redirect-url="<?php echo htmlspecialchars(Helper::getOption('redirect_url_after_booking'))?>"><img src="<?php echo Helper::icon('check-small.svg', 'front-end')?>"> <span data-translate="FINISH BOOKING"></span></button>
										</div>
									</div>
								</div>

								<div class="hidden" data-step-id="other">

									<?php
									$index = 0;
									foreach( $parameters['other_translates'] AS $translateKey => $translateTxt )
									{
										?>
										<div class="form-group col-md-12">
											<div class="input-group-prepend">
												<div class="input-group-text"><?php echo ++$index?></div>
												<input type="text" class="form-control" data-translate-key="<?php echo htmlspecialchars($translateKey)?>" value="<?php echo htmlspecialchars($translateTxt)?>">
											</div>
										</div>
										<?php
									}
									?>

								</div>

							</div>

							<div class="booknetic_appointment_container_footer">
								<button type="button" class="booknetic_btn_secondary booknetic_prev_step"><span data-translate="BACK"></span></button>
								<button type="button" class="booknetic_btn_primary booknetic_next_step"><span data-translate="NEXT STEP"></span></button>
							</div>
						</div>
					</div>
				</div>

			</div>

		</div>
	</div>
</div>