<?php

namespace AceDatagrid\Helper;

use Laminas\View\Exception;
use Laminas\View\Helper\AbstractHelper;

class SortControl extends AbstractHelper
{
    /**
     * @param string $sort
     * @param string $name
     * @param string $label
     * @param bool $reverse
     * @param array $query
     * @throws \Laminas\View\Exception\RuntimeException
     * @return string
     */
    public function __invoke($sort, $name, $label = '', $reverse = false, $query = [])
    {
        if (!$name) {
            throw new Exception\RuntimeException('No column name provided');
        }

        $query['sort'] = (($sort == $name) || ((ltrim($sort, '-') != $name) && $reverse) ? '-' . $name : $name);

        $xhtml = '<a href="' . $this->view->url(null, [], ['query' => array_filter($query)], true) . '">' . $label;
        if (ltrim($sort, '-') == $name) {
            $xhtml .= ' <span class="fas fa-chevron-' . ($sort == $name ? 'down' : 'up') . '" aria-hidden="true"></span>';
        }
        $xhtml .= '</a>';

        return $xhtml;
    }
}
