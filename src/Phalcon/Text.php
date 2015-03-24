<?php
namespace Phalcon
{

    /**
     * Phalcon\Text
     *
     * Provides utilities to work with texts
     */
    abstract class Text
    {

        const RANDOM_ALNUM = 0;

        const RANDOM_ALPHA = 1;

        const RANDOM_HEXDEC = 2;

        const RANDOM_NUMERIC = 3;

        const RANDOM_NOZERO = 4;

        /**
         * Converts strings to camelize style
         *
         * <code>
         * echo \Phalcon\Text::camelize('coco_bongo'); //CocoBongo
         * </code>
         *
         * @param
         *            string str
         * @return string
         */
        public static function camelize($str)
        {
            // Make sure it is a string
            $str .= '';
            $len = strlen($str);
            $camelize_str = '';
            for ($i = 0; $i < $len; ++ $i) {
                $ch = $str[$i];
                if ($i == 0 || $ch == '-' || $ch == '_') {
                    if ($ch == '-' || $ch == '_') {
                        ++ $i;
                    }
                    $camelize_str .= strtoupper($str[$i]);
                } else {
                    $camelize_str .= strtolower($str[$i]);
                }
            }
            return $camelize_str;
        }

        /**
         * Uncamelize strings which are camelized
         *
         * <code>
         * echo \Phalcon\Text::camelize('CocoBongo'); //coco_bongo
         * </code>
         *
         * @param
         *            string str
         * @return string
         */
        public static function uncamelize($str)
        {
            // Make sure it is a string
            $str .= '';
            $len = strlen($str);
            $uncamelize_str = '';
            
            for ($i = 0; $i < $len; ++ $i) {
                $ch = ord($str[$i]);
                if ($ch >= 65 && $ch <= 90) {
                    if ($i > 0) {
                        $uncamelize_str .= '_';
                    }
                    $uncamelize_str .= chr($ch + 32);
                } else {
                    $uncamelize_str .= chr($ch);
                }
            }
            return $uncamelize_str;
        }

        /**
         * Adds a number to a string or increment that number if it already is defined
         *
         * <code>
         * echo \Phalcon\Text::increment("a"); // "a_1"
         * echo \Phalcon\Text::increment("a_1"); // "a_2"
         * </code>
         *
         * @param string $str            
         * @param string $separator            
         * @return string
         */
        public static function increment($str, $separator = null)
        {
            if ($separator == null) {
                $separator = '_';
            }
            list ($prefix, $number) = explode($separator, $str . $separator . '0');
            ++ $number;
            return $prefix . $separator . $number;
        }

        /**
         * Generates a random string based on the given type.
         * Type is one of the RANDOM_* constants
         *
         * <code>
         * echo \Phalcon\Text::random(Phalcon\Text::RANDOM_ALNUM); //"aloiwkqz"
         * </code>
         *
         * @param
         *            int type
         * @param
         *            int length
         * @return string
         */
        public static function random($type = null, $length = null)
        {
            switch ($type) {
                case Text::RANDOM_ALPHA:
                    $pool = array_merge(range('a', 'z'), range('A', 'Z'));
                    break;
                case Text::RANDOM_HEXDEC:
                    $pool = array_merge(range(0, 9), range('a', 'f'));
                    break;
                case Text::RANDOM_NUMERIC:
                    $pool = range(0, 9);
                    break;
                case Text::RANDOM_NOZERO:
                    $pool = range(1, 9);
                    break;
                default:
                    
                    // Default type \Phalcon\Text::RANDOM_ALNUM
                    $pool = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
                    break;
            }
            $end = count($pool) - 1;
            $str = '';
            while (strlen($str) < $length) {
                $str .= $pool[mt_rand(0, $end)];
            }
            
            return $str;
        }

        /**
         * Check if a string starts with a given string
         *
         * <code>
         * echo \Phalcon\Text::startsWith("Hello", "He"); // true
         * echo \Phalcon\Text::startsWith("Hello", "he"); // false
         * echo \Phalcon\Text::startsWith("Hello", "he", false); // true
         * </code>
         *
         * @param
         *            string str
         * @param
         *            string start
         * @param
         *            boolean ignoreCase
         * @return boolean
         */
        public static function startsWith($str, $start, $ignoreCase = null)
        {
            return ($ignoreCase ? strcasecmp($start, substr($str, 0, strlen($start))) : strcmp($start, substr($str, 0, strlen($start)))) == 0;
        }

        /**
         * Check if a string ends with a given string
         *
         * <code>
         * echo \Phalcon\Text::endsWith("Hello", "llo"); // true
         * echo \Phalcon\Text::endsWith("Hello", "LLO"); // false
         * echo \Phalcon\Text::endsWith("Hello", "LLO", false); // true
         * </code>
         *
         * @param
         *            string str
         * @param
         *            string end
         * @param
         *            boolean ignoreCase
         * @return boolean
         */
        public static function endsWith($str, $end, $ignoreCase = null)
        {
            return ($ignoreCase ? strcasecmp($end, substr($str, - strlen($end))) : strcmp($end, substr($str, - strlen($end)))) == 0;
        }

        /**
         * Lowercases a string, this function makes use of the mbstring extension if available
         *
         * @param string $str            
         * @return string
         */
        public static function lower($str)
        {
            /**
             * 'lower' checks for the mbstring extension to make a correct lowercase transformation
             */
            if (function_exists("mb_strtolower")) {
                return mb_strtolower(str);
            }
            return strtolower(str);
        }

        /**
         * Uppercases a string, this function makes use of the mbstring extension if available
         *
         * @param
         *            string str
         * @return string
         */
        public static function upper($str)
        {
            /**
             * 'upper' checks for the mbstring extension to make a correct lowercase transformation
             */
            if (function_exists("mb_strtoupper")) {
                return mb_strtoupper(str);
            }
            return strtoupper(str);
        }
    }
}
