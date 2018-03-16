<?php

namespace Draw;

use Ratchet\ConnectionInterface;
use Draw\DrawSession;

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
     * @var DrawSession
     */
    private $session;

    private $color;

    /**
     * DrawClient constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection) {
        $this->connection = $connection;
        $this->name = "User-$connection->resourceId";
        $this->session = null;
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
     * @return ConnectionInterface
     */
    function send($data) {
        return $this->connection->send($data);
    }

    /**
     * Close the connection
     */
    function close() {
        $this->connection->close();
    }

    /**
     * @return \Draw\DrawSession
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * @param \Draw\DrawSession $session
     */
    public function setSession($session) {
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->connection->resourceId;
    }
}
