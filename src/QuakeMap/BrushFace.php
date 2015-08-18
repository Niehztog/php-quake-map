<?php
namespace QuakeMap;

use \Math_Plane\Plane;

/**
 * A face consists of a plane (three 3D-vecotrs = 9 Numbers)
 * + a surface texture
 * + surface properties
 * 
 * @author nilsgotzhein
 *
 */
class BrushFace
{
    const PARSE_REGEXP_BRUSH_PLANE = '=^\( ([\-]?[0-9]+) ([\-]?[0-9]+) ([\-]?[0-9]+) \) \( ([\-]?[0-9]+) ([\-]?[0-9]+) ([\-]?[0-9]+) \) \( ([\-]?[0-9]+) ([\-]?[0-9]+) ([\-]?[0-9]+) \) ([a-zA-Z0-9/_+]+) ([\-]?[0-9]+) ([\-]?[0-9]+) ([\-]?[0-9]+) ([\-]?[0-9\.]+) ([\-]?[0-9\.]+)(?: ([\-]?[0-9]+) ([\-]?[0-9]+) ([\-]?[0-9]+))?$=';
    //texture: <x_offset> <y_offset> <rotation> <x_scale> <y_scale> <content_flags> <surface_flags> <value>

    /**
     * @var Plane
     */
    private $plane = null;

    /**
     * @var \Math_Vector3[]
     */
    private $vertexes = array();

    /**
     * @var string
     */
    private $texture = null;

    /**
     * @var array
     */
    private $properties = array();
    
    /**
     * Adds one new vertex(position vector), limiting and hence defining the face
     *
     * @param \Math_Vector3 $vertexNew
     * @throws \Math_Vector_Exception
     */
    public function addVertex(\Math_Vector3 $vertexNew)
    {
        foreach ($this->vertexes as $vertexCurrent) {
            $diff = \Math_VectorOp::substract($vertexNew, $vertexCurrent);
            $len = $diff->length();
            if (abs($len) < Plane::EPSILON_DISTANCE) {
                return;
            }
        }
        $this->vertexes[] = $vertexNew;
    }

    /**
     * Calculates the point vector which is positioned exactly in the center of the face
     * this is accomplished by calculating the average of all vertex-pointvectors
     */
    private function calculateCenter()
    {
        $center = new \Math_Vector3(array(0, 0, 0));
        foreach ($this->vertexes as $vertex) {
            $center = new \Math_Vector3(\Math_VectorOp::add($center, $vertex)->getTuple());
        }
        $center = new \Math_Vector3(\Math_VectorOp::scale(1 / count($this->vertexes), $center)->getTuple());
        return $center;
    }

    /**
     * @param \Math_Vector3 $v1
     * @param \Math_Vector3 $v2
     * @param \Math_Vector3 $v3
     */
    private function setPlaneRaw(\Math_Vector3 $v1, \Math_Vector3 $v2, \Math_Vector3 $v3)
    {
        $this->plane = Plane::getInstanceByThreePositionVectors($v1, $v2, $v3);
    }

    /**
     * @return Plane
     */
    public function getPlane()
    {
        return $this->plane;
    }

    /**
     * @param \stdClass $rawFace
     */
    public function initFromRaw(\stdClass $rawFace)
    {
        $this->setPlaneRaw(
            new \Math_Vector3($rawFace->points[0]),
            new \Math_Vector3($rawFace->points[1]),
            new \Math_Vector3($rawFace->points[2])
        );
        
        $this->texture = $rawFace->texture;
        $this->properties = $rawFace->properties;
    }

    /**
     * @param $number
     * @return string
     */
    private function getPropertyType($number)
    {
        if ((float) (int) $this->properties[ $number ] !== (float) $this->properties[ $number ]) {
            return 'f';
        }
        return 'd';
    }

    /**
     * Takes all point vectors ("vertexes") of the polygon describing the face and sorts them
     * in clockwise order.
     */
    public function sortVerticesClockwise()
    {
        $center = $this->calculateCenter();
        for ($n=0; $n <= count($this->vertexes)-3; $n++) {
            $a = new \Math_Vector3(\Math_VectorOp::substract($this->vertexes[$n], $center)->getTuple());
            $a->normalize();
            $p = Plane::getInstanceByThreePositionVectors($this->vertexes[$n], $center, new \Math_Vector3(\Math_VectorOp::add($center, $this->plane->getNormalVectorNormalized())->getTuple()));
            $smallestAngle = -1;
            $smallest = -1;
            for ($m = $n + 1; $m <= count($this->vertexes) -1; $m++) {
                if ($p->calculateSideOfPointVector($this->vertexes[$m]) !== Plane::SIDE_BACK) {
                    $b = new \Math_Vector3(\Math_VectorOp::substract($this->vertexes[$m], $center)->getTuple());
                    $b->normalize();
                    $angle = \Math_VectorOp::dotProduct($a, $b);
                    if ($angle > $smallestAngle) {
                        $smallestAngle = $angle;
                        $smallest = $m;
                    }
                }
            }
            if ($smallest == -1) {
                throw new \RuntimeException('Error: Degenerate polygon!');
            }

            //swap vertices
            $temp = $this->vertexes[$n+1];
            $this->vertexes[$n+1] = $this->vertexes[$smallest];
            $this->vertexes[$smallest] = $temp;
            unset($temp);
        }

        // Check if vertex order needs to be reversed for back-facing polygon
        $newPlane = Plane::getInstanceByThreePositionVectors($this->vertexes[0], $this->vertexes[1], $this->vertexes[2]);
        if (\Math_VectorOp::dotProduct($newPlane->getNormalVectorNormalized(), $this->plane->getNormalVectorNormalized()) < 0) {
            array_reverse($this->vertexes);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $format = '( %g %g %g ) ( %g %g %g ) ( %g %g %g ) %s';
        foreach ($this->properties as $key => $property) {
            $format .= sprintf(' %' . $this->getPropertyType($key), $property);
        }

        return sprintf(
            $format,
            $this->vertexes[0]->get(0),
            $this->vertexes[0]->get(1),
            $this->vertexes[0]->get(2),
            $this->vertexes[1]->get(0),
            $this->vertexes[1]->get(1),
            $this->vertexes[1]->get(2),
            $this->vertexes[2]->get(0),
            $this->vertexes[2]->get(1),
            $this->vertexes[2]->get(2),
            $this->texture);
    }
}
