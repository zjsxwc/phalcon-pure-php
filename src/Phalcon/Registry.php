<?php
namespace Phalcon
{

    /**
     * Phalcon\Registry
     *
     * A registry is a container for storing objects and values in the application space.
     * By storing the value in a registry, the same object is always available throughout
     * your application.
     *
     * <code>
     * $registry = new \Phalcon\Registry();
     *
     * // Set value
     * $registry->something = 'something';
     * // or
     * $registry['something'] = 'something';
     *
     * // Get value
     * $value = $registry->something;
     * // or
     * $value = $registry['something'];
     *
     * // Check if the key exists
     * $exists = isset($registry->something);
     * // or
     * $exists = isset($registry['something']);
     *
     * // Unset
     * unset($registry->something);
     * // or
     * unset($registry['something']);
     * </code>
     *
     * In addition to ArrayAccess, Phalcon\Registry also implements Countable
     * (count($registry) will return the number of elements in the registry),
     * Serializable and Iterator (you can iterate over the registry
     * using a foreach loop) interfaces. For PHP 5.4 and higher, JsonSerializable
     * interface is implemented.
     *
     * Phalcon\Registry is very fast (it is typically faster than any userspace
     * implementation of the registry); however, this comes at a price:
     * Phalcon\Registry is a final class and cannot be inherited from.
     *
     * Though Phalcon\Registry exposes methods like __get(), offsetGet(), count() etc,
     * it is not recommended to invoke them manually (these methods exist mainly to
     * match the interfaces the registry implements): $registry->__get('property')
     * is several times slower than $registry->property.
     *
     * Internally all the magic methods (and interfaces except JsonSerializable)
     * are implemented using object handlers or similar techniques: this allows
     * to bypass relatively slow method calls.
     */
    final class Registry implements \ArrayAccess, \Countable, \Iterator, \Traversable
    {

        protected $_data;

        /**
         * Registry constructor
         */
        final public function __construct()
        {
            $this->_data = array();
        }

        /**
         * Checks if the element is present in the registry
         *
         * @param
         *            string offset
         */
        final public function offsetExists($offset)
        {
            return isset($this->_data[$offset]);
        }

        /**
         * Returns an index in the registry
         *
         * @param
         *            string offset
         */
        final public function offsetGet($offset)
        {
            return $this->_data[$offset];
        }

        /**
         * Sets an element in the registry
         *
         * @param
         *            string offset
         * @param
         *            mixed value
         */
        final public function offsetSet($offset, $value)
        {
            return $this->_data[$offset] = $value;
        }

        /**
         * Unsets an element in the registry
         *
         * @param
         *            string offset
         */
        final public function offsetUnset($offset)
        {
            unset($this->_data[$offset]);
        }

        /**
         * Sets an element in the registry
         *
         * @param
         *            string offset
         * @param
         *            mixed value
         */
        final public function __set($offset, $value)
        {
            return $this->_data[$offset] = $value;
        }

        /**
         * Returns an index in the registry
         *
         * @param
         *            string offset
         */
        final public function __get($offset)
        {
            return $this->_data[$offset];
        }

        /**
         * Checks how many elements are in the register
         *
         * @return int
         */
        final public function count()
        {
            return count($this->_data);
        }

        /**
         * Moves cursor to next row in the registry
         */
        final public function next()
        {
            next($this->_data);
        }

        /**
         * Gets pointer number of active row in the registry
         *
         * @return int
         */
        final public function key()
        {
            return key($this->_data);
        }

        /**
         * Rewinds the registry cursor to its beginning
         */
        final public function rewind()
        {
            reset($this->_data);
        }

        public function valid()
        {
            return key($this->_data) !== null;
        }

        public function current()
        {
            return current($this->_data);
        }
    }
}
