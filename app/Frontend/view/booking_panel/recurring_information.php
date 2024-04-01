<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;

/**
 * @var mixed $parameters
 */

$dateFormat = Helper::isSaaSVersion() ? 'Y-m-d' : Helper::getOption( 'date_format', 'Y-m-d' );

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/flatpickr.min.css','front-end') ?>">
<script src="<?php echo Helper::assets('js/flatpickr.js','front-end') ?>"></script>
<div class="form-row">
	<div class="form-group col-md-12">
		<table class="booknetic_table_gray booknetic_dashed_border booknetic_recurring_table">
			<thead>
			<tr>
				<th><?php echo bkntc__('#')?></th>
				<th><?php echo bkntc__('DATE')?></th>
				<th<?php echo $parameters['appointmentObj']->isDateBasedService() ? ' class="booknetic_hidden"' : ''?>><?php echo bkntc__('TIME')?></th>
				<th<?php echo $parameters['appointmentObj']->isDateBasedService() ? ' class="booknetic_hidden"' : ''?>><?php echo bkntc__('EDIT')?></th>
			</tr>
			</thead>
			<tbody id="booknetic_recurring_dates">
			<?php $index = 1;?>
			<?php foreach ( $parameters['appointments'] AS $timeSlot ): ?>
				<tr>
					<td><?php echo $index++?></td>
                    <td data-date="<?php echo $timeSlot->getDate()?>" data-service-type="<?php echo $parameters['appointmentObj']->isDateBasedService() ? 'datebased' : ''?>" >
                        <div class="booknetic_recurring_date_container">
                            <span class="date_text"><?php echo $timeSlot->getDate( true )?></span>
                            <input type="hidden" class="booknetic_recurring_info_edit_date booknetic-hidden">
                            <?php if( $parameters['appointmentObj']->isDateBasedService()):?>
                                <?php if( ! $timeSlot->isBookable() ): ?>
                                    <span class="booknetic_data_has_error" title="<?php echo bkntc__('Please select a valid time! ( %s %s is busy! )', [ $timeSlot->getDate( true ), '' ]) ?>"><img src="<?php echo Helper::icon('warning_red.svg', 'front-end')?>"></span>
                                    <button data-type="1" data-date="<?php echo $timeSlot->getDate( true )?>" data-date-format="<?php echo $dateFormat ?>" type="button" class="booknetic_btn_secondary booknetic_date_edit_btn demos"><?php echo bkntc__('EDIT')?></button>
                                <?php endif;?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php if(! $parameters['appointmentObj']->isDateBasedService()): ?>
					<td>
						<span class="booknetic_time_span"><?php echo $timeSlot->getTime( true )?></span>
						<?php if( ! $timeSlot->isBookable() ): ?>
							<span class="booknetic_data_has_error" title="<?php echo bkntc__('Please select a valid time! ( %s %s is busy! )', [ $timeSlot->getDate( true ), $timeSlot->getTime( true ) ]) ?>"><img src="<?php echo Helper::icon('warning_red.svg', 'front-end')?>"></span>
						<?php endif;?>
                    </td>
                    <td data-time="<?php echo $timeSlot->getTime()?>">
                        <span class="booknetic_time_span"></span>
                        <button type="button" class="booknetic_btn_secondary booknetic_date_edit_btn"><?php echo bkntc__('EDIT')?></button>
                    </td>
                    <?php endif; ?>

				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>

