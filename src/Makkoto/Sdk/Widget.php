<?php

namespace Makkoto\Sdk;

class Widget
{
    protected $client;
    protected $renderer;
    protected $template;
    protected $data = array();

    public function __construct($makkoto_id, $username, $password)
    {
        $this->client = new Client($makkoto_id);
        $this->renderer = new \Mustache_Engine;

        try
        {
            $this->client->login(array(
                'username' => $username,
                'password' => $password
            ));
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $response = $e->getResponse();
            throw new \Exception(
                $response->getStatusCode().' - '.
                $response->getBody()
            );
        }
    }

    public function render()
    {
        return $this->renderer->render(
            file_get_contents(dirname(__FILE__).'/ui/'.$this->template.'.mustache'),
            $this->data
        );
    }
}
