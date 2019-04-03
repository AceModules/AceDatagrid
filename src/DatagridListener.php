<?php

namespace Ace\Datagrid;

use Ace\Datagrid\Annotation\Header;
use Ace\Datagrid\Annotation\Search;
use Ace\Datagrid\Annotation\Suggest;
use Ace\Datagrid\Annotation\Title;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\Annotation\AbstractAnnotationsListener;

class DatagridListener extends AbstractAnnotationsListener
{
    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureColumn', [$this, 'handleHeaderAnnotation']);
        $this->listeners[] = $events->attach('configureColumn', [$this, 'handleSearchAnnotation']);
        $this->listeners[] = $events->attach('configureColumn', [$this, 'handleSuggestAnnotation']);
        $this->listeners[] = $events->attach('discoverTitle', [$this, 'handleTitleAnnotation']);
    }

    /**
     * @param EventInterface $e
     * @return void
     */
    public function handleHeaderAnnotation(EventInterface $e)
    {
        $annotation = $e->getParam('annotation');
        $method = $e->getParam('method');
        if (!$annotation instanceof Header || !$method instanceof \ReflectionMethod) {
            return;
        }

        $datagridSpec = $e->getParam('datagridSpec');
        if (!isset($datagridSpec['headerColumns'])) {
            $datagridSpec['headerColumns'] = [];
        }
        $datagridSpec['headerColumns'][] = [
            'label' => $annotation->label,
            'sortName' => $annotation->getSortName(),
            'sortColumns' => $annotation->sort,
            'sortReverse' => $annotation->reverse,
            'method' => $method->getName(),
        ];

        if ($annotation->default) {
            $datagridSpec['defaultSort'] = ($annotation->reverse ? '-' : '') . $annotation->getSortName();
        }
    }

    /**
     * @param EventInterface $e
     * @return void
     */
    public function handleSearchAnnotation(EventInterface $e)
    {
        $annotation = $e->getParam('annotation');
        $property = $e->getParam('property');
        if (!$annotation instanceof Search || !$property instanceof \ReflectionProperty) {
            return;
        }

        $datagridSpec = $e->getParam('datagridSpec');
        if (!isset($datagridSpec['searchColumns'])) {
            $datagridSpec['searchColumns'] = [];
        }
        $datagridSpec['searchColumns'][] = [
            'name' => ($annotation->columnName ? $annotation->columnName : $property->getName()),
            'minLength' => $annotation->minLength,
        ];
    }

    /**
     * @param EventInterface $e
     * @return void
     */
    public function handleSuggestAnnotation(EventInterface $e)
    {
        $annotation = $e->getParam('annotation');
        $property = $e->getParam('property');
        if (!$annotation instanceof Suggest || !$property instanceof \ReflectionProperty) {
            return;
        }

        $datagridSpec = $e->getParam('datagridSpec');
        if (!isset($datagridSpec['suggestColumns'])) {
            $datagridSpec['suggestColumns'] = [];
        }
        $datagridSpec['suggestColumns'][] = [
            'name' => ($annotation->columnName ? $annotation->columnName : $property->getName()),
            'minLength' => $annotation->minLength,
        ];
    }

    /**
     * @param EventInterface $e
     * @return void
     */
    public function handleTitleAnnotation(EventInterface $e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Title) {
            return;
        }

        $datagridSpec = $e->getParam('datagridSpec');
        $datagridSpec['title'] = [
            'singular' => $annotation->singular,
            'plural' => $annotation->plural,
        ];
    }
}