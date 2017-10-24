# Callgrind to PlantUML
Generate PlantUML sequence diagrams for PHP using XDebug callgrind files

# Requirements
To setup and run the application you will need:
PHP ^7.0
Composer

# Rendering requirements 
If you want to use the "image" export option, you will additionally need:
JRE
PlantUML Jar file
GraphViz DOT binary

# Setup
1. checkout this repository
2. run composer install

# Usage
By default, running the command will take the entire input of the given callgrind file, and use it to produce a PlantUML format sequence diagram. Keep in mind that these will be very large for most applications.  To limit the size of the diagram, see the filters section. Rendering large diagrams will require a lot of RAM and may require tweaking the memory settings for the JVM.

## Running the command
php application.php generate <callgrind-file>

## Export format
On runtime, the user can choose between the following export formats:
1. Screen
2. File
3. Image

### 1. Screen
When using this option, the PlantUML sequence diagram will be outputted to StdOUT in text representation. No further configuration is needed for this option.

### 2. File
When using this option, the PlantUML sequence diagram will be saved to the specified file location, and will contain the sequence diagram in text representation. After choosing this option you will be prompted with:
- Output file name
- Output file location

### 3. Image
When using the image option, the application will use the PlantUML and Graphiz DOT application to produce a PNG image containing the sequence diagram. After choosing this option you will be prompted with:
- DOT file location
- JAR file location
- Max memory (for JRE)
- Max diagram size (for PlantUML)
- Output file name
- Output file location

## Filters
To be able to control the size of you diagram (and focus on a specific part) you can apply filters.

### Not deeper than
php application.php generate <callgrind-file> --not-deeper-than ClassName::Method





## Maintainers: 
[papapezs](https://github.com/papapezs)
[desjob](https://github.com/desjob)

