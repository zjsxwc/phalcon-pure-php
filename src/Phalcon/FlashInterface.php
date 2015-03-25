<?php
namespace Phalcon
{

    /**
     * Phalcon\FlashInterface
     *
     * Interface for Phalcon\Flash
     */
    interface FlashInterface
    {
        /**
         * Show a HTML error message
         * 
         * @param string message
         * @return string
         */
        public function error($message);

        /**
         * Show a HTML notice/information message
         * 
         * @param string message
         * @return string
         */
        public function notice($message);

        /**
         * Show a HTML success message
         * 
         * @param string message
         * @return string
         */
        public function success($message);

        /**
         * Show a HTML warning message
         * 
         * @param string message
         * @return string
         */
        public function warning($message);

        /**
         * Output a message
         * 
         * @param string type
         * @param string message
         * @return string
         */
        public function message($type, $message);
    }
}
