<?php


namespace BookneticApp\Providers\UI\Abstracts;


use BookneticApp\Providers\Core\Backend;
use BookneticApp\Providers\Core\Route;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\DB\DB;
use BookneticApp\Providers\DB\Model;

abstract class AbstractDataTableUI
{
    private $idField            =   'id';
    /**
     * @var Model
     */
    private $query				=	null;
    private $title				=	'';
    private $addNewButton		=	'';
    private $columns			=	[];
    private $rows				=	[];
    private $actions			=	[];
    private $rowCount			=	0;
    private $currentPage		=	1;
    private $rowsPerPage		=	8;
    private $orderBy			=	'id';
    private $orderByType		=	'DESC';
    private $isAjaxRequest		=	false;
    private $exportCSV			=	false;
    private $currentAction	    =	'';
    private $getChoicesAction	=	false;
    private $generalSearch		=	'';
    private $searchByColumns	=	[];
    private $hideGeneralSearch	=	false;
    private $attributes			=	[];
    private $exportBtn			=	false;
    private $importBtn			=	false;
    private $pagination			=	true;
    private $filters			=	[];
    private $module             = null;

    const ROW_INDEX 		    =   '__ROWINDEX__';
    const DEFAULT_VIEW 		    =   Backend::MODULES_DIR . 'Base' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'data_table.php';

    const ACTION_FLAG_NONE          = 0b000;
    const ACTION_FLAG_SINGLE        = 0b001;
    const ACTION_FLAG_BULK          = 0b010;
    const ACTION_FLAG_BULK_SINGLE   = 0b011;

    public function __construct( $query )
    {
        $this->query = $query;
        if( (static::$helper)::_post('fs-data-table', false, 'bool') )
        {
            $this->isAjaxRequest = true;

            $this->currentPage = (static::$helper)::_post('page', '1', 'int');
            if( $this->currentPage < 1 )
                $this->currentPage = 1;

            $this->generalSearch = (static::$helper)::_post('search', '', 'string');
        }
        else if( (static::$helper)::_get('export_csv', 'false', 'string') == 'true' )
        {
            $this->exportCSV = true;
        }
        else if( !empty( (static::$helper)::_post('fs-data-table-action', '', 'string') ) )
        {
            $this->currentAction = (static::$helper)::_post('fs-data-table-action', '', 'string');
        }
        else if( (static::$helper)::_post('action', false, 'string') === 'datatable_get_select_options' )
        {
            $this->getChoicesAction = true;
        }
    }

    public function setIdField( $fieldName )
    {
        $this->idField = $fieldName;

        return $this;
    }

    /***
     * @deprecated
     */
    public function isNotRemovable( )
    {
//        $this->isRemovable = false;

        return $this;
    }

    /***
     * @deprecated
     */
    public function noBulkAction( )
    {
//        $this->bulkAction = false;

        return $this;
    }

