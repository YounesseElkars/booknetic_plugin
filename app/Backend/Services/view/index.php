<?php

defined( 'ABSPATH' ) or die();

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Date;

/**
 * @var mixed $parameters
 */

function printTree( $parentId, &$categories, &$staff, $canDoDynamicTranslations, $class = 'vertical' )
{
	if( !isset( $categories[ $parentId ] ) )
		return '';

	$return = '<ul data-type="' . $class . '">';
	foreach ( $categories[ $parentId ] AS $categId => $categInf )
	{
		$return .= '
			<li data-id="' . (int)$categId . '" data-type="' . $categInf['type'] . '">
				<div class="node_details' . ( $categInf['type'] == 'service' ? ' dashed-border' : '' ) . ( $categInf['type'] == 'service' && $categInf['is_active'] == 0 ? ' node_hidden' : '' ) . '">
					<span class="node_name" title="' . (htmlspecialchars( $categInf['name'] ) . ' [ID: ' . $categId .']') . '">' . htmlspecialchars( $categInf['name'] ) . '</span>
					'.($categInf['type']!='category' ? '' : '<span class="add_new_node"></span>').'
					<span class="edit_node"></span>'
                    . ( $categInf[ 'type' ] === 'category' && $canDoDynamicTranslations ? '<span class="node_translations"><i class="fas fa-globe"></i></span>' : '' )  . '
				    
					<span class="remove_node"></span>';

		if( ($class === 'vertical' && !isset( $categories[ $categId ] )) || $categInf['class'] === 'horizontal' )
		{
			$return .= '<div><a href="#" class="add_new_service_btn"></a></div>';
		}

		if( $categInf['type'] == 'service' )
		{
			$return .= '<div class="staff_list">';

			$ii = 0;
			if( isset( $staff[$categId] ) )
			{
				foreach( $staff[$categId] AS $stafInf )
				{
				 	$ii++;
				 	$return .= '<img src="' . Helper::profileImage($stafInf['profile_image'], 'Staff') . '" class="circle_image">';

					if( $ii > 2 )
						break;
				}

				if( count( $staff[$categId] ) - $ii > 0 )
				{
					$return .= '<span>+' . ( count( $staff[$categId] ) - $ii ) . '</span>';
				}
			}

			if( $ii == 0 )
			{
				$return .= '<span class="staff_not_selected">' . bkntc__('Staff not selected') . '</span>';
			}

			$return .= '</div>';
		}

		$return .= '</div>';

		if( $categInf['type'] == 'category' && isset( $categories[ $categId ] ) )
		{
			$return .= printTree( $categId, $categories, $staff, $canDoDynamicTranslations, $categInf['class'] );
		}

		$return .= '</li>';
	}

	$return .= '</ul>';

	return $return;
}
?>

<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/services.css', 'Services')?>" />
<link rel="stylesheet" type="text/css" href="<?php echo Helper::assets('css/bootstrap-colorpicker.min.css', 'Services')?>" />
<script type="application/javascript" src="<?php echo Helper::assets('js/bootstrap-colorpicker.min.js', 'Services')?>"></script>
<script type="application/javascript" src="<?php echo Helper::assets('js/services.js', 'Services')?>"></script>

<div class="m_header clearfix">
	<div class="m_head_title float-left"><?php echo bkntc__('Services')?> <span class="badge badge-warning row_count" id="services_count_badge"><?php echo $parameters['number_of_services']?></span></div>
	<div class="m_head_actions float-right">
        <a href="?page=<?php echo Helper::getSlugName() ?>&module=services&action=edit_order" class="btn btn-lg btn-primary float-left" ><i class="fa fa-arrows-alt mr-2" aria-hidden="true"></i><?php echo bkntc__( 'EDIT ORDER' ); ?></a>
        <a href="?page=<?php echo Helper::getSlugName() ?>&module=services&view=list" class="btn btn-lg btn-primary float-left" ><?php echo bkntc__( 'LIST VIEW' ); ?></a>
		<button type="button" class="btn btn-lg btn-light goto-center tooltip-it" data-placement="bottom" data-title="<?php echo bkntc__('Go to center')?>"><i class="fa fa-location-arrow"></i></button>

		<div class="d-inline">
			<button type="button" class="btn btn-lg btn-light" id="zoom-out"><i class="fa fa-minus"></i></button>
			<button type="button" class="btn btn-lg btn-light" id="zoom-dropdown" data-toggle="dropdown">100%</button>
			<button type="button" class="btn btn-lg btn-light" id="zoom-in"><i class="fa fa-plus"></i></button>

			<div class="dropdown-menu zoom-select" aria-labelledby="zoom-dropdown" >
				<a class="dropdown-item" href="javascript: void(0);">25%</a>
				<a class="dropdown-item" href="javascript: void(0);">30%</a>
				<a class="dropdown-item" href="javascript: void(0);">40%</a>
				<a class="dropdown-item" href="javascript: void(0);">50%</a>
				<a class="dropdown-item" href="javascript: void(0);">60%</a>
				<a class="dropdown-item" href="javascript: void(0);">70%</a>
				<a class="dropdown-item" href="javascript: void(0);">80%</a>
				<a class="dropdown-item" href="javascript: void(0);">90%</a>
				<a class="dropdown-item selected-option" href="javascript: void(0);">100% <i class="fa fa-check"></i> </a>
				<a class="dropdown-item" href="javascript: void(0);">125%</a>
				<a class="dropdown-item" href="javascript: void(0);">150%</a>
			</div>

			<span class="clear"></span>
		</div>

	</div>
</div>

<div class="map_divider"></div>

<div id="services_map">

	<div class="drag-top-arrow"><i class="fa fa-chevron-up"></i></div>
	<div class="drag-bottom-arrow"><i class="fa fa-chevron-down"></i></div>
	<div class="drag-left-arrow"><i class="fa fa-chevron-left"></i></div>
	<div class="drag-right-arrow"><i class="fa fa-chevron-right"></i></div>

	<div id="categories_tree">
		<ul>
			<li data-type="root" data-id="0">
				<div class="node_details dashed-border">
					<span class="node_name"><?php echo bkntc__('Categories')?></span>
					<span class="add_new_node"></span>
				</div>
				<?php echo printTree(0, $parameters['categories'], $parameters['staff'], $parameters['can_do_dynamic_translations'])?>
			</li>
		</ul>
	</div>

</div>

<div id="select_add_type">

	<div class="add_type_title"><?php echo bkntc__('What do you want to create?')?></div>

	<div class="add_type_options">
		<button type="button" class="btn btn-lg btn-default" data-type="category"><?php echo bkntc__('CATEGORY')?></button>
		<button type="button" class="btn btn-lg btn-default" data-type="service"><?php echo bkntc__('SERVICE')?></button>
	</div>

</div>
