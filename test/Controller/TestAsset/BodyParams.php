<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\OAuth2\Controller\TestAsset;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class BodyParams extends AbstractPlugin
{
    public function __invoke()
    {
        return [];
    }
}
