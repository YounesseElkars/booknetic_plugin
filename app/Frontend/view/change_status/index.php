<div class="booknetic_change_status_block <?php echo $parameters['isSaaS'] ? 'booknetic_change_status_tenant_block' : '' ?>">
    <?php if( $parameters['isSaaS'] && $parameters['uploadLogoCapability'] && $parameters['displayLogo'] ):?>
            <img style="max-width: 100px; max-height: 100px; margin-bottom: 15px;" src="<?php echo $parameters['companyImage']?>">
    <?php endif;?>
    <label class="label"
           data-success-message="<?php echo isset($parameters['successlabel']) ? htmlspecialchars($parameters['successlabel']) : 'Your Appointment status changed successfully!'; ?>"
           id="label"><?php echo isset($parameters['label']) ? htmlspecialchars($parameters['label']) : 'Do you want to change your appointment status to {status}' ;?>
    </label>
    <div class="change_status_container">
        <div class="block__cell">
            <a class="btn btn--change" id="btnChangeStatus">
                <span class="btn__icon"></span>
                <span class="btn__text" data-wait="Changing" data-after="<?php echo isset ($parameters['successbutton']) ? htmlspecialchars($parameters['successbutton']) : 'Changed' ;?>"><?php echo isset($parameters['button']) ? htmlspecialchars($parameters['button']) : 'Change' ;?></span>
            </a>
        </div>
    </div>
</div>