<?php

namespace RollNApi\Hydrator\Filter;

use Zend\Stdlib\Hydrator\Filter\FilterInterface;

class ExcludeUser implements FilterInterface
{
    public function filter($field)
    {
        if ($field == 'user') {
            return false;
        }

        return true;
    }
}