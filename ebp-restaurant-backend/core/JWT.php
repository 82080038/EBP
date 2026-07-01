<?php

class JWT
{

    private $secret = "ebp_secret_key_change_in_production";

    private $algorithm = "HS256";



    public function encode($payload)
    {

        $header = json_encode([

            'typ' => 'JWT',

            'alg' => $this->algorithm

        ]);



        $header = $this->base64UrlEncode($header);

        $payload = json_encode($payload);

        $payload = $this->base64UrlEncode($payload);



        $signature = hash_hmac(

            'sha256',

            $header . "." . $payload,

            $this->secret,

            true

        );

        $signature = $this->base64UrlEncode($signature);



        return $header . "." . $payload . "." . $signature;

    }



    public function decode($token)
    {

        $tokenParts = explode('.', $token);



        if (count($tokenParts) !== 3) {

            return false;

        }



        list($header, $payload, $signature) = $tokenParts;



        $validSignature = hash_hmac(

            'sha256',

            $header . "." . $payload,

            $this->secret,

            true

        );

        $validSignature = $this->base64UrlEncode($validSignature);



        if ($signature !== $validSignature) {

            return false;

        }



        $payload = $this->base64UrlDecode($payload);

        return json_decode($payload, true);

    }



    private function base64UrlEncode($data)
    {

        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

    }



    private function base64UrlDecode($data)
    {

        return base64_decode(strtr($data, '-_', '+/'));

    }

}
