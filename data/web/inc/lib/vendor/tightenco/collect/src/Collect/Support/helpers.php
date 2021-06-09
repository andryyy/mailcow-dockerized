<?php

use Tightenco\Collect\Support\Arr;
use Tightenco\Collect\Support\Collection;
use Tightenco\Collect\Support\HigherOrderTapProxy;
use Symfony\Component\VarDumper\VarDumper;

if (! class_exists(Illuminate\Support\Collection::class)) {
    if (! function_exists('array_wrap')) {
        /**
         * If the given value is not an array, wrap it in one.
         *
         * @param  mixed  $value
         * @return array
         */
        function array_wrap($value)
        {
            return ! is_array($value) ? [$value] : $value;
        }
    }

    if (! function_exists('collect')) {
        /**
         * Create a collection from the given value.
         *
         * @param  mixed  $value
         * @return \Tightenco\Collect\Support\Collection
         */
        function collect($value = null)
        {
            return new Collection($value);
        }
    }

    if (! function_exists('value')) {
        /**
         * Return the default value of the given value.
         *
         * @param  mixed  $value
         * @return mixed
         */
        function value($value)
        {
            return $value instanceof Closure ? $value() : $value;
        }
    }

    if (! function_exists('data_get')) {
        /**
         * Get an item from an array or object using "dot" notation.
         *
         * @param  mixed   $target
         * @param  string|array  $key
         * @param  mixed   $default
         * @return mixed
         */
        function data_get($target, $key, $default = null)
        {
            if (is_null($key)) {
                return $target;
            }

            $key = is_array($key) ? $key : explode('.', $key);

            while (($segment = array_shift($key)) !== null) {
                if ($segment === '*') {
                    if ($target instanceof Collection) {
                        $target = $target->all();
                    } elseif (! is_array($target)) {
                        return value($default);
                    }

                    $result = Arr::pluck($target, $key);

                    return in_array('*', $key) ? Arr::collapse($result) : $result;
                }

                if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                    $target = $target[$segment];
                } elseif (is_object($target) && isset($target->{$segment})) {
                    $target = $target->{$segment};
                } else {
                    return value($default);
                }
            }

            return $target;
        }
    }

    if (! function_exists('tap')) {
        /**
         * Call the given Closure with the given value then return the value.
         *
         * @param  mixed  $value
         * @param  callable|null  $callback
         * @return mixed
         */
        function tap($value, $callback = null)
        {
            if (is_null($callback)) {
                return new HigherOrderTapProxy($value);
            }

            $callback($value);

            return $value;
        }
    }

    if (! function_exists('with')) {
        /**
         * Return the given object. Useful for chaining.
         *
         * @param  mixed  $object
         * @return mixed
         */
        function with($object)
        {
            return $object;
        }
    }

    if (! function_exists('dd')) {
        /**
         * Dump the passed variables and end the script.
         *
         * @param  mixed
         * @return void
         */
        function dd(...$args)
        {
            foreach ($args as $x) {
               VarDumper::dump($x);
            }
            die(1);
        }
    }
}
