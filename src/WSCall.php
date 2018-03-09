<?php

namespace Draw;

class WSCall {
    /**
     * @var string $method
     */
    public $method;

    /**
     * WSCall constructor.
     *
     * @param string $method
     */
    public function __construct($method) {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * @param string $property
     * @param string $value
     */
    public function addProperty($property, $value) {
        $this->{$property} = $value;
    }
}