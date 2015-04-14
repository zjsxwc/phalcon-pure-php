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
            return \Phalcon\Text::_starts_with($str, $start, $ignoreCase);
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
            return \Phalcon\Text::_ends_with($str, $end, $ignoreCase);
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
        
        /* == internal funciton area == */
        /**
         * Filter alphanum string
         *
         * @param string $param            
         * @return string
         */
        public static function _filter_alphanum($param)
        {
            $param .= '';
            $filtered_str = '';
            $len = strlen($param);
            for ($i = 0; $i < $len; ++ $i) {
                $ch = ord($param[$i]);
                if (($ch >= 97 && $ch <= 122) || ($ch >= 65 && $ch <= 90)) {
                    $filtered_str .= chr($ch);
                }
            }
            return $filtered_str;
        }

        public static function _starts_with($str, $start, $ignoreCase = null)
        {
            return ($ignoreCase ? strcasecmp($start, substr($str, 0, strlen($start))) : strcmp($start, substr($str, 0, strlen($start)))) == 0;
        }

        public static function _ends_with($str, $end, $ignoreCase = null)
        {
            return ($ignoreCase ? strcasecmp($end, substr($str, - strlen($end))) : strcmp($end, substr($str, - strlen($end)))) == 0;
        }

        /**
         *
         * @param string $param            
         * @return string
         */
        public static function _is_basic_charset($param)
        {
            $len = strlen($param);
            $iso88591 = false;
            for ($i = 0; $i < $len; ++ $i) {
                $ch = ord($param[$i]);
                if ($ch == 172 || ($ch >= 128 && $ch <= 159)) {
                    continue;
                }
                if ($ch >= 160 && $ch <= 255) {
                    $iso88591 = true;
                    continue;
                }
                return false;
            }
            if (! $iso88591) {
                return 'ASCII';
            }
            return 'ISO-8859-1';
        }

        /**
         *
         * Escapes non-alphanumeric characters to \HH+space
         *
         * @param string $css            
         * @return string
         */
        public static function _escape_css($css)
        {
            return \Phalcon\Text::_escape_multi($scc, '\\', ' ', 0);
        }

        /**
         * Escapes non-alphanumeric characters to \xHH+\0
         *
         * @param string $js            
         * @return string
         */
        public static function _escape_js($js)
        {
            return \Phalcon\Text::_escape_multi($js, '\\x', '\0', 1);
        }

        /**
         * Perform escaping of non-alphanumeric characters to different formats
         *
         * @param string $param            
         * @param string $escape_char            
         * @param string $escape_extra            
         * @param boolean $use_whitelist            
         * @return string
         */
        public static function _escape_multi($param, $escape_char, $escape_extra, $use_whitelist)
        {
            $len = strlen($param);
            if ($len <= 0) {
                return '';
            }
            
            $ret = '';
            for ($i = 0; $i < $len; ++ $i) {
                $char = $param[$i];
                $ch = ord($char);
                if ($ch == 0) {
                    break;
                }
                if ($ch < 256 && (($ch >= 97 && $ch <= 122) || ($ch >= 65 && $ch <= 90))) {
                    $ret .= $char;
                    continue;
                }
                
                if ($use_whitelist) {
                    switch ($char) {
                        case ' ':
                        case '/':
                        case '*':
                        case '+':
                        case '-':
                        case '\t':
                        case '\n':
                        case '^':
                        case '$':
                        case '!':
                        case '?':
                        case '\\':
                        case '#':
                        case '}':
                        case '{':
                        case ')':
                        case '(':
                        case ']':
                        case '[':
                        case '.':
                        case ',':
                        case ':':
                        case ';':
                        case '_':
                        case '|':
                            $ret .= $char;
                            continue;
                    }
                }
                
                /**
                 * Convert character to hexadecimal
                 */
                $hex = bin2hex($char);
                
                /**
                 * Append the escaped character
                 */
                $ret .= $escape_char . $hex;
                if ($escape_extra != '\0') {
                    $ret .= $escape_extra;
                }
            }
            return $ret;
        }
    }
}
