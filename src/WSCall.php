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
     * @param mixed $data
     */
    public function __construct($method, $data = null) {
        $this->method = $method;
        foreach ($data as $property => $value) {
            $this->addProperty($property, $value);
        }
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
     * @param mixed $value
     */
    public function addProperty($property, $value) {
        $this->{$property} = $value;
    }
}