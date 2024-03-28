<?php

require_once( __DIR__ . "/../config/database.php" );
require_once( __DIR__ . "/../classes/Fetch.php" );
require_once( __DIR__ . "/../classes/RSICalculator.php" );
require_once( __DIR__ . "/../classes/NobitexOrder.php" );

class RSI14Trade {

    function __construct() {

        $updateResult = $this->updatePrice();

        if( !$updateResult ) return;
        
        $rsiCalcResult = $this->calculateRSI();

        if( !$rsiCalcResult ) return;

        $this->checkTrade();
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
            'url' => 'https://api.nobitex.ir/v2/orderbook/AVAXIRT'
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
            FROM avaxirt_price
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
            INSERT INTO avaxirt_price
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
            FROM avaxirt_price 
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
            INSERT INTO avaxirt_rsi
            (rsi)
            VALUES
            ($RSI)
        ";

        if( $conn->query( $sql ) === TRUE ) return true;

        return false;
    }


    function checkTrade() {

        $lastTradeType = $this->getLastTradeType();

        if( $lastTradeType === "buy" ) {
            $this->prepareSellOrder();
            return;
        }

        $this->prepareBuyOrder();
    }


    function getLastTradeType() {

        global $conn;

        $sql = "
            SELECT * 
            FROM avaxirt_trades
            ORDER BY reg_date DESC
            LIMIT 1
        ";

        $result = $conn->query( $sql );

        if( $result->num_rows <= 0 ) return "sell";

        $row = $result->fetch_assoc();

        return $row["type"];
    }


    function prepareBuyOrder() {

        if( !$this->checkBuyOrder() ) return;

        $this->sendBuyOrder();
    }


    function checkBuyOrder() {

        global $conn;
        $sql = "
            SELECT *
            FROM avaxirt_rsi
            ORDER BY reg_date DESC
            LIMIT 1
        ";

        $result = $conn->query( $sql );

        if( $result->num_rows <= 0 ) return false;

        $row = $result->fetch_assoc();

        return $row["rsi"] <= 30 ? true : false;
    }


    function sendBuyOrder() {

        $buyPrice = $this->getBuyPrice();

        if( !$buyPrice ) return;

        $this->sendBuyRequest( $buyPrice );
    }


    function getBuyPrice() {

        global $conn;
        $sql = "
            SELECT *
            FROM avaxirt_price
            ORDER BY reg_date DESC
            LIMIT 1  
        ";

        $result = $conn->query( $sql );

        if( $result->num_rows <= 0 ) return false;

        $row = $result->fetch_assoc();

        return $row["price"];
    }


    function prepareSellOrder() {

        if( !$this->checkSellOrder() ) return;

        $this->sendSellOrder();
    }


    function checkSellOrder() {

        global $conn;

        $buy_sql = "
            SELECT *
            FROM avaxirt_trades
            WHERE type = 'buy'
            ORDER BY reg_date DESC
            LIMIT 1
        ";

        $buy_result = $conn->query( $buy_sql );

        if( $buy_result->num_rows <= 0 ) return false;

        $buy_row = $buy_result->fetch_assoc();
        $buy_price = $buy_row["price"];
        
        $current_sql = "
            SELECT *
            FROM avaxirt_price
            ORDER BY reg_date DESC
            LIMIT 1
        ";

        $current_result = $conn->query( $current_sql );

        if( $current_result->num_rows <= 0 ) return false;

        $current_row = $current_result->fetch_assoc();
        $current_price = $current_row["price"];

        if( $current_price >= $buy_price * 1.01 ) return true;

        return false;
    }


    function sendSellOrder() {

        $sellPrice = $this->getSellPrice();

        if( !$sellPrice ) return;

        $this->sendSellRequest( $sellPrice );
    }


    function getSellPrice() {

        global $conn;

        $buy_sql = "
            SELECT *
            FROM avaxirt_trades
            WHERE type = 'buy'
            ORDER BY reg_date DESC
            LIMIT 1
        ";

        $buy_result = $conn->query( $buy_sql );

        if( $buy_result->num_rows <= 0 ) return false;

        $buy_row = $buy_result->fetch_assoc();

        return $buy_row["price"] * 1.01;
    }


    function sendBuyRequest( $buyPrice ) {
        
        $order = new NobitexOrder([
            'type' => 'buy',
            'srcCurrency' => 'avax',
            'dstCurrency' => 'rls',
            'amount' => round(800000 / $buyPrice, 2),
            'price' => $buyPrice,
            'execution' => 'market'
        ]);

        $result = $order->send();

        if( $result["response"]->status === "ok" ) {
            $this->saveOrderToDB( "buy", $buyPrice );
        }

    }


    function sendSellRequest( $sellPrice ) {
        
        $order = new NobitexOrder([
            'type' => 'sell',
            'srcCurrency' => 'avax',
            'dstCurrency' => 'rls',
            'amount' => floor((800000 / $sellPrice) * 100) / 100,
            'price' => $sellPrice,
            'execution' => 'market'
        ]);

        $result = $order->send();

        if( $result["response"]->status === "ok" ) {
            $this->saveOrderToDB( "sell", $sellPrice );
        }
    }


    function saveOrderToDB( $type, $price ) {

        global $conn;

        $sql = "
            INSERT INTO avaxirt_trades
            (type, price)
            VALUES
            ('$type', $price)
        ";

        $conn->query( $sql );
    }
}

$trade = new RSI14Trade();