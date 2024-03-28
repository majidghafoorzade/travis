<?php

require_once( __DIR__ . "/Fetch.php" );
require_once( __DIR__ . "/../config/database.php" );

class NobitexOrder {

    private $token;
    private $type;
    private $srcCurrency;
    private $dstCurrency;
    private $amount;
    private $price;
    private $execution;


    function __construct( $args ) {

        $this->token = $this->getToken();

        $this->type = $args["type"];
        $this->srcCurrency = $args["srcCurrency"];
        $this->dstCurrency = $args["dstCurrency"];
        $this->amount = $args["amount"];
        $this->price = $args["price"];
        $this->execution = "market";

        if( isset($args["execution"]) ) {
            $this->execution = $args["execution"];
        }
    }


    function send() {

        $request = new Fetch([
            'method' => 'POST',
            'url'    => 'https://api.nobitex.ir/market/orders/add',
            'header' => [
                "Authorization: Token {$this->token}",
            ],
            'body' => [
                "type" => $this->type,
                "execution" => $this->execution,
                "srcCurrency" => $this->srcCurrency,
                "dstCurrency" => $this->dstCurrency,
                "amount" => $this->amount,
                "price" => $this->price
            ]
        ]);

        $result = $request->send();

        return $result;
    }


    function getToken() {

        global $conn;

        $sql = "
            SELECT *
            FROM options
            WHERE option_key = 'nobitex_api_token'
        ";

        $result = $conn->query( $sql );

        // if( $result->num_rows <= 0 ) return $this->refreshToken();

        $row = $result->fetch_assoc();

        return $row["option_value"];
    }


    function refreshToken() {}

}