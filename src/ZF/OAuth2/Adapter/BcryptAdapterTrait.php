<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Adapter;

use Zend\Crypt\Password\Bcrypt;

/**
 * Trait BcryptAdapterTrait
 *
 * @package ZF\OAuth2\Adapter
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
trait BcryptAdapterTrait 
{
    protected $bcryptCost = 10;

    /**
     * @var Bcrypt
     */
    protected $bcrypt;

    /**
     * @param $value
     */
    public function setBcryptCost($value)
    {
        $this->bcryptCost = (int) $value;
    }

    /**
     * @return int
     */
    public function getBcryptCost()
    {
        return $this->bcryptCost;
    }

    /**
     * @return Bcrypt
     */
    public function getBcrypt()
    {
        if (null === $this->bcrypt) {
            $this->bcrypt = new Bcrypt();
            $this->bcrypt->setCost($this->getBcryptCost());
        }

        return $this->bcrypt;
    }
}
 