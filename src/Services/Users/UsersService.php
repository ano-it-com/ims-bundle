<?php

namespace ANOITCOM\IMSBundle\Services\Users;

use ANOITCOM\Wiki\Repository\UserRepository;

class UsersService
{

    /**
     * @var UserRepository
     */
    private $userRepository;


    public function __construct(UserRepository $userRepository)
    {

        $this->userRepository = $userRepository;
    }
}