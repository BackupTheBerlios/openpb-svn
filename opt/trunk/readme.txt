                         __
                      __|  |__
      ______   _____ |__    __|
     /  __  \ |   _ \   |  |
     | |  | | |  | \ \  |  |
     | |__| | |  |_/ /  |  |_
     \______/ |   __/    \___| emplate
              |  |
     Open     |__| ower

     Open Power Template v. 1.1.1 readme
     Professional templating engine for PHP 5
     
1. PROJECT INFORMATION

Open Power Template is a templating engine library written in PHP. The library contains many useful
features implemented, both low- and high-level, as well as the possibility of extending. This
is the first of the Open Power Libraries created by default for the Open Power Board project. Check
out also Open Power Forms and Open Power Driver!

2. REQUIREMENTS

OPT 1.1.1 requires at least PHP 5.0 or better. The library was also tested at the lastest PHP6 snapshot.
In this version, no problems were reported.

3. PACKAGE CONTENTS

/docs - reference manual (HTML, English version) - only the opt-1.x.x-docs archives!
/examples - various ready-to-run feature examples
/lib - library sources
/toolset - OPT Toolset files
/unitTest - unit test files
/readme.txt - you are reading it at the moment
/COPYING - license text

4. SHORT INSTALLATION

 1. Create directories: "templates" for your template files, "templates_c" for compiled templates
 2. "templates" has to be readable for the server, "templates_c" has to be writable for the server
 3. Copy the files from the "lib" directory into your project directory
 4. Include "opt.class.php" file into your project
 5. Enjoy!
 
5. USER MANUAL

If you have downloaded the "opt.1.x.x-docs" version, you can find the HTML English manual in the
/docs directory. The latest version is available on http://opt.openpb.net/

6. UNIT TESTS

How to do unitTests:

 1. Go to http://pear.php.net/package/PHPUnit and download the latest 1.3.x version of PHPUnit package.
 2. Extract it to the /unitTest/PHPUnit directory. The "PHPUnit.php" file must be under the location /unitTest/PHPUnit/PHPUnit.php. Be sure you have kept the OPT source location, as it is in the archive.
 3. Run the test files.

Test files:
 1. testme.php - basic test cases of the main OPT parser.
 2. testme_html.php - browser-friendly version of the script above.
 3. testcompiler.php - compilation tests.
 4. testcompiler_html.php - browser-friendly version of the script above.

7. LICENSE AND AUTHORS

Project authors:
* Tomasz "Zyx" Jedrzejewski - programmer, documentation writer (www.zyxist.com)
* Tomasz "Slump" Szczuplinski - project coordination, PR, Polish translations, etc.

The library is available under GNU Lesser General Public License. You can find the full text
in the "COPYING" file.