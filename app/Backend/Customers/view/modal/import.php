<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

?>

<link rel="stylesheet" href="<?php echo Helper::assets('css/import.css', 'Customers')?>">
<script type="text/javascript" src="<?php echo Helper::assets('js/import.js', 'Customers')?>"></script>

<div id="customer_import_modal">
	<div class="modal-header">
		<h5 class="modal-title"><?php echo bkntc__('Import')?></h5>
		<span data-dismiss="modal" class="p-1 cursor-pointer"><i class="fa fa-times"></i></span>
	</div>
	<div class="modal-body">

		<div class="row">
			<div class="col-md-3">
				<div class="customer_export_img">
					<img src="<?php echo Helper::assets('icons/export.svg')?>">
				</div>
			</div>
			<div class="col-md-9">
				<form id="customer_import_form">

					<div class="customer_export_title"><?php echo bkntc__('You may import list of clients in CSV format.')?></div>

					<div class="form-group">
						<label for="input_image"><?php echo bkntc__('Select file')?></label>
						<input type="file" class="form-control" id="input_csv">
						<div class="form-control" data-label="<?php echo bkntc__('BROWSE')?>"><?php echo bkntc__('(CSV, max 10k customers)')?></div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-12">
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck1" name="fields[]" value="first_name">
								<label class="pl-2" for="defaultCheck1"><?php echo bkntc__('First Name')?></label>
							</div>
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck2" name="fields[]" value="last_name">
								<label class="pl-2" for="defaultCheck2"><?php echo bkntc__('Last Name')?></label>
							</div>
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck3" name="fields[]" value="email">
								<label class="pl-2" for="defaultCheck3"><?php echo bkntc__('Email')?></label>
							</div>
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck4" name="fields[]" value="phone_number">
								<label class="pl-2" for="defaultCheck4"><?php echo bkntc__('Phone')?></label>
							</div>
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck5" name="fields[]" value="gender">
								<label class="pl-2" for="defaultCheck5"><?php echo bkntc__('Gender')?></label>
							</div>
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck5" name="fields[]" value="birthdate">
								<label class="pl-2" for="defaultCheck5"><?php echo bkntc__('Date of birth')?></label>
							</div>
							<div class="mb-1 d-flex">
								<input checked type="checkbox" id="defaultCheck6" name="fields[]" value="notes">
								<label class="pl-2" for="defaultCheck6"><?php echo bkntc__('Note')?></label>
							</div>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-12">
							<label for="input_gender"><?php echo bkntc__('Delimiter')?></label>
							<select id="input_delimiter" class="form-control">
								<option value=","><?php echo bkntc__('Comma ( , )')?></option>
								<option value=";"><?php echo bkntc__('Semicolon ( ; )')?></option>
							</select>
						</div>
					</div>

				</form>
			</div>
		</div>

	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-lg btn-outline-secondary" data-dismiss="modal"><?php echo bkntc__('CANCEL')?></button>
		<button type="button" class="btn btn-lg btn-primary" id="addCustomerSave"><?php echo bkntc__('IMPORT')?></button>
	</div>
</div>