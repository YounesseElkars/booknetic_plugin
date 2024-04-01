<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

if( count( $parameters['extras'] ) === 0 )
{
    echo '<div class="text-secondary font-size-14 text-center">' . bkntc__('No extras found') . '</div>';
}
else
{
	foreach ( $parameters['extras'] AS $extraInf )
	{
		?>
		<div class="customer-fields-area dashed-border pb-3" data-extra-id="<?php echo (int)$extraInf['id']?>">

				<div class="row mb-2"">
					<div class="col-md-4">
						<div class="form-control-plaintext"><?php echo htmlspecialchars($extraInf['name'])?></div>
					</div>
					<div class="col-md-3">
						<input type="number" min="<?php echo (int)$extraInf['min_quantity']?>" max="<?php echo (int)$extraInf['max_quantity']?>" class="form-control extra_quantity" value="<?php echo $extraInf['quantity'] ?>">
					</div>
					<div class="col-md-5">
						<div class="form-control-plaintext help-text text-secondary">
                            ( <?php echo bkntc__('min quantity')?>: <?php echo (int)$extraInf['min_quantity']?> ,
                             <?php echo bkntc__('max quantity')?>: <?php echo (int)$extraInf['max_quantity']?> )
                        </div>
					</div>
				</div>

		</div>

		<?php
	}
}
