Javascript module for Kohana
============================

Maintaining the list of js files you need to include in your html
pages/views can become very annoying, even if you use a javascript
script loader.

I wrote this module to make this job easier.

If you are new to Kohana modules, and never created a new module, [read
this first][] .

## Set up

### Step 1

Clone/Download this repo, then create the javascript modules and copy
the files into it. Enable the module in *bootstrap.php*.

### Step 2

Tell the module what scripts (or directories) to include, what to
exclude, and if there are dependencies.

All you need to do is to create a new config file with the name
*javascript.* (ex: *application/config/javascript.php*)

Here is an example of how*javascript.php* should look like:

    <?php defined('SYSPATH') OR die('No direct access allowed.');

    return array(

        // Directories to scan for javascript files (relative to DOCROOT)

        'dirs' => array(

            'static/js/folder_a/',

            'static/js/vendor/folder_b',

            'static/js/vendor/folder_c'

          // etc.

        ),

        // Files to exclude

        'exclude' => array(

            'static/js/folder_a/i_dont_want_this_file.js'

        ),

        // External resources
        'external' => array(
            // You can include also external files, however you can load external files separately and don't tell the module about them. It depends on you

           'http://code.jquery.com/jquery-latest.min.js'

        ),

        // Files listed here will be added before any directory scanning, immediately after external resources

        'priority' => array(

            'static/js/vendor/folder_b/priority.js'

        ),

        /**

         * Dependencies

         * NOTE: In some cases you can handle dependecies simply by scanning your js directories in the appropriate order.

         */

        'dependencies' => array(

            'static/js/vendor/folder_a/dog.js' => array('static/js/vendor/folder_a/animal.js'),

        ),

        // Base url - used to create absolute path to js files

        'base_url' => Url::base(TRUE)

    );


## Examples of usage


    // Search for javascript files (based on the javascript config group), and echo a list of script tags with the files found.

    echo Javascript::instance('javascript')->scan()->render();

    // Search for javascript files and combine them in a single script tag as inline javascript

    echo Javascript::instance('javascript')->scan()->combine()->render();

    // Search for javascript files, combine them, compress the code, save to 'static/js/main.js' then echo the code in a single script tag as inline javasript.

    echo Javascript::instance('javascript')->scan()->combine()->compress()->save('static/js/main.js')->render();

Hope you guys like it and it helps you work faster :)

  [read this first]: http://kohanaframework.org/3.2/guide/kohana/modules