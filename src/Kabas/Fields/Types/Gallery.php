<?php

namespace Kabas\Fields\Types;

use \Kabas\App;
use \Kabas\Fields\Repeatable;

class Gallery extends Repeatable
{

    protected $multiple = true;

    /**
     * Returns options (not needed on this type)
     * @return array
     */
    protected function makeOptions($options)
    {
        return $options;
    }

    /**
     * Makes an array of images
     * @param  array $value
     * @return array
     */
    protected function parse($value)
    {
        $a = [];
        $class = App::fields()->getClass('image');
        if (is_array($value)) {
            foreach ($value as $i => $item) {
                $a[] = new $class($this->name . '_' . $i, $item, $this);
            }
        }
        return $a;
    }
}
