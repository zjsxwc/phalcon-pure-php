<?php
namespace Phalcon
{

    class Kernel
    {

        /**
         * Produces a pre-computed hash key based on a string. This function produce different numbers in 32bit/64bit processors
         * @param string key
         * @return string
         */
        public static function preComputeHashKey($key)
        {
            $arKey = $key.'\0';
            $arKeyIndex = 0;
            $nKeyLength = strlen($key) + 1;
            
            $hash = 5381;
            
            for(;$nKeyLength >= 8; $nKeyLength -= 8){
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
            }
            
            switch ($nKeyLength) {
                case 7:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                case 6:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                case 5:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                case 4:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                case 3:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                case 2:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                case 1:
                    $hash = (($hash << 5) + $hash) + ord($arKey[$arKeyIndex++]);
                    break;
                
            }
            return substr(sprintf('%lu', $hash),0,24);
        }
    }
}
