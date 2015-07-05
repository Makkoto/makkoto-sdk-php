<?php

namespace Makkoto\Sdk;

class Client
{
    private $makkoto_id;
    private $client;
    private $api_id;
    private $api_secret;

    public function __construct($makkoto_id)
    {
        $this->makkoto_id = $makkoto_id;
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'http://baas.makkoto.com']);

        if ( ! isset($_SESSION))
        {
            session_start();
        }

        if (isset($_SESSION['makkoto_api_id']))
        {
            $this->set_auth_details_from_session();
        }
    }

    public function login(array $form_params)
    {
        $get_api_details = $this->request('post', 'user/api', $form_params);
        $api_details = json_decode($get_api_details->getBody());
        $_SESSION['makkoto_api_id'] = $api_details->id;
        $_SESSION['makkoto_api_secret'] = $api_details->secret;
        $this->set_auth_details_from_session();
        return $get_api_details;
    }

    public function request($method, $resource, array $form_params = array())
    {
        $body = array(
            'headers' => array(
                'Makkoto' => $this->makkoto_id,
                'Accept' => 'application/hal+json;ver=1',
                'Date' => date(DATE_RFC1123)
            )
        );

        if ($this->api_id)
        {
            $body['headers']['Authentication'] = $this->get_auth_signature(
                strtoupper($method), $resource, $this->api_id, $this->api_secret
            );
        }

        if (isset($form_params['multipart']))
        {
            $body['multipart'] = $form_params['multipart'];
        }
        elseif ($form_params)
        {
            $body['form_params'] = $form_params;
        }

        $method = strtolower($method);
        return $this->client->$method($resource, $body);
    }

    private function get_auth_signature($method, $resource, $api_id, $api_secret)
    {
        $canonical_payload = $method."\n".date(DATE_RFC1123).$resource;
        $signature = base64_encode(
            hash_hmac('sha1', $canonical_payload, $api_secret, TRUE)
        );
        return 'MAKKOTO '.$api_id.':'.$signature;
    }

    private function set_auth_details_from_session()
    {
        $this->api_id = $_SESSION['makkoto_api_id'];
        $this->api_secret = $_SESSION['makkoto_api_secret'];
    }
}
