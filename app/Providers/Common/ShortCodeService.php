<?php

namespace BookneticApp\Providers\Common;

class ShortCodeService
{

	private $shortCodeCategories = [];

    /**
     * @var string
     */
    private $replacerFilterName;

    private $replacers = [];

    private $lazyShortCodeCallbacks = [];

    /**
     * @param string $replacerFilterName
     */
    public function setReplacerFilterName($replacerFilterName)
    {
        $this->replacerFilterName = $replacerFilterName;
        return $this;
    }

    public function addReplacer($replacer)
    {
        if (is_callable($replacer))
        {
            $this->replacers[] = $replacer;
        }

        return $this;
    }

    public function registerShortCodesLazily($callback)
    {
        if (is_callable($callback))
        {
            $this->lazyShortCodeCallbacks[] = $callback;
        }

        return $this;
    }



	/**
	 * @param array $data
	 */
	public function replace( $text, $data )
	{
        foreach ($this->replacers as $replacer)
        {
            $text = $replacer($text, $data, $this);
        }

        if (!empty($this->replacerFilterName))
        {
            $text = apply_filters( $this->replacerFilterName, $text, $data );
        }

        return $text;
	}

	public function getShortCodesList( $filterByDependsParameter = [], $filterByKind = [], $groupByCategory = false )
	{
        foreach ($this->lazyShortCodeCallbacks as $callback)
        {
            $callback($this);
        }
        $this->lazyShortCodeCallbacks = [];

		$filteredShortCodesList = [];
		foreach ( $this->shortCodeCategories AS $category => $shortCodeCategoryInfo )
		{
			$categoryName   = $shortCodeCategoryInfo['name'];
			$shortCodesList = $shortCodeCategoryInfo['short_codes'];

			foreach ( $shortCodesList AS $shortCodeInf )
			{
				if(
                    ( empty($filterByDependsParameter) || empty( $shortCodeInf['depends'] ) || in_array( $shortCodeInf['depends'], $filterByDependsParameter ) ) &&
                    ( empty($filterByKind) || in_array($shortCodeInf['kind'], $filterByKind) )
                )
				{
                    if ($groupByCategory)
                    {
                        if( ! isset( $filteredShortCodesList[ $category ] ) )
                        {
                            $filteredShortCodesList[ $category ] = [
                                'name'          =>  $categoryName,
                                'short_codes'   =>  []
                            ];
                        }

                        $filteredShortCodesList[ $category ]['short_codes'][] = $shortCodeInf;
                    }
                    else
                    {
                        $filteredShortCodesList[] = $shortCodeInf;
                    }

				}
			}
		}

		return $filteredShortCodesList;
	}

	public function registerCategory( $shortCodeCategory, $name )
	{
		if( ! isset( $this->shortCodeCategories[ $shortCodeCategory ] ) )
		{
            $this->shortCodeCategories[ $shortCodeCategory ] = [ 'short_codes' => [] ];
		}

        $this->shortCodeCategories[ $shortCodeCategory ]['name'] = $name;
	}

	public function registerShortCode( $shortCode, $params = [] )
	{
		$defaultParams = [
			'name'          =>  '',
			'description'   =>  '',
			'category'      =>  'others',
			'depends'       =>  '',
			'kind'          =>  ''
		];
		$params['code'] = $shortCode;
		$params = array_merge( $defaultParams, $params );
		$shortCodeCategory = $params['category'];

		if( ! isset( $this->shortCodeCategories[ $shortCodeCategory ] ) )
		{
            $this->registerCategory( $shortCodeCategory, $shortCodeCategory );
		}

        $this->shortCodeCategories[ $shortCodeCategory ]['short_codes'][] = $params;
	}

}

