<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

?>

<script type="application/javascript" src="<?php echo Helper::assets('js/vanilla-picker.min.js', 'Appearance')?>"></script>
<script src="<?php echo Helper::assets('js/edit.js', 'Appearance')?>" id="appearance-script" data-id="<?php echo (int)$parameters['id']?>"></script>

<link rel="stylesheet" href="<?php echo Helper::assets('css/edit.css', 'Appearance')?>" type="text/css">
<link rel="stylesheet" href="<?php echo Helper::assets('css/booknetic.light.css', 'Appearance')?>" type="text/css">
<link rel="stylesheet" href="<?php echo $parameters['css_file']?>" type="text/css">

<div class="m_header clearfix">
	<div class="m_head_title float-left"><?php echo bkntc__('Appearance')?></div>
	<div class="m_head_actions float-right">
		<button type="button" class="btn btn-lg btn-outline-secondary float-right ml-1" id="go_back_btn"><img src="<?php echo Helper::icon('back.svg')?>" class="mr-2"> <?php echo bkntc__('GO BACK')?></button>
	</div>
</div>

<div class="fs_separator"></div>

<div class="row m-4">

	<div class="col-md-6 col-lg-3 col-xl-4 p-3 pr-md-1">
		<div class="fs_portlet fs_portlet_with_footer">
			<div class="fs_portlet_title"><?php echo bkntc__('Options')?></div>
			<div class="fs_portlet_content">

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Style name')?></span>
					<span class="f_option_val">
						<div class="inner-addon right-addon">
							<input value="<?php echo htmlspecialchars($parameters['info']['name'])?>" id="input_name" class="form-control" placeholder="<?php echo bkntc__('Type name...')?>" maxlength="100">
							<i class="far fa-edit"></i>
						</div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Panel height')?></span>
					<span class="f_option_val">
						<input value="<?php echo (int)$parameters['info']['height']?>" id="input_height" class="form-control text-right" maxlength="100">
						<span class="height_px_spn">px</span>
					</span>
				</div>

				<div class="f_option_element font_family_element">
					<span class="f_option_name"><?php echo bkntc__('Font family')?></span>
					<span class="f_option_val">
						<input value="<?php echo htmlspecialchars($parameters['info']['fontfamily'])?>" id="input_fontfamily" class="form-control text-right">
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Panel background')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="panel" data-color="<?php echo $parameters['colors']['panel']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Primary BG / text color')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="primary" data-color="<?php echo $parameters['colors']['primary']?>"><i class="fa fa-caret-down"></i></div>
						<div class="colorpicker01" data-for="primary_txt" data-color="<?php echo $parameters['colors']['primary_txt']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Completed steps BG / label color')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="compleated_steps" data-color="<?php echo $parameters['colors']['compleated_steps']?>"><i class="fa fa-caret-down"></i></div>
						<div class="colorpicker01" data-for="compleated_steps_txt" data-color="<?php echo $parameters['colors']['compleated_steps_txt']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Active steps BG / label color')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="active_steps" data-color="<?php echo $parameters['colors']['active_steps']?>"><i class="fa fa-caret-down"></i></div>
						<div class="colorpicker01" data-for="active_steps_txt" data-color="<?php echo $parameters['colors']['active_steps_txt']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Other steps BG / label color')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="other_steps" data-color="<?php echo $parameters['colors']['other_steps']?>"><i class="fa fa-caret-down"></i></div>
						<div class="colorpicker01" data-for="other_steps_txt" data-color="<?php echo $parameters['colors']['other_steps_txt']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Title color')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="title" data-color="<?php echo $parameters['colors']['title']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

				<div class="f_option_element">
					<span class="f_option_name"><?php echo bkntc__('Border color')?></span>
					<span class="f_option_val">
						<div class="colorpicker01" data-for="border" data-color="<?php echo $parameters['colors']['border']?>"><i class="fa fa-caret-down"></i></div>
					</span>
				</div>

                <div class="f_option_element">
                    <span class="f_option_name"><?php echo bkntc__('Price color')?></span>
                    <span class="f_option_val">
						<div class="colorpicker01" data-for="price" data-color="<?php echo $parameters['colors']['price']?>"><i class="fa fa-caret-down"></i></div>
					</span>
                </div>

                <div class="f_option_element">
                    <span class="f_option_name"><?php echo bkntc__('Hide steps')?></span>
                    <input id="hide_steps" type="checkbox" <?php if ( $parameters[ 'info' ][ 'hide_steps' ] === '1' ) echo 'checked' ?>/>
                </div>

                <div class="f_option_element f_option_element_custom_css">
                    <span class="f_option_name"><?php echo bkntc__('Custom CSS')?></span>
                </div>
                <div class="f_option_element f_option_element_custom_css_textarea">
                    <div>
                        <textarea name="custom_css" id="custom_css"><?php echo isset( $parameters['info']['custom_css'] ) ? htmlspecialchars($parameters['info']['custom_css']) : ''; ?></textarea>
                    </div>
                </div>

			</div>
			<div class="fs_portlet_footer">
                <div>
                    <div>
                        <?php if( $parameters['id'] > 0 ) : ?>
                            <button type="button" class="btn btn-danger btn-lg" id="delete_btn"><?php echo bkntc__('DELETE')?></button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-success btn-lg" id="save_btn"><?php echo bkntc__('SAVE')?></button>
                    </div>
                </div>
                <div>
                    <div>
                        <?php if( $parameters['id'] > 0 ) : ?>
                            <div class="theme_id_cls"><?php echo bkntc__('Theme ID: %d', [$parameters['id']])?></div>
                        <?php endif; ?>
                    </div>
                </div>
			</div>
		</div>
	</div>

	<div class="col-md-6 col-lg-9 col-xl-8 p-3 pl-md-1">
		<div class="fs_portlet">
			<div class="fs_portlet_title"><?php echo bkntc__('Design')?></div>
			<div class="fs_portlet_content">

				<div id="booknetic_panel_area">
					<div class="booknetic_appointment" id="booknetic_theme_<?php echo $parameters['id']?>">
						<div class="booknetic_appointment_steps" data-change-for="panel" data-type="background-color">
							<div class="booknetic_appointment_steps_body nice-scrollbar-primary">
								<div class="booknetic_appointment_step_element booknetic_selected_step"><span class="booknetic_badge" data-change-for="compleated_steps" data-type="after">1</span> <span class="booknetic_step_title" data-change-for="compleated_steps_txt" data-type="color"> <?php echo bkntc__('Location')?></span></div>
								<div class="booknetic_appointment_step_element booknetic_selected_step"><span class="booknetic_badge" data-change-for="compleated_steps" data-type="after">2</span> <span class="booknetic_step_title" data-change-for="compleated_steps_txt" data-type="color"> <?php echo bkntc__('Staff')?></span></div>
								<div class="booknetic_appointment_step_element booknetic_active_step"><span class="booknetic_badge" data-change-for="active_steps" data-type="background-color">3</span> <span class="booknetic_step_title" data-change-for="active_steps_txt" data-type="color"> <?php echo bkntc__('Service')?></span></div>
								<div class="booknetic_appointment_step_element"><span class="booknetic_badge" data-change-for="other_steps" data-type="background-color">4</span> <span class="booknetic_step_title" data-change-for="other_steps_txt" data-type="color"> <?php echo bkntc__('Service Extras')?></span></div>
								<div class="booknetic_appointment_step_element"><span class="booknetic_badge" data-change-for="other_steps" data-type="background-color">5</span> <span class="booknetic_step_title" data-change-for="other_steps_txt" data-type="color"> <?php echo bkntc__('Date & Time')?></span></div>
								<div class="booknetic_appointment_step_element"><span class="booknetic_badge" data-change-for="other_steps" data-type="background-color">6</span> <span class="booknetic_step_title" data-change-for="other_steps_txt" data-type="color"> <?php echo bkntc__('Information')?></span></div>
								<div class="booknetic_appointment_step_element"><span class="booknetic_badge" data-change-for="other_steps" data-type="background-color">7</span> <span class="booknetic_step_title" data-change-for="other_steps_txt" data-type="color"> <?php echo bkntc__('Confirmation')?></span></div>
							</div>
							<div class="booknetic_appointment_steps_footer">
								<div class="booknetic_appointment_steps_footer_txt1" data-change-for="other_steps_txt" data-type="color"><?php echo Helper::getOption('company_phone', '') == '' ? '' : bkntc__('Have any questions?')?></div>
								<div class="booknetic_appointment_steps_footer_txt2" data-change-for="other_steps" data-type="color"><?php echo Helper::getOption('company_phone', '')?></div>
							</div>
						</div>
						<div class="booknetic_appointment_container">

							<div class="booknetic_appointment_container_header" data-change-for="title" data-type="color"><?php echo bkntc__('Select service')?></div>
							<div class="booknetic_appointment_container_body">

								<div data-step-id="service">

									<div class="booknetic_service_category" data-change-for="primary" data-type="color"><?php echo bkntc__('Category 1')?></div>

									<div class="booknetic_service_card">
										<div class="booknetic_service_card_image">
											<img src="<?php echo Helper::profileImage('', 'Services')?>">
										</div>
										<div class="booknetic_service_card_title">
											<span><?php echo bkntc__('Service 1')?></span>
											<span>1h</span>
										</div>
										<div class="booknetic_service_card_description"><?php echo bkntc__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')?></div>
										<div class="booknetic_service_card_price" data-change-for="price" data-type="color">$150.0</div>
									</div>


									<div class="booknetic_service_card booknetic_service_card_selected" data-change-for="border" data-type="border-color">
										<div class="booknetic_service_card_image">
											<img src="<?php echo Helper::profileImage('', 'Services')?>">
										</div>
										<div class="booknetic_service_card_title">
											<span><?php echo bkntc__('Service 2')?></span>
											<span>1h</span>
										</div>
										<div class="booknetic_service_card_description"><?php echo bkntc__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')?></div>
										<div class="booknetic_service_card_price" data-change-for="price" data-type="color">$50.0</div>
									</div>

									<div class="booknetic_service_category" data-change-for="primary" data-type="color"><?php echo bkntc__('Category 2')?></div>
									<div class="booknetic_service_card">
										<div class="booknetic_service_card_image">
											<img src="<?php echo Helper::profileImage('', 'Services')?>">
										</div>
										<div class="booknetic_service_card_title">
											<span><?php echo bkntc__('Service 3')?></span>
											<span>1h</span>
										</div>
										<div class="booknetic_service_card_description"><?php echo bkntc__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')?></div>
										<div class="booknetic_service_card_price" data-change-for="price" data-type="color">$40.0</div>
									</div>

								</div>

							</div>
							<div class="booknetic_appointment_container_footer">
								<button type="button" class="booknetic_btn_secondary booknetic_prev_step"><?php echo bkntc__('BACK')?></button>
								<button type="button" class="booknetic_btn_primary booknetic_next_step" data-change-for="primary" data-type="background-color"><span data-change-for="primary_txt" data-type="color"><?php echo bkntc__('NEXT STEP')?></span></button>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

</div>
