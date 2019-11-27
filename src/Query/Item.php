<?php

namespace Softonic\GraphQL\Query;

use Softonic\GraphQL\Traits\GqlIterator;

class Item implements ReadObject, \Iterator
{
    use GqlIterator;

    /**
     * @var array
     */
    private $arguments = [];

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function __get($key)
    {
        return $this->arguments[$key];
    }
}