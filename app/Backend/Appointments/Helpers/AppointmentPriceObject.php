<?php

namespace BookneticApp\Backend\Appointments\Helpers;

use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;

class AppointmentPriceObject
{

	private $uniqueKey;
	private $groupByKey;
	private $price;
	private $printAbs = false;
	private $label;
	private $priceView;
	private $isHidden = false;
	private $color;
	private $appointmentsCount = 1;
    private $negativeOrPositive = 1;
    private $isMergeable = true;


	public function __construct( $uniqueKey, $groupByKey = null )
	{
		$this->uniqueKey    = $uniqueKey;
		$this->groupByKey   = $groupByKey;
	}

	public function setAppointmentsCount( $count )
	{
		$this->appointmentsCount = $count;

		return $this;
	}

	public function getId()
	{
		return $this->uniqueKey;
	}

	public function getGroupByKey()
	{
		return $this->groupByKey;
	}

	public function setPrice( $price, $printAbs = null )
	{
		$this->price    = $price;

        if (!is_null($printAbs))
        {
            $this->printAbs = $printAbs;
        }

        return $this;
	}

	public function setLabel( $label )
	{
		$this->label = $label;

		return $this;
	}

	public function setPriceView( $priceView )
	{
		$this->priceView = $priceView;

		return $this;
	}

	public function getPrice( $sumForAllRecurringAppointments = false )
	{
		return   is_null( $this->price ) ? 0 : ( $sumForAllRecurringAppointments ? Math::mul($this->price, $this->appointmentsCount) : Math::floor($this->price) );
	}

	public function getPriceView( $sumForAllRecurringAppointments = false )
	{
		if( is_null( $this->priceView ) )
		{
			$price = $this->getPrice( $sumForAllRecurringAppointments );

			if( $this->printAbs )
			{
				$price = Math::abs( $price );
			}

			return Helper::price( $price );
		}

		return $this->priceView;
	}

	public function getLabel()
	{
		return is_null( $this->label ) ? $this->uniqueKey : $this->label;
	}

	public function setHidden( $isHidden )
	{
		$this->isHidden = $isHidden;

		return $this;
	}

	public function isHidden()
	{
		return $this->isHidden;
	}

    public function setNegativeOrPositive($sign = 1)
    {
        $this->negativeOrPositive = $sign;

        return $this;
    }

    public function getNegativeOrPositive()
    {
        return $this->negativeOrPositive;
    }

    public function setIsMergeable($isMergeable)
    {
        $this->isMergeable = $isMergeable;

        return $this;
    }

    public function isMergeable()
    {
        return $this->isMergeable;
    }

    public function setColor( $color )
    {
        $this->color = $color;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }


}