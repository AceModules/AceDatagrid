<?php

namespace AceDatagrid\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Title
{
    /**
     * @var string
     */
    public $singular;

    /**
     * @var string
     */
    public $plural;
}
