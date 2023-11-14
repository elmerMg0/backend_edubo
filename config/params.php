<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'apiGoogle' => 'https://www.googleapis.com/oauth2/v1/userinfo/',
    'username' => 'horarios_consa',
    'password' => 'ca460ac963d199604aab8e3e5b0c25397f033fd7634d3ffc79bede90f89b773f6',
    'jwt' => [
        'issuer' => 'https://api.example.com',  //name of your project (for information only)
        'audience' => 'https://frontend.example.com',  //description of the audience, eg. the website using the authentication (for info only)
        'id' => 'UNIQUE-JWT-IDENTIFIER',  //a unique identifier for the JWT, typically a random string
        'expire' => 300,  //the short-lived JWT token is here set to expire after 5 min.
    ],
];
