<?php
namespace AKlump\Data;


class Data implements DataInterface
{
    /**
     * Extend this class and alter the default value of $pathSeparator if want
     * to use a different separator for your path strings.
     *
     * @var string
     */
    protected $pathSeparator = '.';

    /**
     * May be used to track internal data for recursive processing.
     *
     * @var array
     */
    protected $cache = array(
        'set'      => array(),
        'get'      => array(),
        'validate' => array(),
    );

    /**
     * @inheritdoc
     */
    public function get($subject, $path, $defaultValue = null, $valueCallback = null)
    {
        $this->cacheSet(__FUNCTION__, $subject, $path, $defaultValue, $valueCallback);

        if (empty($subject)) {
            return $this->postGet($defaultValue, $defaultValue, $valueCallback);
        }

        $this->validate($subject, $path, $defaultValue);
        $key = array_shift($path);
        $base = $subject;

        if (is_array($base)) {
            $base = isset($base[$key]) ? $base[$key] : $defaultValue;
        }
        elseif (is_object($base)) {
            $base = clone $base;
            if (isset($base->{$key})) {
                $base = $base->{$key};
            }
            else {
                $processed = false;
                foreach ($this->childGetters() as $method => $callback) {
                    if (method_exists($base, $method)) {
                        $base = $callback($base, $key, $defaultValue);
                        $processed = true;
                        break;
                    }
                }
                $base = $processed ? $base : $defaultValue;
            }
        }
        if (count($path) === 0) {
            return $this->postGet($base, $defaultValue, $valueCallback);
        }
        $function = __FUNCTION__;

        return $this->{$function}($base, implode($this->pathSeparator, $path), $defaultValue, $valueCallback);
    }

    /**
     * @inheritdoc
     */
    public function set(&$subject, $path, $value, $childTemplate = null)
    {
        $this->cacheSet(__FUNCTION__, $subject, $path, $value, $childTemplate);
        $this->validate($subject, $path);

        // Establish the childTemplate on first pass.
        if (is_null($childTemplate)) {
            if (is_array($subject)) {
                $childTemplate = array();
            }
            elseif (is_object($subject)) {
                $childTemplate = get_class($subject);
                $childTemplate = new $childTemplate;
            }
        }

        $childTemplate = is_object($childTemplate) ? clone $childTemplate : $childTemplate;

        $key = array_shift($path);
        if (is_array($subject)) {
            $subject[$key] = isset($subject[$key]) ? $subject[$key] : $childTemplate;
            $next = &$subject[$key];
        }
        elseif (is_object($subject)) {
            $subject->{$key} = isset($subject->{$key}) ? $subject->{$key} : $childTemplate;
            $next = &$subject->{$key};
        }

        if (empty($path)) {
            $next = $value;

            $this->cacheClear('set', 'validate');

            return $this;
        }

        return $this->set($next, $path, $value, $childTemplate);
    }

    /**
     * @inheritdoc
     */
    public function ensure(&$subject, $path, $default, $childTemplate = null)
    {
        $value = $this->get($subject, $path, $default);
        $this->set($subject, $path, $value, $childTemplate);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fill(&$subject, $path, $value, $test = null, $childTemplate = null)
    {
        // Figure out what an empty value is based on type of $value.
        $type = gettype($value);
        $empty = null;
        settype($empty, $type);

        // Our default test is based on variable type.
        $test = is_null($test) ? 'empty' : $test;

        // Basic language constructs
        if (is_string($test)) {
            switch ($test) {
                case 'empty':
                    $test = function ($current) {
                        return empty($current);
                    };
                    break;
                case 'is_null':
                    $test = function ($current) {
                        return is_null($current);
                    };
                    break;
                // empty plus type comparison
                case 'strict':
                    $test = function ($current) use ($empty) {
                        return $current === $empty;
                    };
                    break;
                // Custom 'array_key_exists', 'property_exists'
                case 'not_exists':
                    $test = function ($current, $exists, $value) {
                        return !$exists;
                    };
                    break;
            }
        }

        if (!is_callable($test)) {
            throw new \InvalidArgumentException("\$test must be a callable, null or predefined string.");
        }

        $ancestry = $path;
        $key = $this->pathPop($ancestry);
        if (empty($ancestry)) {
            $parent = $subject;
        }
        else {
            $type = gettype($subject);
            $empty_subject = null;
            settype($empty_subject, $type);
            $parent = $this->get($subject, $ancestry, $empty_subject);
        }

        // Determine if the path exists or not.
        $exists = false;
        if (is_array($parent) && array_key_exists($key, $parent)) {
            $exists = true;
            $current = $parent[$key];
        }
        elseif (is_object($parent) && property_exists($parent, $key)) {
            $exists = true;
            $current = $parent->{$key};
        }
        else {
            $current = $this->get($subject, $path, $empty);
        }

        if ($test($current, $exists, $value)) {
            $this->set($subject, $path, $value, $childTemplate);
        }

        return $this;
    }

    /**
     * Removes $count elements from $path, right to left
     *
     * @param string|array &$path
     *
     * @return mixed
     */
    protected function pathPop(&$path)
    {
        $type = gettype($path);
        $this->validate(__FUNCTION__, $path);
        $return = array_pop($path);
        $path = $type === 'string' ? implode($this->pathSeparator, $path) : $path;

        return $return;
    }

    protected function cacheSet($op)
    {
        $args = func_get_args();
        array_shift($args);
        $this->cache[$op]['args'] = isset($this->cache[$op]['args']) ? $this->cache[$op]['args'] : $args;
        $this->cache[$op]['level'] = isset($this->cache[$op]['level']) ? ++$this->cache[$op]['level'] : 0;
    }

    /**
     * Post process the get method.
     *
     * @param mixed    $value The discovered value, or default.
     * @param mixed    $defaultValue
     * @param Callable $valueCallback
     *
     * @return mixed
     */
    protected function postGet($value, $defaultValue, $valueCallback)
    {
        if (is_callable($valueCallback)) {
            $value = $valueCallback($value, $defaultValue);
        }
        $this->cacheClear('get', 'validate');

        return $value;
    }

    /**
     * Clear internal caches by key.
     *
     * @param... a list of keys to clear.
     *
     * @return $this
     */
    protected function cacheClear()
    {
        $keys = func_get_args();
        foreach ($keys as $key) {
            unset($this->cache[$key]);
        }

        return $this;
    }

    protected function validate($subject, &$path)
    {
        $this->cacheSet(__FUNCTION__, $subject, $path);
        if (empty($this->cache['validate']['validated'])) {
            // Convert ints/floats to a string, if possible
            $path = is_numeric($path) && strval($path) == $path ? strval($path) : $path;

            // Explode strings
            $path = is_string($path) ? explode($this->pathSeparator, $path) : $path;
            if (!is_array($path)) {
                throw new \InvalidArgumentException("\$path must be an array of $this->pathSeparator separated string.");
            }
        }

        return $this;
    }

    /**
     * Return an array of possible methods to call on objects for getting
     * property values.
     *
     * Extend this method as needed if you have child classes using
     * methods other than 'get' for extracting a propery value.
     *
     * By returning an array of methods, it allows this class to support
     * non-homomogenous objects under a commone banner.  The first matched
     * method on the child will be used, subsequent methods will not be called.
     *
     * @return array
     *   - Keys are the methods to call on the child class
     *   - Values are callbacks which need to return a value, arguments
     *   received are $object, $property, $default.
     */
    protected function childGetters()
    {
        return array(
            'get' => function ($childObject, $property, $defaultValue) {
                return $childObject->get($property, $defaultValue);
            },
        );
    }
}
