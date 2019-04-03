<?php

namespace Ace\Datagrid;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\QueryBuilder;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayObject;

class DatagridManager implements EventManagerAwareInterface, ServiceLocatorAwareInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var ServiceLocatorInterface
     */
    protected $services;

    /**
     * @var array
     */
    protected $datagrids = [];

    public function __construct()
    {
        $this->getEventManager()->attach(new DatagridListener());
    }

    /**
     * @param EventManagerInterface $events
     * @return $this
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([__CLASS__, get_called_class()]);
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
     * @param ServiceLocatorInterface $services
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }

    /**
     * @param string $className
     * @return Datagrid
     */
    public function create($className)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $metadata = $entityManager->getClassMetadata($className);
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

        $this->datagrids[$className] = new Datagrid($entityManager, $datagridSpec->getArrayCopy());
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