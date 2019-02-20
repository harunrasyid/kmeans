<?php
namespace Permengandum\Kmeans\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct($message = 'Not Found', \Exception $previous = null, array $headers = array())
    {
        parent::__construct(404, $message, $previous, $headers);
    }
}
