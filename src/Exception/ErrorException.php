<?php
namespace App\Exception;

class ErrorException extends \Exception implements PublishedMessageException, UserInputException
{
}