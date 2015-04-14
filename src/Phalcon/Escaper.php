<?php
namespace Phalcon
{

    use Phalcon\EscaperInterface;
    use Phalcon\Escaper\Exception;

    /**
     * Phalcon\Escaper
     *
     * Escapes different kinds of text securing them. By using this component you may
     * prevent XSS attacks.
     *
     * This component only works with UTF-8. The PREG extension needs to be compiled with UTF-8 support.
     *
     * <code>
     * $escaper = new \Phalcon\Escaper();
     * $escaped = $escaper->escapeCss("font-family: <Verdana>");
     * echo $escaped; // font\2D family\3A \20 \3C Verdana\3E
     * </code>
     */
    class Escaper implements \Phalcon\EscaperInterface
    {

        protected $_encoding = 'utf-8';

        protected $_htmlEscapeMap = null;

        protected $_htmlQuoteType = 3;

        /**
         * Sets the encoding to be used by the escaper
         *
         * <code>
         * $escaper->setEncoding('utf-8');
         * </code>
         *
         * @param
         *            string encoding
         */
        public function setEncoding($encoding)
        {
            $this->_encoding = $encoding;
        }

        /**
         * Returns the internal encoding used by the escaper
         *
         * @return string
         */
        public function getEncoding()
        {
            return $this->_encoding;
        }

        /**
         * Sets the HTML quoting type for htmlspecialchars
         *
         * <code>
         * $escaper->setHtmlQuoteType(ENT_XHTML);
         * </code>
         *
         * @param
         *            int quoteType
         */
        public function setHtmlQuoteType($quoteType)
        {
            $this->_htmlQuoteType = $quoteType;
        }

        /**
         * Detect the character encoding of a string to be handled by an encoder
         * Special-handling for chr(172) and chr(128) to chr(159) which fail to be detected by mb_detect_encoding()
         *
         * @param
         *            string str
         * @return string/null
         */
        public function detectEncoding($str)
        {
            /**
             * Check if charset is ASCII or ISO-8859-1
             */
            $charset = \Phalcon\Text::_is_basic_charset($str);
            if (is_string($charset)) {
                return $charset;
            }
            
            /**
             * Strict encoding detection with fallback to non-strict detection.
             * Check encoding
             */
            foreach (array(
                'UTF-32',
                'UTF-8',
                'ISO-8859-1',
                'ASCII'
            ) as $charset) {
                if (mb_detect_encoding($str, $charset, true)) {
                    return $charset;
                }
            }
            
            return mb_detect_encoding($str);
        }

        /**
         * Utility to normalize a string's encoding to UTF-32.
         *
         * @param
         *            string str
         * @return string
         */
        public function normalizeEncoding($str)
        {
            /**
             * Convert to UTF-32 (4 byte characters, regardless of actual number of bytes in
             * the character).
             */
            return mb_convert_encoding($str, 'UTF-32', $this->detectEncoding($str));
        }

        /**
         * Escapes a HTML string.
         * Internally uses htmlspecialchars
         *
         * @param
         *            string text
         * @return string
         */
        public function escapeHtml($text)
        {
            return htmlspecialchars($text, $this->_htmlQuoteType, $this->_encoding);
        }

        /**
         * Escapes a HTML attribute string
         *
         * @param
         *            string attribute
         * @return string
         */
        public function escapeHtmlAttr($attribute)
        {
            return htmlspecialchars($attribute, ENT_QUOTES, $this->_encoding);
        }

        /**
         * Escape CSS strings by replacing non-alphanumeric chars by their hexadecimal escaped representation
         *
         * @param
         *            string css
         * @return string
         */
        public function escapeCss($css)
        {
            return \Phalcon\Text::_escape_css($this->normalizeEncoding($css));
        }

        /**
         * Escape javascript strings by replacing non-alphanumeric chars by their hexadecimal escaped representation
         *
         * @param
         *            string js
         * @return string
         */
        public function escapeJs($js)
        {
            /**
             * Normalize encoding to UTF-32
             * Escape the string
             */
            return \Phalcon\Text::_escape_js($this->normalizeEncoding($js));
        }

        /**
         * Escapes a URL.
         * Internally uses rawurlencode
         *
         * @param
         *            string url
         * @return string
         */
        public function escapeUrl($url)
        {
            return rawurlencode($url);
        }
    }
}
