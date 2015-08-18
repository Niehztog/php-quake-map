# php-quake-map #

php-quake-map is a php composer package. It is developed for educational purposes. the goal is to understand the Quake II map format, how it can be interpreted and correctly generated.

### Features ###

* read in and parse Quake II map files
* compute true polygon coordinates for every brush
* store the map data in PHP data structures
* write a syntactically correct Quake II map file

Current state of development: Parsing/reading works, writing doesn't

### Useful links: ###
* https://github.com/stefanha/map-files/blob/master/MAPFiles.pdf
* http://www.gamers.org/dEngine/quake/QDP/qmapspec.html
* https://developer.valvesoftware.com/wiki/MAP_file_format