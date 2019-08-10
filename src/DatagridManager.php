<?php

namespace AceDatagrid;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Stdlib\ArrayObject;

class DatagridManager implements EventManagerAwareInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var array
     */
    protected $datagrids = [];

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param EventManagerInterface $events
     * @return $this
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([__CLASS__, get_called_class()]);
        (new DatagridListener())->attach($events);
        $this->events = $events;
        return $this;
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * @param string $className
     * @return Datagrid
     */
    public function create($className)
    {
        $metadata = $this->entityManager->getClassMetadata($className);
        $reflection = $metadata->getReflectionClass();

        $datagridSpec = new ArrayObject([
            'className' => $className,
            'primaryKey' => $metadata->getSingleIdentifierFieldName(),
            'name' => [
                'singular' => '',
                'plural' => '',
            ],
            'defaultSort' => null,
            'headerColumns' => [],
            'searchColumns' => [],
            'suggestColumns' => [],
        ]);

        $reader = new AnnotationReader();
        foreach ($reader->getClassAnnotations($reflection) as $annotation) {
            $params = compact('datagridSpec', 'annotation');
            $this->getEventManager()->trigger('discoverTitle', $this, $params);
        }
        foreach ($reflection->getProperties() as $property) {
            foreach ($reader->getPropertyAnnotations($property) as $annotation) {
                $params = compact('datagridSpec', 'annotation', 'property');
                $this->getEventManager()->trigger('configureColumn', $this, $params);
            }
        }
        foreach ($reflection->getMethods() as $method) {
            foreach ($reader->getMethodAnnotations($method) as $annotation) {
                $params = compact('datagridSpec', 'annotation', 'method');
                $this->getEventManager()->trigger('configureColumn', $this, $params);
            }
        }

        $this->datagrids[$className] = new Datagrid($this->entityManager, $datagridSpec->getArrayCopy());
        return $this->datagrids[$className];
    }

    /**
     * @param $className
     * @return mixed
     */
    public function get($className)
    {
        if (!isset($this->datagrids[$className])) {
            return $this->create($className);
        }
        return $this->datagrids[$className];
    }
}