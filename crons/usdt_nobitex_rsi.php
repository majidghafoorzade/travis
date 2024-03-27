<?php

require_once( __DIR__ . "/../config/database.php" );
require_once( __DIR__ . "/../classes/Fetch.php" );
require_once( __DIR__ . "/../classes/RSICalculator.php" );

class RSI14Trade {

    function __construct() {

        $updateResult = $this->updatePrice();

        if( !$updateResult ) return;
        
        $rsiCalcResult = $this->calculateRSI();

        if( !$rsiCalcResult ) return;


    }


    function updatePrice() {
        $current_price = $this->getCurrentPrice();

        if( !$current_price ) return false;
        
        $dbResult = $this->insertCurrentPriceToDB( $current_price );

        if( !$dbResult ) return false;

        return true;
    }


    function getCurrentPrice() {
        $req = new Fetch([
            'method' => 'GET',
            'url' => 'https://api.nobitex.ir/v2/orderbook/USDTIRT'
        ]);

        $res = $req->send();

        if(
            !isset( $res["response"] ) ||
            !isset( $res["response"]->status ) ||
            $res["response"]->status !== "ok"
        ) {
            // Send Alert
            return false;
        }

        return $res["response"]->lastTradePrice;
    }


    function getPrevPrice( $price ) {

        global $conn;
        
        $sql = "
            SELECT price
            FROM usdtirt_price
            ORDER BY reg_date DESC
            LIMIT 1 
        ";

        $result = $conn->query($sql);

        if ($result->num_rows <= 0) return false;

        $row = $result->fetch_assoc();

        $prev_price = $row["price"];

        return $prev_price;
    }


    function insertCurrentPriceToDB( $price ) {
        
        global $conn;

        $price_change = 0;
        $prev_price = $this->getPrevPrice( $price );

        if( $prev_price ) {
            $price_change = $price - $prev_price;
        }

        $sql = "
            INSERT INTO usdtirt_price
            (price, price_change)
            VALUES
            ($price, $price_change)
        ";

        // Send Alert
        if( $conn->query( $sql ) === TRUE ) {
            return true;
        }

        return false;
    }


    function calculateRSI() {
        
        global $conn;
        $prices = [];

        $sql = "
            SELECT price_change
            FROM usdtirt_price 
            ORDER BY reg_date DESC
            LIMIT 14
        ";

        $result = $conn->query( $sql );

        while( $row = $result->fetch_assoc() ) {
            $prices[] = $row["price_change"];
        }

        if( count( $prices ) < 14 ) return false; 

        $RSI_calc = new RSICalculator();
        $RSI = $RSI_calc->calculate( $prices );

        if( $this->insertRSIToDB($RSI) ) return true;

        return false;
    }


    function insertRSIToDB( $RSI ) {

        global $conn;

        $sql = "
            INSERT INTO usdtirt_rsi
            (rsi)
            VALUES
            ($RSI)
        ";

        if( $conn->query( $sql ) === TRUE ) return true;

        return false;
    }
}

$trade = new RSI14Trade();