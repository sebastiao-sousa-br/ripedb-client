<?php

namespace Test;

/**
 * A proxy class for the various IP libraries out there. The sole requirement 
 * for a valid IP object is that it can be stringified to an IP address.
 */
class IP
{
    protected $address;

    public function __construct($ip)
    {
        if (!filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            throw new \Exception('Invalid IPv4 address.');
        }
        $this->address = $ip;
    }

    public function __toString()
    {
        return $this->address;
    }
}
