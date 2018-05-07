<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\OAuth2\Controller\TestAsset;

use RuntimeException;
use ZF\ApiProblem\Exception\ProblemExceptionInterface;

class CustomProblemDetailsException extends RuntimeException implements ProblemExceptionInterface
{
    public $type;
    public $title;
    public $details;

    public function getType()
    {
        return $this->type;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAdditionalDetails()
    {
        return $this->details;
    }
}
