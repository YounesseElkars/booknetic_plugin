<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

foreach ($parameters['appearances'] AS $appearance )
{
	$cssFile = \BookneticApp\Backend\Appearance\Helpers\Theme::getThemeCss( $appearance['id'] );
	echo '<link rel="stylesheet" href="' . str_replace(['http://', 'https://'], '//', $cssFile) . '?_r='.rand(0,10000).'" type="text/css">' . "\n";
}
?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/index.css', 'Appearance')?>" type="text/css">
<link rel="stylesheet" href="<?php echo Helper::assets('css/booknetic.light.css', 'Appearance')?>" type="text/css">
<script type="application/javascript" src="<?php echo Helper::assets('js/index.js', 'Appearance')?>"></script>

<div class="m_header clearfix">
	<div class="m_head_title float-left"><?php echo bkntc__('Appearance')?> <span class="badge badge-warning row_count"><?php echo count($parameters['appearances'])?></span></div>
	<div class="m_head_actions float-right">
		
	</div>
</div>

<div class="appearance_area">
	<div class="row">

		<?php
		foreach ( $parameters['appearances'] AS $appearance )
		{
			?>
			<div class="col-sm-6 col-md-4">
				<div class="appearance_box<?php echo $appearance['is_default']?' appearance_box_active':''?>" data-id="<?php echo (int)$appearance['id']?>">
					<a href="?page=<?php echo Helper::getSlugName() ?>&module=appearance&action=edit&id=<?php echo $appearance['id']?>" class="appearance_box_preview">

						<div class="booknetic_appointment" id="booknetic_theme_<?php echo $appearance['id']?>">
							<div class="booknetic_appointment_steps">
								<div class="booknetic_appointment_steps_body nice-scrollbar-primary">
									<div class="booknetic_appointment_step_element booknetic_selected_step"><span class="booknetic_badge">1</span> <span class="booknetic_step_title"> <?php echo bkntc__('Location')?></span></div>
									<div class="booknetic_appointment_step_element booknetic_selected_step"><span class="booknetic_badge">2</span> <span class="booknetic_step_title"> <?php echo bkntc__('Staff')?></span></div>
									<div class="booknetic_appointment_step_element booknetic_active_step"><span class="booknetic_badge">3</span> <span class="booknetic_step_title"> <?php echo bkntc__('Service')?></span></div>
									<div class="booknetic_appointment_step_element"><span class="booknetic_badge">4</span> <span class="booknetic_step_title"> <?php echo bkntc__('Service Extras')?></span></div>
									<div class="booknetic_appointment_step_element"><span class="booknetic_badge">5</span> <span class="booknetic_step_title"> <?php echo bkntc__('Date & Time')?></span></div>
									<div class="booknetic_appointment_step_element"><span class="booknetic_badge">6</span> <span class="booknetic_step_title"> <?php echo bkntc__('Information')?></span></div>
									<div class="booknetic_appointment_step_element"><span class="booknetic_badge">7</span> <span class="booknetic_step_title"> <?php echo bkntc__('Confirmation')?></span></div>
								</div>
								<div class="booknetic_appointment_steps_footer">
									<div class="booknetic_appointment_steps_footer_txt1"><?php echo Helper::getOption('company_phone', '') == '' ? '' : bkntc__('Have any questions?')?></div>
									<div class="booknetic_appointment_steps_footer_txt2"><?php echo Helper::getOption('company_phone', '')?></div>
								</div>
							</div>
							<div class="booknetic_appointment_container">

								<div class="booknetic_appointment_container_header"><?php echo bkntc__('Select service')?></div>
								<div class="booknetic_appointment_container_body">

									<div data-step-id="service">

										<div class="booknetic_service_category"><?php echo bkntc__('Category 1')?></div>

										<div class="booknetic_service_card">
											<div class="booknetic_service_card_image">
												<img src="<?php echo Helper::profileImage('', 'Services')?>">
											</div>
											<div class="booknetic_service_card_title">
												<span><?php echo bkntc__('Service 1')?></span>
												<span>1h</span>
											</div>
											<div class="booknetic_service_card_description"><?php echo bkntc__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')?></div>
											<div class="booknetic_service_card_price">$150.0</div>
										</div>


										<div class="booknetic_service_card booknetic_service_card_selected">
											<div class="booknetic_service_card_image">
												<img src="<?php echo Helper::profileImage('', 'Services')?>">
											</div>
											<div class="booknetic_service_card_title">
												<span><?php echo bkntc__('Service 2')?></span>
												<span>1h</span>
											</div>
											<div class="booknetic_service_card_description"><?php echo bkntc__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')?></div>
											<div class="booknetic_service_card_price">$50.0</div>
										</div>

										<div class="booknetic_service_category"><?php echo bkntc__('Category 2')?></div>
										<div class="booknetic_service_card">
											<div class="booknetic_service_card_image">
												<img src="<?php echo Helper::profileImage('', 'Services')?>">
											</div>
											<div class="booknetic_service_card_title">
												<span><?php echo bkntc__('Service 3')?></span>
												<span>1h</span>
											</div>
											<div class="booknetic_service_card_description"><?php echo bkntc__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...')?></div>
											<div class="booknetic_service_card_price">$40.0</div>
										</div>

									</div>

								</div>
								<div class="booknetic_appointment_container_footer">
									<button type="button" class="booknetic_btn_secondary booknetic_prev_step"><?php echo bkntc__('BACK')?></button>
									<button type="button" class="booknetic_btn_primary booknetic_next_step"><span><?php echo bkntc__('NEXT STEP')?></span></button>
								</div>
							</div>
                            <div class="appearance_box_edit">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                      <path d="M2.01677 10.5946C1.90328 10.4149 1.84654 10.3251 1.81477 10.1865C1.79091 10.0824 1.79091 9.91824 1.81477 9.81415C1.84654 9.67556 1.90328 9.58571 2.01677 9.40601C2.95461 7.92103 5.74617 4.16699 10.0003 4.16699C14.2545 4.16699 17.0461 7.92103 17.9839 9.40601C18.0974 9.58571 18.1541 9.67556 18.1859 9.81415C18.2098 9.91824 18.2098 10.0824 18.1859 10.1865C18.1541 10.3251 18.0974 10.4149 17.9839 10.5946C17.0461 12.0796 14.2545 15.8337 10.0003 15.8337C5.74617 15.8337 2.95461 12.0796 2.01677 10.5946Z" stroke="white" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                                      <path d="M10.0003 12.5003C11.381 12.5003 12.5003 11.381 12.5003 10.0003C12.5003 8.61961 11.381 7.50033 10.0003 7.50033C8.61962 7.50033 7.50034 8.61961 7.50034 10.0003C7.50034 11.381 8.61962 12.5003 10.0003 12.5003Z" stroke="white" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
						</div>

					</a>
					<div class="appearance_box_footer">
						<a href="?page=<?php echo Helper::getSlugName() ?>&module=appearance&action=edit&id=<?php echo $appearance['id']?>" class="appearance_box_name"><?php echo htmlspecialchars($appearance['name']) ?></a>
						<?php
						if( $appearance['is_default'] )
						{
						?>
						<button class="btn btn-primary appearance_box_choose_btn" data-label-true="<?php echo bkntc__('SELECTED')?>" data-label-false="<?php echo bkntc__('SELECT')?>"><?php echo bkntc__('SELECTED')?></button>
						<?php
						}
						else
						{
						?>
						<button class="btn btn-outline-secondary appearance_box_choose_btn" data-label-true="<?php echo bkntc__('SELECTED')?>" data-label-false="<?php echo bkntc__('SELECT')?>"><?php echo bkntc__('SELECT')?></button>
						<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php
		}
		?>

		<div class="col-md-4">
			<a href="?page=<?php echo Helper::getSlugName() ?>&module=appearance&action=edit&id=0" class="appearance_add_new">
				<div class="dashed-border appearance_add_new_contetn">
					<img src="<?php echo Helper::icon('add-employee.svg')?>">
					<div><?php echo bkntc__('Create new style')?></div>
				</div>
			</a>
		</div>

	</div>
</div>
