<?php
require 'vendor/autoload.php';
use Guzzle\Http\Client;

try {
    //Scenario 1
    if(!isset($_COOKIE['tokenCookie'])) {
        // Create a client to work with the Great Food Ltd API
        $client = new \GuzzleHttp\Client();
        $request = $client->post(
            'https://api.gfl',
            array(
                'form_params' => array(
                    'client_secret' => '4j3g4gj304gj3',
                    'client_id' => '1337',
                    'grant_type	' => 'client_credentials'
                )
            )
        );

        $response = $request->send();
        $data = $response->json();

        if(isset($data['access_token'])) {
            $tokenCookie = $data['access_token'];
            $expiryTime = isset($data['expires_in']) ? $data['expires_in'] : 0;
            setcookie("tokenCookie", $expiryTime, time()+$expiryTime);
        } else {
            throw new \Exception("No token delivered");
        }
    }

    $basicauth = new Client(['base_uri' => 'https://api.gfl']);
    $newresponse01 = $basicauth->request(
        'GET',
        '/menus',
        ['headers' => 
            [
                'Authorization' => "Bearer {$tokenCookie}"
            ]
        ]
    )->json();

    $key = array_search('Takeaway', $newresponse01["data"]);
    if(!isset($key)) {
        throw new \Exception("No key found");
    }

    $basicauth = new Client(['base_uri' => 'https://api.gfl']);
    $newresponse02 = $basicauth->request(
        'GET',
        '/menu/'.$key.'/products',
        ['headers' => 
            [
                'Authorization' => "Bearer {$tokenCookie}"
            ]
        ]
    )->json();

    $out = "<table>";
    $out .= "<tr>";
    $out .= "<th>ID</th>";
    $out .= "<th>NAME</th>";
    $out .= "</tr>";
    foreach ($newresponse02["data"] as $key => $value){
        $out .= "<tr>";
        $out .= "<td>".$value['id']."</td>";
        $out .= "<td>".$value['name']."</td>";
        $out .= "</tr>";
    }
    $out = "</table>";
    echo $out;

    //Scenario 2

    $basicauth = new Client(['base_uri' => 'https://api.gfl']);

    $newresponse03 = $basicauth->request(
        'PUT',
        '/menu/7/product/84',
        [
            'headers' => [
                'Authorization' => "Bearer {$tokenCookie}"
            ],
            'json' => [
                'id' => 7,
                'name' => 'Chips'
            ]
        ]
    );

    // print the status code to check if the product was updated
    echo $response->getStatusCode();
} catch (\Exception $e) {
    die('Error : ' . $e->getMessage());
}