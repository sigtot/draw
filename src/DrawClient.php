<?php

namespace Draw;

use Ratchet\ConnectionInterface;

class DrawClient implements ConnectionInterface{
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var ConnectionInterface conn
     */
    private $connection;

    /**
     * DrawClient constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection) {
        $this->connection = $connection;
        $this->name = "User-$connection->resourceId";
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection($connection) {
        $this->connection = $connection;
    }

    /**
     * Send data to the connection
     *
     * @param  string $data
     *
     * @return \Ratchet\ConnectionInterface
     */
    function send($data) {
        $this->connection->send($data);
    }

    /**
     * Close the connection
     */
    function close() {
        $this->connection->close();
}}
