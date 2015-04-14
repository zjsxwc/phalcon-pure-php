<?php
namespace Phalcon
{

    use Phalcon\Debug\Exception;

    /**
     * Phalcon\Debug
     *
     * Provides debug capabilities to Phalcon applications
     */
    class Debug
    {

        public $_uri = 'http://static.phalconphp.com/debug/1.2.0/';

        public $_theme = 'default';

        protected $_hideDocumentRoot = 'false';

        protected $_showBackTrace = true;

        protected $_showFiles = true;

        protected $_showFileFragment = false;

        protected $_data;

        protected static $_isActive;

        /**
         * Change the base URI for static resources
         *
         * @param
         *            string uri
         * @return \Phalcon\Debug
         */
        public function setUri($uri)
        {
            $this->_uri = $uri;
            return $this;
        }

        /**
         * Sets if files the exception"s backtrace must be showed
         *
         * @param
         *            boolean showBackTrace
         * @return \Phalcon\Debug
         */
        public function setShowBackTrace($showBackTrace)
        {
            $this->_showBackTrace = $showBackTrace;
            return $this;
        }

        /**
         * Set if files part of the backtrace must be shown in the output
         *
         * @param
         *            boolean showFiles
         * @return \Phalcon\Debug
         */
        public function setShowFiles($showFiles)
        {
            $this->_showFiles = $showFiles;
            return $this;
        }

        /**
         * Sets if files must be completely opened and showed in the output
         * or just the fragment related to the exception
         *
         * @param
         *            boolean showFileFragment
         * @return \Phalcon\Debug
         */
        public function setShowFileFragment($showFileFragment)
        {
            $this->_showFileFragment = $showFileFragment;
            return $this;
        }

        /**
         * Listen for uncaught exceptions and unsilent notices or warnings
         *
         * @param
         *            boolean exceptions
         * @param
         *            boolean lowSeverity
         * @return \Phalcon\Debug
         */
        public function listen($exceptions = null, $lowSeverity = null)
        {
            if ($exceptions) {
                $this->listenExceptions();
            }
            
            if ($lowSeverity) {
                $this->listenLowSeverity();
            }
            return $this;
        }

        /**
         * Listen for uncaught exceptions
         *
         * @return \Phalcon\Debug
         */
        public function listenExceptions()
        {
            set_exception_handler(array(
                $this,
                'onUncaughtException'
            ));
            return $this;
        }

        /**
         * Listen for unsilent notices or warnings
         *
         * @return \Phalcon\Debug
         */
        public function listenLowSeverity()
        {
            set_exception_handler(array(
                $this,
                'onUncaughtLowSeverity'
            ));
            return $this;
        }

        /**
         * Halts the request showing a backtrace
         */
        public function halt()
        {
            throw new Exception('Halted request');
        }

        /**
         * Adds a variable to the debug output
         *
         * @param
         *            mixed varz
         * @param
         *            string key
         * @return \Phalcon\Debug
         */
        public function debugVar($varz, $key = null)
        {
            $this->_data[] = array(
                $varz,
                debug_backtrace(),
                time()
            );
            return $this;
        }

        /**
         * Clears are variables added previously
         *
         * @return \Phalcon\Debug
         */
        public function clearVars()
        {
            $this->_data = null;
            return $this;
        }

        /**
         * Escapes a string with htmlentities
         *
         * @param
         *            string value
         * @return string
         */
        protected function _escapeString($value)
        {
            if (is_string($value)) {
                return htmlentities(str_replace('\n', '\\n', $value), ENT_COMPAT, 'utf-8');
            }
            return $value;
        }

        /**
         * Produces a recursive representation of an array
         *
         * @param
         *            array argument
         * @return string
         */
        protected function _getArrayDump($argument, $n = 0)
        {
            $numberArguments = count($argument);
            if (n < 3) {
                if ($numberArguments > 0) {
                    if ($numberArguments < 10) {
                        $dump = array();
                        foreach ($argument as $k => $v) {
                            if (is_scalar($v)) {
                                if ($v == '') {
                                    $varDump = '[' . $k . '] =&gt; (empty string)';
                                } else {
                                    $varDump = '[' . $k . '] =&gt; ' . $this->_escapeString($v);
                                }
                                $dump[] = $varDump;
                            } else {
                                if (is_array($v)) {
                                    $dump[] = '[' . $k . '] =&gt; Array(' . $this->_getArrayDump($v, $n + 1) . ')';
                                    continue;
                                }
                                
                                if (is_object($v)) {
                                    $dump[] = '[' . $k . '] =&gt; Object(' . get_class($v) . ')';
                                    continue;
                                }
                                
                                if ($v === null) {
                                    $dump[] = '[' . $k . '] =&gt; null';
                                    continue;
                                }
                                
                                $dump[] = '[' . $k . '] &gt; ' . $v;
                            }
                        }
                        
                        return join(', ', $dump);
                    }
                    return $numberArguments;
                }
            }
            return null;
        }