    public function setModule( $module )
    {
        $this->module = $module;
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getExportCSV()
    {
        return $this->exportCSV;
    }

    public function addAction( $key, $title, $callback = null, $flags = self::ACTION_FLAG_SINGLE )
    {
        $this->actions[$key] = [
            'id'        =>  $key,
            'key'       =>  $key,
            'title'     =>  $title,
            'callback'  => $callback,
            'flags'     => $flags
        ];

        return $this;
    }

    /***
     * @deprecated Use ->addAction() instead
     */
    public function deleteFn( $fn )
    {
//        $this->deleteFn = $fn;

        return $this;
    }

    public function addFilter( $columnName, $inputType = 'input', $placeholder = 'Filter', $searchType = '=', $choices = [], $colMd = 2 )
    {
        $this->filters[] = [
            'column_name'	=>	$columnName,
            'input_type'	=>	$inputType,
            'placeholder'	=>	$placeholder,
            'search_type'	=>	is_string( $searchType ) ? strtolower( $searchType ) : $searchType,
            'choices'		=>	$choices,
            'col_md'		=>	$colMd
        ];

        return $this;
    }

    public function addNewBtn( $btnTitle )
    {
        $this->addNewButton = $btnTitle;

        return $this;
    }

    public function activateExportBtn( )
    {
        $this->exportBtn = true;

        return $this;
    }

    public function activateImportBtn( )
    {
        $this->importBtn = true;

        return $this;
    }

    public function disablePagination()
    {
        $this->pagination = false;

        return $this;
    }

    public function noSearch()
    {
        $this->hideGeneralSearch = true;

        return $this;
    }

    public function setQuery( $query )
    {
        $this->query = $query;

        return $this;
    }

    public function setTitle( $title )
    {
        $this->title = $title;

        return $this;
    }

    public function setRowsPerPage( $n )
    {
        $this->rowsPerPage = $n;

        return $this;
    }

    public function searchBy( $columns )
    {
        $this->searchByColumns = $columns;

        return $this;
    }

    public function addColumns( $name, $sqlColumn, $options = [], $hideInExport = false )
    {
        $options['name'] = $name;
        $options['sql_column'] = $sqlColumn;

        $standartOptions = [
            'is_sortable'	=>	true,
            'is_shown'		=>	true,
            'type'			=>	'text',
            'is_html'		=>	false
        ];

        $column = array_merge($standartOptions, $options);

        if( $column['is_sortable'] && !isset($column['order_by_field']) )
        {
            if( $column['sql_column'] === static::ROW_INDEX || !is_string( $column['sql_column'] ) )
            {
                $column['is_sortable'] = false;
            }
            else
            {
                $column['order_by_field'] = $column['sql_column'];
            }
        }

        if( $hideInExport )
        {
            $column['is_shown'] = !$this->exportCSV;
        }

        $this->columns[] = $column;

        return $this;
    }

    public function addColumnsForExport( $name, $sqlColumn, $options = [] )
    {
        $options['is_shown'] = $this->exportCSV;

        return $this->addColumns( $name, $sqlColumn, $options );
    }

    public function addAttr( $attribute, $value )
    {
        if( !is_string( $attribute ) )
            return false;

        $this->attributes[ $attribute ] = $value;

        return $this;
    }

    private function queryWhere()
    {
        $where = $this->prepareFilters( );

        if( ! empty( $this->generalSearch ) && ! empty( $this->searchByColumns ) )
        {
            $this->query->where(function ( $query )
            {
                foreach ($this->searchByColumns AS $column)
                {
                    $query->orWhere($column, 'like', '%' . $this->generalSearch . '%');
                }
            });
        }

        return '';
    }

    private function prepareFilters( )
    {
        $filters = (static::$helper)::_post('filters', [], 'arr');
        $filtersSanitized = [];

        foreach( $filters AS $filter )
        {
            if(
                isset( $filter[0] ) && is_numeric( $filter[0] ) && isset( $this->filters[ $filter[0] ] )
                && isset( $filter[1] ) && is_string( $filter[1] )
            )
            {
                if ( isset( $filter[2] ) && is_string( $filter[2] ) )
                {
                    if( $filter[2] === 'date' )
                    {
                        $filter[1] = Date::reformatDateFromCustomFormat( $filter[1] );
                    }
                }
                $filtersSanitized[] = [ (int)$filter[0] , (string)$filter[1] ];
            }
        }


        if( empty( $filtersSanitized ) )
        {
            return;
        }

        foreach ( $filtersSanitized AS $filter )
        {
            $filterInf	= $this->filters[ $filter[0] ];
            $filterVal	= esc_sql( $filter['1'] );

            if( !is_string( $filterInf['search_type'] ) && is_callable( $filterInf['search_type'] ) )
            {
                $this->query = $filterInf['search_type']( $filterVal, $this->query );
            }
            else if( in_array( $filterInf['search_type'] , [ '=', '!=', '<>', '>', '<', '>=', '<=', 'like' ] ) )
            {
                if( $filterInf['search_type'] == 'like' )
                    $filterVal = '%'.$filterVal.'%';

                $this->query = $this->query->where($filterInf['column_name'], $filterInf['search_type'], $filterVal);
            }
        }
    }

    private function queryOrderBy()
    {
        $orderBy = (static::$helper)::_post('order_by', null, 'int');
        $orderByType = (static::$helper)::_post('order_by_type', 'DESC', 'string', ['ASC']);

        $activeColumnIndex = 0;
        foreach ( $this->columns AS $column )
        {
            if( ! $column['is_shown'] )
                continue;

            if( $orderBy == $activeColumnIndex )
            {
                if( $column['is_sortable'] )
                {
                    $this->orderBy		= $column['order_by_field'];
                    $this->orderByType	= $orderByType;
                }

                break;
            }

            $activeColumnIndex++;
        }

        $orderBy = '';
        foreach ( explode(',', $this->orderBy) AS $orderByCol )
        {
            if( !empty( $orderByCol ) )
            {
                $orderBy .= ( empty( $orderBy ) ? '' : ',' ) . $orderByCol . ' ' . $this->orderByType;
            }
        }

        return $orderBy;
    }

    private function getAllowedIds()
    {
        $ids = (static::$helper)::_post('ids', [], 'array');

        $idsArr = [];

        foreach ( $ids AS $nodeId )
        {
            if( is_numeric($nodeId) && $nodeId > 0 )
            {
                $idsArr[] = (int)$nodeId;
            }
        }

        if( empty( $idsArr ) )
            (static::$helper)::response(false);

        $idsStr = '"' . implode('","', $idsArr) . '"';

        // check ids for security reasons...
        $searchIds = DB::DB()->get_results("SELECT {$this->idField} FROM (".$this->query->toSql().") AS `tb` WHERE {$this->idField} IN ({$idsStr})", ARRAY_A);
        $allowedIdsArr = [];

        foreach($searchIds AS $searchId)
        {
            $allowedIdsArr[] = (int)$searchId['id'];
        }

        return $allowedIdsArr;
    }

    public function render()
    {
        if( ! empty( $this->currentAction ) && array_key_exists( $this->currentAction , $this->actions ) )
        {
            if( is_callable( $this->actions[ $this->currentAction ]['callback'] ) )
            {
                $allowedIdsArr = $this->getAllowedIds();

                call_user_func( $this->actions[ $this->currentAction ]['callback'] , $allowedIdsArr );
            }

            (static::$helper)::response(true);
        }



        if( $this->getChoicesAction )
        {
            $this->showSelectChoices();
            return null;
        }

        $where = $this->queryWhere();
        $orderBy = $this->queryOrderBy();

        $this->rowCount = DB::DB()->get_row("SELECT COUNT(0) AS `rowcount` FROM (".$this->query->toSql().") AS `FStbl`", ARRAY_A)['rowcount'];

        $maxPage = ceil( $this->rowCount / $this->rowsPerPage );

        if( $maxPage < $this->currentPage )
        {
            $this->currentPage = 1;
        }

        $limit		= (int)$this->rowsPerPage;
        $offset		= (int)( ($this->currentPage - 1) * $limit );

        if( $this->exportCSV )
        {
            $query = $this->query;
        }
        else
        {
            $query = $this->query->orderBy($orderBy);
            if ($this->pagination === true) {
                $query = $query->limit($limit)->offset($offset);
            }
        }

        $this->rows = $query->fetchAll();

        $thead = $this->getThead();
        $tbody = $this->getTbody();

        $hasBulkAction = false;
        foreach ($this->actions as $key => $action)
        {
            if ($action['flags'] & self::ACTION_FLAG_BULK)
            {
                $hasBulkAction = true;
                break;
            }
        }

        return [
            'title'				=>	$this->title,
            'hide_search'		=>	$this->hideGeneralSearch,
            'search'			=>	$this->generalSearch,
            'is_ajax'			=>	$this->isAjaxRequest,
            'row_count'			=>	$this->rowCount,
            'current_page'		=>	$this->currentPage,
            'max_page'			=>	ceil( $this->rowCount / $this->rowsPerPage ),
            'rows_per_page'		=>	$this->rowsPerPage,
            'order_by'			=>	$this->orderBy,
            'order_by_type'		=>	$this->orderByType,
            'thead'				=>	$thead,
            'tbody'				=>	$tbody,
            'actions'			=>	$this->actions,
            'attributes'		=>	$this->attributes,
            'add_new_btn'		=>	$this->addNewButton,
            'export_btn'		=>	$this->exportBtn,
            'import_btn'		=>	$this->importBtn,
            'pagination'		=>	$this->pagination,
            'bulk_action'		=>	$hasBulkAction,
            'filters'			=>	$this->filters
        ];
    }

    private function renderCSV( $dataTable, $exportOnly = [] )
    {
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 01 Jul 2001 04:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header('Content-Encoding: UTF-8');
        header("Content-Type: application/force-download");
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header('Content-Disposition: attachment;filename="' . Route::getCurrentModule() . '_' . Date::format('YMd') . '.csv"');
        header("Content-Transfer-Encoding: binary");

        if ( ob_get_length() > 0 )
        {
            ob_clean();
        }

        $df = fopen("php://output", 'w');

        fputs( $df, "\xEF\xBB\xBF" );

        $head = [];

        foreach ( $dataTable['thead'] AS $column )
        {
            $head[] = htmlspecialchars( $column['name'] );
        }

        fputcsv( $df, $head );

		if ( !empty($exportOnly) )
		{
			$sanitizedExports = array_filter($dataTable['tbody'], function($data) use ($exportOnly)
			{
				return is_numeric( array_search($data['id'], $exportOnly) );
			});

			if ( !empty($sanitizedExports) )
			{
				$dataTable['tbody'] = $sanitizedExports;
			}

		}

        foreach ( $dataTable['tbody'] AS $data )
        {
            $csvTr = [];

            foreach ( $data['data'] AS $getTd )
            {
                $csvTrTd = trim( strip_tags( $getTd['content'] ) );
                $csvTrTd = preg_replace("/[\r\n]+/", ' | ', $csvTrTd);
                $csvTrTd = preg_replace('/[ \t]+/', ' ', $csvTrTd);
                $csvTr[] = $csvTrTd;
            }

            fputcsv( $df, $csvTr );
        }

        fclose($df);

        exit;
    }

    public function renderHTML( $view = null )
    {
        $dataTable = $this->render();
        $dataTable = apply_filters('bkntc_datatable_after_render',$dataTable ,$this );

        if( $this->exportCSV )
        {

			$exportOnly = json_decode( (static::$helper)::_get('counts', '[]', 'str'), true );

            $this->renderCSV( $dataTable, $exportOnly );
            return true;
        }

        $view = empty($view) ? static::DEFAULT_VIEW : $view;

        if( file_exists($view) )
        {
            ob_start();
            require $view;
            $viewOutput = ob_get_clean();
        }
        else
        {
            $viewOutput = '('.htmlspecialchars( $view ).') View not found!';
        }

        if( $this->isAjaxRequest )
        {
            (static::$helper)::response(true, [
                'html'			=> htmlspecialchars($viewOutput),
                'rows_count'	=> $this->rowCount
            ]);
            return true;
        }

        return $viewOutput;
    }

    private function getThead()
    {
        $thead = [];

        foreach ($this->columns as $column)
        {
            if( !$column['is_shown'] )
                continue;

            $thead[] = [
                'name'				=>	$column['name'],
                'is_sortable'		=>	$column['is_sortable'],
                'order_by_field'	=>	$column['is_sortable'] ? $column['order_by_field'] : false
            ];
        }

        return $thead;
    }

    private function getTbody()
    {
        $data = [];
        $index = ($this->currentPage - 1) * $this->rowsPerPage;

        foreach ( $this->rows AS $row )
        {
            $index++;
            $newRow = [];
            foreach ( $this->columns AS $column )
            {
                if( !$column['is_shown'] )
                    continue;

                if( is_callable( $column['sql_column'] ) && is_object( $column['sql_column'] ) )
                {
                    $columnData = $column['sql_column']( $row );
                }
                else if( is_string( $column['sql_column'] ) &&  !empty($row[$column['sql_column']]) )
                {
                    $columnData = $row[ $column['sql_column'] ];

                    if( isset( $column['type'] ) )
                    {
                        $columnData = $this->columnTypeFilter( $columnData, $column['type'] );
                    }
                }
                else if( $column['sql_column'] === static::ROW_INDEX )
                {
                    $columnData = $index;
                }
                else
                {
                    $columnData = '-';
                }

                if( $column['is_html'] == false )
                {
                    $columnData = htmlspecialchars($columnData, ENT_QUOTES);
                }

                $attributes = [];

                if( isset( $column['attr'] ) )
                {
                    $attributes = $column['attr'];
                }

                $newRow[] = [
                    'content'		=>	$columnData,
                    'attributes'	=>	$attributes
                ];
            }

            $attributes = '';
            foreach ( $this->attributes AS $dataName => $dataValue )
            {
                if( is_callable( $dataValue ) && is_object( $dataValue ) )
                {
                    $attrData = $dataValue( $row );
                }
                else
                {
                    $attrData = isset( $row[ $dataValue ] ) ? $row[ $dataValue ] : '-';
                }

                $attributes .= ' data-' . htmlspecialchars( $dataName ) . '="' . htmlspecialchars( $attrData ) . '"';
            }

            $data[] = [
                'id'            =>  $row[ $this->idField ],
                'data'			=>	$newRow,
                'attributes'	=>	$attributes,
                'is_active' 	=>	isset( $row[ 'is_active' ] ) && ( int ) $row[ 'is_active' ] === 0 ? 0 : 1
            ];
        }

        return $data;
    }

    private function columnTypeFilter( $data, $type )
    {
        switch ($type)
        {
            case 'datetime':
                return empty( $data ) ? '' : Date::dateTime( $data );
                break;
            case 'date':
                return empty( $data ) ? '' : Date::datee( $data );
                break;
            case 'time':
                return empty( $data ) ? '' : Date::time( $data );
                break;
            case 'price':
                return (static::$helper)::price( $data );
                break;
            default:
                return $data;
                break;
        }
    }

    private function showSelectChoices()
    {
        $filterId	= (static::$helper)::_post('filter_id', false, 'int');
        $filter		= (static::$helper)::_post('q', '', 'string');

        if( $filterId === false || !isset( $this->filters[ $filterId ] ) || $this->filters[ $filterId ]['input_type'] != 'select' )
        {
            (static::$helper)::response(false);
        }

        $filterData	= $this->filters[ $filterId ];

        $choices	= $filterData['choices'];

        $data = [];

        if( isset( $choices['list'] ) && is_array( $choices['list'] ) )
        {
            foreach ( $choices['list'] AS $choiceKey => $choiceVal )
            {
                if( !(empty( $filter ) || strpos( $choiceKey, $filter ) !== false || strpos( $choiceVal, $filter ) !== false) )
                {
                    continue;
                }

                $data[] = [
                    'id'	=>	htmlspecialchars( $choiceKey ),
                    'text'	=>	htmlspecialchars( $choiceVal )
                ];
            }
        }
        else
        {
            $query	        = $choices['model'];
            $idField	    = isset( $choices['id_field'] ) ? $choices['id_field'] : 'id';
            $nameField	    = isset( $choices['name_field'] ) ? $choices['name_field'] : 'name';

            if( !empty( $filter ) )
            {
                $query->where( $nameField, 'like', "%{$filter}%" );
            }

            $searchResult = $query->select([$idField . ' AS id_as', $nameField . ' AS name_as'])->fetchAll();

            foreach ( $searchResult AS $result )
            {
                $data[] = [
                    'id'	=>	htmlspecialchars( $result['id_as'] ),
                    'text'	=>	htmlspecialchars( $result['name_as'] )
                ];
            }
        }

        (static::$helper)::response(true, [ 'results' => $data ]);
    }
}