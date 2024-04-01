<?php

namespace BookneticApp\Backend\Base;

use BookneticApp\Models\ServiceCategory;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Backend\Settings\Helpers\LocalizationService;
use BookneticApp\Models\Location;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Translation;
use BookneticApp\Providers\Core\Controller;
use BookneticApp\Providers\Core\FSCodeAPI;
use BookneticApp\Providers\Core\Templates\Applier;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Session;

class Ajax extends Controller
{
	public function switch_language()
	{
		if( !Helper::isSaaSVersion() )
		{
			return $this->response( false );
		}

		$language = Helper::_post('language', '', 'string');

		if( LocalizationService::isLngCorrect( $language ) )
		{
			Session::set('active_language', $language);
		}

		return $this->response( true );
	}

	public function ping()
	{
		return $this->response( true );
	}

    public function direct_link()
    {
        $service_id     = Helper::_post('service_id' ,0 ,'int');
        $staff_id       = Helper::_post('staff_id' ,0 ,'int');
        $location_id    = Helper::_post('location_id' ,0 ,'int');
        $categories = ServiceCategory::fetchAll();
        $services   = Service::fetchAll();
        $staff      = Staff::fetchAll();
        $locations  = Location::fetchAll();

        return $this->modalView('direct_link' , compact('categories', 'services' , 'staff' , 'locations' ,'service_id' ,'staff_id' ,'location_id'));
    }

    public function get_translations() {
        $rowId        = Helper::_post( 'row_id', '0', 'int' );
        $tableName    = Helper::_post( 'table', '', 'string' );
        $columnName   = Helper::_post( 'column', '', 'string' );
        $translations = json_decode( Helper::_post( 'translations', '', 'string' ), TRUE );
        $nodeType     = Helper::_post( 'node', 'input', 'string', [ 'input', 'textarea' ] );

        if ( empty( $tableName ) || empty( $columnName ) )
        {
            return $this->response( false, [
                'message' => 'Fields are not correct',
            ] );
        }

        // translationlari elave edib, sonra modali baglayib yeniden translation modalini acanda inputun translation datasini gonderirikki db da saxlanilmayan translationlari gore bilek
        if ( ! empty( $translations ) && is_array( $translations ) )
        {
            return $this->modalView( 'translations', [
                'translations' => $translations,
                'node'         => $nodeType,
                'id'           => $rowId,
                'column'       => $columnName ,
                'table'        => $tableName
            ] );
        }

        if ( $tableName === 'options' )
        {
            $translations = Translation::where( [
                'table_name'  => $tableName,
                'column_name' => $columnName
            ] )->fetchAll();
        }
        else if ( $rowId > 0 )
        {
            $translations = Translation::where( [
                'row_id'      => $rowId,
                'table_name'  => $tableName,
                'column_name' => $columnName
            ] )->fetchAll();
        } else
        {
            $translations = [];
        }

        return $this->modalView( "translations", [
            'translations' => $translations,
            'node'         => $nodeType,
            'id'           => $rowId,
            'column'       => $columnName,
            'table'        => $tableName
        ] );
    }

    public function save_translations() {
		Capabilities::mustTenant('dynamic_translations');

        $whiteList    = [ 'services', 'staff', 'service_categories' ,'locations', 'service_extras', 'form_inputs', 'form_input_choices', 'taxes', 'options' ];
        $translations = json_decode( Helper::_post( 'translations', '', 'string' ), TRUE );
        $tableName    = Helper::_post( 'table_name', '', 'string', apply_filters( 'bkntc_whitelist_translation_tables', $whiteList ) );
        $columnName   = Helper::_post( 'column_name', '', 'string' );
        $rowID        = Helper::_post( 'row_id', 0, 'int' );

        if ( empty( $translations ) || ! is_array( $translations ) || empty( $tableName ) || empty( $columnName ) )
        {
            return $this->response( false, [
                'message' => 'Please fill in all required fields correctly',
            ] );
        }

        foreach ( $translations AS $translation )
        {
            $id  = isset( $translation[ 'id' ] ) && ! empty( $translation[ 'id' ] ) ? $translation [ 'id' ] : 0;
            $locale = isset( $translation[ 'locale' ] ) && ! empty( $translation[ 'locale' ] ) ? $translation[ 'locale' ] : '';
            $value  = isset( $translation[ 'value' ] ) ? $translation[ 'value' ] : '';

            if ( empty( $locale ) ) continue;

            if ( $id > 0 )
            {
                Translation::where( [
                    'id' => $id,
                ] )->update( [
                    'locale' => $locale,
                    'value'  => $value
                ] );
            } else
            {
                Translation::insert( [
                    'row_id'       => $rowID,
                    'column_name'  => $columnName,
                    'table_name'   => $tableName,
                    'locale'       => $locale,
                    'value'        => $value
                ] );
            }
        }

        return $this->response( 200, [
            'message' => bkntc__( 'Saved successfully' )
        ] );
    }

    public function delete_translation() {
        $id = Helper::_post( 'id', 0, 'int' );

        if ( empty( $id ) )
        {
            return $this->response( false );
        }

        Translation::where( 'id', $id )->delete();

        return $this->response( true );
    }

    public function get_template_selection_modal()
    {
        if ( ! Helper::canShowTemplates() )
            return $this->response( false );

        $templates = $this->getTemplates();

        //if server/saas admin has no templates available, ignore the request and don't show the modal again
        if ( ! $templates )
        {
            //set `selected_a_template` option to true
            Helper::setOption( 'selected_a_template', 1 );

            return $this->response( true );
        }

        return $this->modalView( 'template-selection', [
            'templates' => $templates,
        ] );
    }

    public function apply_template()
    {
        $id = Helper::_post( 'id', 0, 'int' );

        if ( ! $id )
            return $this->response( false );

        $template = $this->getTemplate( $id );

        if ( ! $template )
            return $this->response( false );

        //create an applier instance
        $applier = new Applier( $template );

        //apply the given template
        $applier->apply();

        //set `selected_a_template` option to true
        Helper::setOption( 'selected_a_template', 1 );

        return $this->response( true );
    }

    public function skip_template_selection()
    {
        //set `selected_a_template` option to true
        Helper::setOption( 'selected_a_template', 1 );

        return $this->response( true );
    }

    /**
     * returns the template with the corresponding $id
     * @param int $id
     * @return array
     */
    private function getTemplate( $id )
    {
        if ( Helper::isSaaSVersion() )
            return apply_filters( 'bkntc_template_get', [], $id );

        $api = new FSCodeAPI();

        //Make an API call to retrieve the data from booknetic servers
        return $api->post( 'templates/get', [ 'id' => $id ] );
    }

    /**
     * @return array
     */
    private function getTemplates()
    {
        if ( Helper::isSaaSVersion() )
            return apply_filters( 'bkntc_templates_get_all', [] );

        $api = new FSCodeAPI();

        return $api->post( 'templates/all', [
            'select' => [ 'id', 'name', 'image', 'description' ],
            'where'  => [ 'is_default' => 0 ]
        ] );
    }
}
