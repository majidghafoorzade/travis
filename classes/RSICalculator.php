<?php

class RSICalculator {

    function calculateRS( $prices ) {
        $total_profits = 0;
        $total_losses = 0;

        foreach( $prices as $price ) {
            if( $price >= 0 ) {
                $total_profits += $price;
            } else {
                $total_losses += $price;
            }
        }

        $RS = round($total_profits / ($total_losses * -1), 5);

        return $RS;
    }


    function calculateRSI( $RS ) {

        $RSI = round(100 - ( 100 / ( 1 + $RS ) ), 2);

        return $RSI;
    }


    function calculate( $prices ) {
        $RS = $this->calculateRS( $prices );
        $RSI = $this->calculateRSI( $RS );

        return $RSI;
    }
}