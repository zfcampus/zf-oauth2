<?php

namespace ZF\OAuth2\Document;

/**
 * Jti
 */
class Jti
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $audience;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var string
     */
    private $jti;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\OAuth2\Document\Client
     */
    private $client;

    public function exchangeArray(array $array)
    {
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'client':
                    $this->setClient($value);
                    break;
                case 'subject':
                    $this->setSubject($value);
                    break;
                case 'audience':
                    $this->setAudience($value);
                    break;
                case 'expires':
                    $this->setExpires($value);
                    break;
                case 'jti':
                    $this->setJti($value);
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'client' => $this->getClient(),
            'subject' => $this->getSubject(),
            'audience' => $this->getAudience(),
            'expires' => $this->getExpires(),
            'jti' => $this->getJti(),
        );
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Jti
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set audience
     *
     * @param string $audience
     * @return Jti
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * Get audience
     *
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return Jti
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set jti
     *
     * @param string $jti
     * @return Jti
     */
    public function setJti($jti)
    {
        $this->jti = $jti;

        return $this;
    }

    /**
     * Get jti
     *
     * @return string
     */
    public function getJti()
    {
        return $this->jti;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set client
     *
     * @param \ZF\OAuth2\Document\Client $client
     * @return Jti
     */
    public function setClient(\ZF\OAuth2\Document\Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \ZF\OAuth2\Document\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
