Functions to play with:
================================
*(see the config file for more details)*

1. instance - create a singleton instance
2. scan - scan the defined directories for js files, and filter the results
3. files - returns the list of files found
4. combine - combines all js files into a single variable
5. compress - compress the combined script using jsmin
6. render - render the files one-by-one or combined
7. save - save the combined script into a file

*Examples of usage:*

Javascript::instance()->scan()->combine()->render();

Javascript::instance()->scan()->combine()->render();

Javascript::instance()->scan()->combine()->compress()->save('static/js/basic.js')->render();
