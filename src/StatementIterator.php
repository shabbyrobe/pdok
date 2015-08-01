<?php
namespace PDOK;

class StatementIterator implements \Iterator
{
    private $current;
    private $index = -1;

    function __construct(\PDOK\StatementInterface $statement, $method, $args)
    {
        $this->statement = $statement;
        $this->method = $method;
        $this->args = $args;
    }

    public function current() { return $this->current; }
    public function key()     { return $this->index; }
    public function next()    { $this->fetch(); $this->index++; }
    public function valid()   { return $this->current !== false; }

    public function rewind()
    {
        if ($this->index < 0) {
            $this->index = 0;
            $this->fetch();
        } else {
            throw new \LogicException("Cannot rewind");
        }
    }

    private function fetch()
    {
        $this->current = call_user_func_array(
            [$this->statement, $this->method],
            $this->args
        );
    }
}
