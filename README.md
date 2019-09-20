# php-quake-map #

php-quake-map is a php composer package. It is developed for educational purposes. The goal is to understand the Quake II map format, how it can be interpreted and correctly generated.

### Features ###

* read in and parse Quake II map files
* compute the polygon coordinates for every brush
* store the map data in PHP data structures
* write a syntactically correct Quake II map file

Current state of development: Parsing and writing map files works. Coordinates seem to be not 100% accurate, since many maps appear ***LEAKED***. Geometry looks fine however.

### Useful links: ###
* https://github.com/stefanha/map-files/blob/master/MAPFiles.pdf
* http://www.gamers.org/dEngine/quake/QDP/qmapspec.html
* https://developer.valvesoftware.com/wiki/MAP_file_format
