<?php

if (!function_exists('currency_format')) {
    /**
     * Formats an amount for display with the store's currency settings.
     *
     * @param $number
     * @return string
     */
    function currency_format($number)
    {
        $settings = \Xeor\OctoCart\Models\Settings::instance();
        $numDecimals = isset($settings->num_decimals) && !empty($settings->num_decimals) ? $settings->num_decimals : 2;
        $decimalSep = isset($settings->decimal_sep) && !empty($settings->decimal_sep) ? $settings->decimal_sep : '.';
        $thousandSep = isset($settings->thousand_sep) && !empty($settings->thousand_sep) ? $settings->thousand_sep : ',';
        $price = number_format((float)$number, $numDecimals, $decimalSep, $thousandSep);

        $currency = isset($settings->currency) && !empty($settings->currency) ? $settings->currency : '$';

        $position = isset($settings->currency_pos) && !empty($settings->currency_pos) ? $settings->currency_pos : 'left';
        switch ($position) {
            case 'left':
                $price = $currency . $price;
                break;
            case 'right':
                $price = $price . $currency;
                break;
            case 'left_space':
                $price = $currency . ' ' . $price;
                break;
            case 'right_space':
                $price = $price . ' ' . $currency;
                break;
        }

        return $price;
    }
}
