<?php

namespace AceDatagrid\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Header
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var array
     */
    public $sort = [];

    /**
     * @var bool
     */
    public $reverse = false;

    /**
     * @var bool
     */
    public $default = false;

    /**
     * @return string|null
     */
    public function getSortName()
    {
        if (!$this->sort) {
            return null;
        }

        $sortName = reset($this->sort);
        if (count($this->sort) == 1 && preg_match('/^[a-z]+$/i', $sortName)) {
            return $sortName;
        }

        return strtolower(preg_replace("/[^a-z0-9]+/i", "", $this->label));
    }
}
