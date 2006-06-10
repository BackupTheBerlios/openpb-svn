                         __
                      __|  |__
      ______   _____ |__    __|
     /  __  \ |   _ \   |  |
     | |  | | |  | \ \  |  |
     | |__| | |  |_/ /  |  |_
     \______/ |   __/    \___| emplate
              |  |
     Open     |__| ower

     Open Power Template v. 1.0.0 readme
     Professional templating engine for PHP 5

SHORT INSTALLATION

 1. Create directories: "templates" for your template files, "templates_c" for compiled templates
 2. "templates" has to be readable for the server, "templates_c" has to be writable for the server
 3. Copy the files from the "lib" directory into your project directory
 4. Include "opt.class.php" file into your project
 5. Enjoy!

More info on http://opt.openpb.net/
Docs: /docs/manual_en.pdf

--------------------
How to do unitTests:

 1. Go to http://pear.php.net/package/PHPUnit and download the latest 1.3.x version of PHPUnit package.
 2. Extract it to the /unitTest/PHPUnit directory. The "PHPUnit.php" file must be under the location /unitTest/PHPUnit/PHPUnit.php. Be sure you have kept the OPT source location, as it is in the archive.
 3. Run the test files.

Test files:
 1. testme.php - basic test cases of the main OPT parser.
 2. testme_html.php - browser-friendly version of the script above.
 3. testcompiler.php - compilation tests.
 4. testcompiler_html.php - browser-friendly version of the script above.