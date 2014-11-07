<?php
namespace HalClient;

interface ClientAwareInterface
{
    /**
     * @param  Client $client
     * @return void
     */
    public function setClient(Client $client);
}
