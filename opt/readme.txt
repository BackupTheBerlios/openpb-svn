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

****************************
*  1.0.0 CHANGELOG         *
****************************
There is a lot of changes, 1.0.0 and 0.1.1 are completely different scripts.

 - [new] Everything is new, fresh and clean
 - [upd] License change to GNU LGPL
 - [fix] many problems with output cache (thanks to normanos)
 - [fix] bug #6
 - [fix] bug #5
 - [fix] bug #7
 - [fix] bug #8
 - [fix] bug #9
 - [fix] bug #12
 - [fix] bug #13
 - [fix] bug #14
 - [fix] bug #15
 - [fix] bug #16
 - [fix] bug #17
 - [fix] bug #18
 - [fix] bug #19
 - [fix] bug #21

****************************
*  0.1.1 CHANGELOG         *
****************************
 - [new] API that allows you to write your own template parser using OPT compiler.
 - [new] Functions may be called one from another: {function_one(function_two($block))}
 - [new] Added methods parse_capture() and fetch()
 - [new] Added method compile_cache_reset() used for cleaning compile cache.
 - [upd] Licence changed from GNU GPL to GNU LGPL.
 - [upd] New E_NOTICE remover.
 - [upd] Compile cache files are now protected from opening them from a browser.
 - [upd] Most of the functions converted into native PHP function calls.
 - [upd] Some optimizations done.
 - [fix] Unknown tag format bug removed.
 - [fix] Unexisting compile directory bug removed.
 - [fix] Invalid root/compile directory names bug removed.
 - [fix] %%cache.dat is opened now only once, when there are some templates to recompile.
 - shutdown() function removed from the code.
 - "cache" directive renamed into "compile"
 - "cache_disabled" directive renamed into "compile_cache_disabled"