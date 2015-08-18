<?php
namespace QuakeMap;

use \Math_Plane\Plane;

/**
 * Class Brush
 * One brush is one threedimensional object, described by a number of vectors
 *
 * @package QuakeMap
 */
class Brush
{
    /**
     * @var BrushFace[]
     */
    private $faceList = array();

    /**
     * Adds one face to the brush
     *
     * @param BrushFace $face
     */
    private function addFace(BrushFace $face)
    {
        $this->faceList[] = $face;
    }

    /**
     * @param \stdClass $rawPlane
     */
    public function addRawFace(\stdClass $rawPlane)
    {
        $face = new BrushFace();
        $face->initFromRaw($rawPlane);
        $this->addFace($face);
    }

    /**
     * convert brush defined by planes in $this->faceList into polygon
     * Create an array 'polygon' which has as many members as Faces
     */
    public function calculateVertexes()
    {
        for ($i = 0; $i < count($this->faceList) - 2; $i++) {
            for ($j = $i; $j < count($this->faceList)-1; $j++) {
                for ($k = $j; $k < count($this->faceList); $k++) {
                    if (!($i === $j && $i === $k)) {
                        try {
                            $intersection = $this->faceList[$i]->getPlane()->calculateIntersectionPointWithTwoPlanes($this->faceList[$j]->getPlane(), $this->faceList[$k]->getPlane());
                            $isIntersectionOutsideBrush = false;
                            foreach ($this->faceList as $face) {
                                $side = $face->getPlane()->calculateSideOfPointVector($intersection);
                                if (Plane::SIDE_FRONT === $side) {
                                    $isIntersectionOutsideBrush = true;
                                }
                            }
                            if (false === $isIntersectionOutsideBrush) {
                                $this->faceList[$i]->addVertex(clone $intersection);
                                $this->faceList[$j]->addVertex(clone $intersection);
                                $this->faceList[$k]->addVertex(clone $intersection);
                            }
                        }
                        catch(\InvalidArgumentException $e) {
                            //no intersection - do nothing
                        }
                    }
                }
            }
        }
        //sort vertexes for each fact in clockwise order
        foreach ($this->faceList as $face) {
            $face->sortVerticesClockwise();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = '{' . PHP_EOL;
        foreach ($this->faceList as $number => $face) {
            $output .= $face . PHP_EOL;
        }
        $output .= '}';
        
        return $output;
    }
}
