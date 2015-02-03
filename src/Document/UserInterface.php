<?php

/**
 * Because the user is decoupled from the OAuth2 Client entity
 * this interface serves for what OAuth2 needs from the User
 */

namespace ZF\OAuth2\Document;

/**
 * UserInterface
 */
interface UserInterface
{
    public function getId();
    public function getUsername();
    public function setUsername();
    public function getPassword();
    public function setPassword();
}
