<?php

namespace Draw;

use Ratchet\ConnectionInterface;

/**
 * Class DrawSession
 *
 * @package Draw
 */
class DrawSession {

    /**
     * @var string $pin
     */
    protected $pin;

    /**
     * @var \SplObjectStorage $clients
     */
    protected $clients;

    /**
     * Session constructor.
     */
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * @return string
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * Generates a pin that does not belong to any other sessions
     * @param \SplObjectStorage $sessions
     */
    public function generatePin(\SplObjectStorage $sessions) {
        $pin = (string) rand(100000, 999999);
        $unique = false; // Assume not unique
        while(!$unique) {
            $unique = true;
            /** @var DrawSession $session */
            foreach ($sessions as $session) {
                if($session->getPin() === $pin) $unique = false;
            }
            $pin = (string) rand(100000, 999999);
        }
        $this->pin = $pin;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getClients() {
        return $this->clients;
    }

    /**
     * @param ConnectionInterface $client
     */
    public function addClient(ConnectionInterface $client) {
        $this->clients->attach($client);
    }
}