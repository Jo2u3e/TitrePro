<?php

namespace App\Class;

use Mailjet\Client;
use Mailjet\Resources;


class Mail
{
    private $api_key = '1b3c3d6059995cf52c31f32160173534';
    private $api_key_secret = 'ac93c3fdf507ebc9a34783d0ff4e31c9';


    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "joo971@hotmail.com",
                        'Name' => "Gwada Boutik"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3201559,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];

        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}