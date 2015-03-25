<?php
namespace Phalcon
{

    /**
     * Phalcon\Filter
     *
     * The Phalcon\Filter component provides a set of commonly needed data filters. It provides
     * object oriented wrappers to the php filter extension. Also allows the developer to
     * define his/her own filters
     *
     * <code>
     * $filter = new \Phalcon\Filter();
     * $filter->sanitize("some(one)@exa\\mple.com", "email"); // returns "someone@example.com"
     * $filter->sanitize("hello<<", "string"); // returns "hello"
     * $filter->sanitize("!100a019", "int"); // returns "100019"
     * $filter->sanitize("!100a019.01a", "float"); // returns "100019.01"
     * </code>
     */
    class Filter implements \Phalcon\FilterInterface
    {

        protected $_filters = array();

        /**
         * Adds a user-defined filter
         *
         * @param
         *            string name
         * @param
         *            callable handler
         * @return \Phalcon\Filter
         */
        public function add($name, $handler)
        {
            $this->_filters[$name] = $handler;
            return $this;
        }

        /**
         * Sanitizes a value with a specified single or set of filters
         *
         * @param
         *            value
         * @param
         *            array filters
         * @param
         *            boolean noRecursive
         * @return mixed
         */
        public function sanitize($value, $filters, $noRecursive = null)
        {
            $filters = is_array($filters) ? $filters : array(
                $filters
            );
            if ($value !== null) {
                foreach ($filters as $filter) {
                    /**
                     * If the value to filter is an array we apply the filters recursively
                     */
                    if (is_array($value) && ! $noRecursive) {
                        $arrayValue = array();
                        foreach ($value as $itemKey => $itemValue) {
                            $arrayValue[$itemKey] = $this->_sanitize($itemValue, $filter);
                        }
                        $value = $arrayValue;
                    } else {
                        $value = $this->_sanitize($value, $filter);
                    }
                }
            }
            
            return $value;
        }

        /**
         * Internal sanitize wrapper to filter_var
         *
         * @param mixed $value            
         * @param string $filter            
         * @return mixed
         */
        protected function _sanitize($value, $filter)
        {
            if (isset($this->_filters[$filter])) {
                $filterObject = $this->_filters[$filter];
                /**
                 * If the filter is a closure we call it in the PHP userland
                 */
                if ($filterObject instanceof \Closure) {
                    return call_user_func_array($filterObject, array(
                        $value
                    ));
                }
                
                return $filterObject->filter($value);
            }
            
            switch ($filter) {
                case 'email':
                    /**
                     * The 'email' filter uses the filter extension
                     */
                    return filter_var(str_replace('\'', '', $value), FILTER_SANITIZE_EMAIL);
                
                case 'int':
                    /**
                     * 'int' filter sanitizes a numeric input
                     */
                    return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                
                case 'int!':
                    return intval($value);
                
                case 'string':
                    return filter_var($value, FILTER_SANITIZE_STRING);
                
                case 'float':
                    /**
                     * The 'float' filter uses the filter extension
                     */
                    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, array(
                        'flags' => FILTER_FLAG_ALLOW_FRACTION
                    ));
                
                case 'float!':
                    return doubleval($value);
                
                case 'alphanum':
                    return preg_replace('/[^A-Za-z0-9]/', '', $value);
                
                case 'trim':
                    return trim(value);
                
                case 'striptags':
                    return strip_tags(value);
                
                case 'lower':
                    if (function_exists('mb_strtolower')) {
                        /**
                         * 'lower' checks for the mbstring extension to make a correct lowercase transformation
                         */
                        return mb_strtolower(value);
                    }
                    return strtolower(value);
                
                case 'upper':
                    if (function_exists('mb_strtoupper')) {
                        /**
                         * 'upper' checks for the mbstring extension to make a correct lowercase transformation
                         */
                        return mb_strtoupper(value);
                    }
                    return strtoupper(value);
                
                default:
                    throw new Exception('Sanitize filter \'' . $filter . '\' is not supported');
            }
        }

        /**
         * Return the user-defined filters in the instance
         *
         * @return object[]
         */
        public function getFilters()
        {
            return $this->_filters;
        }
    }
}
