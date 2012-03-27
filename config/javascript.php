<?php defined('SYSPATH') OR die('No direct access allowed.');
return array(
    // Directories to scan for javascript files (relative to DOCROOT)
    'dirs' => array(
        'static/js/helpers',
        'static/js/classes',
        'static/js/i18n',
        'static/js/vendor',
        'static/js'
    ),

    // Files to exclude
    'exclude' => array(

    ),

    // External resources
    'external' => array(
        'http://code.jquery.com/jquery-latest.min.js'
    ),

    // Files listed here will be added before any directory scanning, immediately after external resources
    'priority' => array(
        'static/js/jquery.mvc.js'
    ),

    /**
     * Dependencies
     * NOTE: In some cases you can handle dependecies simply by scanning
     * your js directories in the appropriate order.
     */

    'dependencies' => array(
        'static/js/bootstrap.js' => array('static/js/classes/route.js'),
        'static/js/vendor/bootstrap/bootstrap-popover.js' => array('static/js/vendor/bootstrap/bootstrap-tooltip.js'),
    ),

    // Base url - used to create absolute path to js files
    'base_url' => Url::base(TRUE)
);