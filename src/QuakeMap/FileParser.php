<?php
namespace QuakeMap;

/**
 * Class FileParser
 * Parses map files and stores the data in php data structures
 *
 * @package QuakeMap
 */
class FileParser
{
    const MEANING_PARSE_ERROR            = 0;
    const MEANING_SECTION_START          = 1;
    const MEANING_SECTION_END            = 2;
    const MEANING_ATTRIBUTE_VALUE_PAIR   = 3;
    const MEANING_BRUSH_PLANE_DEFINITION = 4;
    
    const SECTION_ENTITY                 = 1;
    const SECTION_ENTITY_BRUSH           = 2;

    /**
     * Holds the php data structures containing all the map data
     * @var QuakeMap
     */
    private $map;

    /**
     * If activated, special debug info will be displayed
     * @var bool
     */
    private $debugMode = false;

    /**
     * Constructor, expects an empty QuakeMap instance
     *
     * @param QuakeMap $quakeMap
     */
    public function __construct(QuakeMap $quakeMap) {
        $this->map = $quakeMap;
    }

    /**
     * Loads and processes one map file
     *
     * @param $fileName
     */
    public function load($fileName)
    {
        $handle = fopen($fileName, 'r');
        if (false === $handle) {
            throw new \RuntimeException(sprintf('Mapfile %s not found.', $fileName));
        }
        
        $currentSection = null;
        $currentEntity = null;
        $lineCounter = 0;
        $brushCounter = 0;
        $entityCounter = 0;

        //assume one command per line
        while (false !== ($line = fgets($handle))) {
            $lineCounter++;
            $line = trim($line);
            
            if (true === $this->debugMode) {
                echo 'line ' . $lineCounter . ', section: ' . $currentSection . PHP_EOL;
            }
            
            $parseResult = $this->parseLine($line);
            if (null === $parseResult) {
                continue;
            }
            if (self::MEANING_PARSE_ERROR === $parseResult->meaning) {
                trigger_error('Line ' . $lineCounter . ' "' . $line . '" could not be parsed.' . PHP_EOL, E_USER_NOTICE);
                continue;
            }
            if (null === $currentSection) {
                if (self::MEANING_SECTION_START === $parseResult->meaning) {
                    $currentSection = self::SECTION_ENTITY;
                    $currentEntity = new Entity();
                    continue;
                } else {
                    trigger_error('Line ' . $lineCounter . ' "' . $line . '" is expected to be initialization of an entity.' . PHP_EOL, E_USER_ERROR);
                }
            } elseif (self::SECTION_ENTITY === $currentSection) {
                if (self::MEANING_SECTION_START === $parseResult->meaning) {
                    $currentEntity->newBrush();
                    $currentSection = self::SECTION_ENTITY_BRUSH;
                    continue;
                } elseif (self::MEANING_ATTRIBUTE_VALUE_PAIR === $parseResult->meaning) {
                    if (null === $currentEntity) {
                        $currentEntity = new Entity();
                    }
                    $currentEntity->addAttribute($parseResult->attributeName, $parseResult->attributeValue);
                    continue;
                } elseif (self::MEANING_SECTION_END === $parseResult->meaning) {
                    $this->map->addEntity($currentEntity);
                    $entityCounter++;
                    $currentEntity = null;
                    $currentSection = null;
                    continue;
                }
            } elseif (self::SECTION_ENTITY_BRUSH === $currentSection) {
                if (self::MEANING_BRUSH_PLANE_DEFINITION === $parseResult->meaning) {
                    $currentEntity->addBrushPlane($parseResult);
                    continue;
                } elseif (self::MEANING_SECTION_END === $parseResult->meaning) {
                    $currentEntity->finishBrush();
                    $brushCounter++;
                    $currentSection = self::SECTION_ENTITY;
                    continue;
                }
            }

            trigger_error('Line ' . $lineCounter . ' "' .$line . '" (' . strlen($line) . ') could no be parsed.' . PHP_EOL, E_USER_ERROR);
        }

        echo sprintf('Read %1$d entities with %2$d brushes', $entityCounter, $brushCounter).PHP_EOL;
    }

    /**
     * Interprets one line from a map file
     *
     * @param $line
     * @return null|\stdClass
     */
    private function parseLine($line)
    {
        $line = trim($line);
        //ignore empty lines
        if (0 === strlen($line)) {
            return null;
        }
        //ignore comments
        if (false !== ($pos = strpos($line, '//'))) {
            if (0 === $pos) {
                return null;
            }
            $line = substr($line, 0, $pos+2);
        }
        
        $result = new \stdClass();
        
        if ('{' === $line) {
            $result->meaning = self::MEANING_SECTION_START;
            return $result;
        }
        if ('}' === $line) {
            $result->meaning = self::MEANING_SECTION_END;
            return $result;
        }
        //if the line got 4 quotes assume its an attribute-value association
        //only entities can have these
        if (4 === substr_count($line, '"')) {
            $parts = explode('"', $line);
            $result->meaning = self::MEANING_ATTRIBUTE_VALUE_PAIR;
            $result->attributeName = $parts[1];
            $result->attributeValue = $parts[3];
            return $result;
        }
        if (1 === preg_match(BrushFace::PARSE_REGEXP_BRUSH_PLANE, $line, $matches)) {
            $result->meaning = self::MEANING_BRUSH_PLANE_DEFINITION;
            $result->points =
                [[(int) $matches[1], (int) $matches[2], (int) $matches[3]],
                [(int) $matches[4], (int) $matches[5], (int) $matches[6]],
                [(int) $matches[7], (int) $matches[8], (int) $matches[9]]];

            $result->texture = $matches[10];
            $result->properties =
                [(int) $matches[11], (int) $matches[12], (int) $matches[13],
                (float) $matches[14], (float) $matches[15],
                isset($matches[16]) ? (int) $matches[16] : 0,
                isset($matches[17]) ? (int) $matches[17] : 0,
                isset($matches[18]) ? (int) $matches[18] : 0];

            return $result;
        }
        
        $result->meaning = self::MEANING_PARSE_ERROR;
        return $result;
    }

}
