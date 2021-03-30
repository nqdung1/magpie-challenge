<?php

namespace App;

class Product
{
    /**
     * Title product.
     *
     * @var string
     */
    public $title;

    /**
     * Price product.
     *
     * @var float
     */
    public $price;

    /**
     * Image url product.
     *
     * @var string
     */
    public $imageUrl;

    /**
     * Capacity product (MB)
     *
     * @var int
     */

    public $capacityMB;
    /**
     * Colour product
     *
     * @var string
     */
    public $colour;

    /**
     * is available
     *
     * @var bool
     */
    public $isAvailable;

    /**
     * Availability Text
     *
     * @var string
     */
    public $availabilityText;

    /**
     * shipping text
     *
     * @var string
     */
    public $shippingText;

    /**
     * shipping date (Y-m-d)
     *
     * @var string
     */
    public $shippingDate;

    /**
     * Create a new Product model instance.
     *
     * @param  array  $attributes
     * @return void
     */

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    private function fill(array $attributes)
    {
        foreach ($attributes as $key=>$val) {
            $this->{$key} = $val;
        }
    }

    public function __toString() {
        return $this->title;
    }
}
