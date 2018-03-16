<?php

namespace Draw;

use Ratchet\ConnectionInterface;
use Draw\DrawClientStorage;
use Draw\DrawSessionStorage;
use Draw\DrawClient;

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

    const NICE_COLORS = array(
        'primary' => array('ffeb3b', '259b24', 'ff9800', 'e51c23', '9c27b0', 'e91e63', '03a9f4'),
        'secondary' => array('8bc34a', 'cddc39', 'ffc107', 'ff5722', 'ffeb3b', '00bcd4', '673ab7', '5677fc', '009688'),
    );

    /**
     * Session constructor.
     */
    public function __construct() {
        $this->clients = new DrawClientStorage();
    }

    /**
     * Choose a color for $client. First choose among the primary colors, when those are used up,
     * choose among the secondary, and when those are used up too, allow for duplicate colors.
     * @param DrawClient $client
     */
    private function setClientColor(DrawClient $client){
        $primaryColors = self::NICE_COLORS['primary'];
        $secondaryColors = self::NICE_COLORS['secondary'];
        $takenColors = array();
        /** @var DrawClient $c */
        foreach ($this->clients as $c) {
            array_push($takenColors, $c->getColor());
        }

        if(sizeof($takenColors) < sizeof($primaryColors)) {
            $availableColors = array_diff($primaryColors, $takenColors);
        } else if(sizeof($takenColors) < sizeof($primaryColors) + sizeof($secondaryColors)) {
            $availableColors = array_diff($secondaryColors, $takenColors);
        } else {
            $availableColors = array_merge($primaryColors, $secondaryColors);
        }

        $client->setColor($availableColors[array_rand($availableColors)]);
    }

    /**
     * @return string
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * Generates a pin that does not belong to any other sessions
     * @param DrawSessionStorage $sessions
     */
    public function generatePin(DrawSessionStorage $sessions) {
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
     * @return DrawClientStorage
     */
    public function getClients() {
        return $this->clients;
    }

    /**
     * @return array of client data (name and color)
     */
    public function getClientData() {
        $clientArray = array();
        /** @var DrawClient $client */
        foreach ($this->clients as $client) {
            array_push($clientArray, array(
                'name' => $client->getName(),
                'color' => $client->getColor(),
                'id' => $client->getId(),
            ));
        }
        return $clientArray;
    }

    /**
     * Adds client to $clients and set the client's session to this session
     *
     * @param DrawClient $client
     */
    public function addClient(DrawClient $client) {
        $this->clients->attach($client);
        $client->setSession($this);

        $this->setClientColor($client);

        $returnCall = new WSCall('join_session', array(
            'pin' => $this->getPin(),
            'clients' => $this->getClientData(),
        ));
        $client->send(json_encode($returnCall));

        $this->notifyClients('client_added', array('client' => array(
            'name' => $client->getName(),
            'color' => $client->getColor(),
            'id' => $client->getId(),
        )), $client);
    }

    /**
     * Remove client from $clients and null the client's session
     *
     * @param DrawClient $client
     */
    public function removeClient(DrawClient $client) {
        $this->clients->detach($client);
        $client->setSession(null);

        $this->notifyClients('client_left', array('client' => array(
            'name' => $client->getName(),
            'id' => $client->getId(),
        )), $client);
    }

    /**
     * Notify all the session's clients of an event.
     *
     * @param string $event
     * @param array $data is a relational array containing extra fields for the WSCall object
     * @param DrawClient $excludedClient
     */
    public function notifyClients($event, $data = null, $excludedClient = null){
        // Create the return call object
        $returnCall = new WSCall($event);
        if($data !== null) {
            foreach ($data as $property => $value) {
                $returnCall->addProperty($property, $value);
            }
        }

        // Send it to all clients in the session (except maybe one excluded client)
        foreach ($this->clients as $client) {
            if($excludedClient == null || $client !== $excludedClient)
                $client->send(json_encode($returnCall));
        }
    }
}
