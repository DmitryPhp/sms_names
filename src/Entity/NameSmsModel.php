<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class NameSmsModel
{
    public $user_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 3,
     *      max = 11,
     *      minMessage = "Name must be at least {{ limit }} characters long",
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    public $name;

    public $status;

    public $date_op;

    public $message;

}