<?php

class Fetch {

    function __construct( $options ) {

        $this->method = $options["method"] || "GET";
        $this->url = $options["url"];
        $this->header = isset($options["header"]) ? $options["header"] : false;
        $this->body = isset($options["body"]) ? $options["body"] : false;
    }


    function send() {

        $headers = [];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);

        if( $this->method === "POST" ) curl_setopt($ch, CURLOPT_POST, 1);

        if( $this->header ) {
            foreach( $this->header as $header ) {
                $headers[] = $header;
            }
        }

        if ( $this->body ) {
            $payload = json_encode( $this->body );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        }

        $headers[] = "Content-Type:application/json";

        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($ch);
        $output = json_decode($output);

        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return [
            "response" => $output,
            "status" => $httpStatus
        ];

    }

}
