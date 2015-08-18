<?php
namespace QuakeMap;

/**
 * Class Entity
 * One entity consists of a number of attributes and brushes (physical representations in 3d space)
 *
 * @package QuakeMap
 */
class Entity
{
    /**
     * @var array
     */
    private $attributeList = array();

    /**
     * @var Brush[]
     */
    private $brushList = array();

    /**
     * @param $name
     * @param $value
     */
    public function addAttribute($name, $value)
    {
        $this->attributeList[ $name ] = $value;
    }

    /**
     * Serves the array key of the last(=current)brush in the list
     *
     * @return int
     * @throws \RuntimeException
     */
    private function getCurrentBrush()
    {
        $amount = count($this->brushList);
        if ($amount === 0) {
            throw new \RuntimeException('trying to get non existent brush');
        }
        return $amount - 1;
    }

    /**
     * @param Brush $brush
     */
    private function addBrush(Brush $brush)
    {
        $this->brushList[] = $brush;
    }

    /**
     * Add a new (empty) brush to the entity. Entities can have multiple brushes.
     */
    public function newBrush()
    {
        $brush = new Brush();
        $this->addBrush($brush);
    }

    /**
     * Commit some calculations within the brush which can only be done after it is
     * wholly defined
     */
    public function finishBrush()
    {
        $this->brushList[$this->getCurrentBrush()]->calculateVertexes();
    }

    /**
     * @param \stdClass $rawPlane
     * @throws \RuntimeException
     */
    public function addBrushPlane(\stdClass $rawPlane)
    {
        $lastBrush = $this->getCurrentBrush();
        if (!isset($this->brushList[$lastBrush])) {
            throw new \RuntimeException('trying to add brush plane without initializing any brush');
        }

        $this->brushList[$lastBrush]->addRawFace($rawPlane);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = '{' . PHP_EOL;
        foreach ($this->attributeList as $name => $value) {
            $output .= sprintf('"%s" "%s"' . PHP_EOL, $name, $value);
        }
        foreach ($this->brushList as $number => $brush) {
            $output .= '// brush ' . $number . PHP_EOL;
            $output .= $brush . PHP_EOL;
        }
        $output .= '}';
        
        return $output;
    }
}