        /**
         * Produces an string representation of a variable
         *
         * @param
         *            mixed variable
         * @return string
         */
        protected function _getVarDump($variable)
        {
            if (is_scalar($variable)) {
                /**
                 * Boolean variables are represented as "true"/"false"
                 */
                if (is_bool($variable)) {
                    return $variable ? 'true' : 'false';
                }
                
                /**
                 * String variables are escaped to avoid XSS injections
                 */
                if (is_string($variable)) {
                    return $this->_escapeString($variable);
                }
                
                /**
                 * Other scalar variables are just converted to strings
                 */
                return $variable;
            }
            
            /**
             * If the variable is an object print its class name
             */
            if (is_object($variable)) {
                $className = get_class($variable);
                
                /**
                 * Try to check for a "dump" method, this surely produces a better printable representation
                 */
                if (method_exists($variable, 'dump')) {
                    $dumpedObject = $variable->dump();
                    
                    /**
                     * dump() must return an array, generate a recursive representation using getArrayDump
                     */
                    $dump = 'Object(' . $className . ': ' . $this->_getArrayDump($dumpedObject) . ')';
                } else {
                    /**
                     * If dump() is not available just print the class name
                     */
                    $dump = 'Object(' . $className . ')';
                }
                return $dump;
            }
            
            /**
             * Recursively process the array and enclose it in []
             */
            if (is_array($variable)) {
                return 'Array(' . $this->_getArrayDump($variable) . ')';
            }
            
            /**
             * Null variables are represented as "null"
             */
            if ($variable === null) {
                return 'null';
            }
            
            /**
             * Other types are represented by its type
             */
            return gettype($variable);
        }

        /**
         * Returns the major framework's version
         *
         * @return string
         */
        public function getMajorVersion()
        {
            $parts = explode(' ', \Phalcon\Version::get());
            return $parts[0];
        }

        /**
         * Generates a link to the current version documentation
         *
         * @return string
         */
        public function getVersion()
        {
            return '<div class="version">Phalcon Framework <a target="_new" href="http://docs.phalconphp.com/en/' . $this->getMajorVersion() . '/">' . \Phalcon\Version::get() . '</a></div>';
        }

        /**
         * Returns the css sources
         *
         * @return string
         */
        public function getCssSources()
        {
            $uri = $this->_uri;
            $sources = '<link href="' . $uri . 'jquery/jquery-ui.css" type="text/css" rel="stylesheet" />';
            $sources .= '<link href="' . $uri . 'themes/default/style.css" type="text/css" rel="stylesheet" />';
            return $sources;
        }

        /**
         * Returns the javascript sources
         *
         * @return string
         */
        public function getJsSources()
        {
            $uri = $this->_uri;
            
            $sources = '<script type="text/javascript" src="' . $uri . 'jquery/jquery.js"></script>';
            $sources .= '<script type="text/javascript" src="' . $uri . 'jquery/jquery-ui.js"></script>';
            $sources .= '<script type="text/javascript" src="' . $uri . 'jquery/jquery.jquery.scrollTo.js"></script>';
            $sources .= '<script type="text/javascript" src="' . $uri . 'pretty.js"></script>';
            return $sources;
        }

        /**
         * Shows a backtrace item
         *
         * @param
         *            int n
         * @param
         *            array trace
         */
        final protected function showTraceItem($n, $trace)
        {
            $space = ' ';
            $towSpaces = '  ';
            $underscore = '_';
            $minus = '-';
            
            /**
             * Every trace in the backtrace have a unique number
             */
            $html = '<tr><td align="right" valign="top" class="error-number">#' . $n . '</td><td>';
            
            if (isset($trace['class'])) {
                $className = $trace['class'];
                
                /**
                 * We assume that classes starting by Phalcon are framework"s classes
                 */
                if (preg_match('/^Phalcon/', $className)) {
                    $namespaceSeparator = '\\';
                    
                    /**
                     * Prepare the class name according to the Phalcon"s conventions
                     */
                    $prepareUriClass = str_replace($namespaceSeparator, $underscore, $className);
                    
                    /**
                     * Generate a link to the official docs
                     */
                    $html .= '<span class="error-class"><a target="_new" href="http://docs.phalconphp/en/latest/api/' . $prepareUriClass . '.html">' . $className . '</a></span>';
                } else {
                    $classReflection = new \ReflectionClass($className);
                    
                    /**
                     * Check if classes are PHP"s classes
                     */
                    if ($classReflection->isInternal()) {
                        $prepareInternalClass = str_replace($underscore, $minus, strtolower($className));
                        
                        /**
                         * Generate a link to the official docs
                         */
                        $html .= '<span class="error-class"><a target="_new" href="http://php.net/manual/en/class.' . $prepareInternalClass . '.php">' . $className . '</a></span>';
                    } else {
                        $html .= '<span class="error-class">' . $className . '</span>';
                    }
                }
                
                /**
                 * Object access operator: static/instance
                 */
                $html .= $trace['type'];
            }
        }

        /**
         * Handles uncaught exceptions
         *
         * @param
         *            \Exception exception
         * @return boolean
         */
        public function onUncaughtException($exception)
        {}
    }
}
