<?php
namespace QuakeMap;

/**
 * Class QuakeMap
 * Represents one quake map.
 * One map consists of a collection of entities
 *
 * @package QuakeMap
 */
class QuakeMap
{
    /**
     * Internal entity list
     *
     * @var Entity[]
     */
    private $entityList = array();

    /**
     * Adds a new entity to the maps entity list
     *
     * @param Entity $entity
     */
    public function addEntity(Entity $entity) {
        $this->entityList[] = $entity;
    }

    /**
     * Loads and parses map files
     * stores the data in internal entityList property
     *
     * @param $fileName
     * @return QuakeMap
     */
    public function load($fileName) {
        $parser = new FileParser($this);
        $parser->load($fileName);
    }

    /**
     * Writes the map data to file
     *
     * @param $fileName
     */
    public function save($fileName)
    {
        $handle = fopen($fileName, 'w');
        if (false === $handle) {
            throw new \RuntimeException(sprintf('Could not open %s for writing.', $fileName));
        }
        foreach ($this->entityList as $number => $entity) {
            fwrite($handle, '// entity ' . $number . PHP_EOL);
            fwrite($handle, (string) $entity . PHP_EOL);
        }
    }

}
