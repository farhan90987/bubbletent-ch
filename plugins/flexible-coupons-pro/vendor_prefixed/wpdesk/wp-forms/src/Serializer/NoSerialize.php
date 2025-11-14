<?php

namespace FlexibleCouponsProVendor\WPDesk\Forms\Serializer;

use FlexibleCouponsProVendor\WPDesk\Forms\Serializer;
class NoSerialize implements Serializer
{
    public function serialize($value)
    {
        return $value;
    }
    public function unserialize($value)
    {
        return $value;
    }
}
