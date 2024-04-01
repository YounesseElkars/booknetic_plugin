<?php

namespace BookneticApp\Providers\Core\Templates;

trait Data
{
    /**
     * @var array $data
    */
    protected $data;

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @return array
     */
    public function get( $key )
    {
        if ( isset( $this->data[ $key ] ) )
            return $this->data[ $key ];

        return [];
    }
}