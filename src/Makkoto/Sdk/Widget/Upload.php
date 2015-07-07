<?php

namespace Makkoto\Sdk\Widget;

class Upload extends \Makkoto\Sdk\Widget
{
    protected $template = 'upload';

    public function execute()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'
            OR ! isset($_POST['makkoto_upload']))
            return;

        if ( ! $_POST['makkoto_name'])
        {
            $name = $_FILES['makkoto_file']['name'];
        }
        else
        {
            $name = $_POST['makkoto_name'];
        }

        try
        {
            $this->client->request('post', 'user/photo', array(
                'multipart' => array(
                    array(
                        'name' => 'file',
                        'content' => fopen($_FILES['makkoto_file']['tmp_name'])
                    ),
                    array(
                        'name' => 'name',
                        'contents' => $name
                    )
                )
            ));
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $this->has_errors = TRUE;
        }
    }
}
