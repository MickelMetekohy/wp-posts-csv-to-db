# WPpostsCSVtoDB.com

WPpostsCSVtoDB allows you to upload posts and meta date to Wordpress outside of the wordpress environment.
Slicing your CSV up into multiple CSV's will enables you to upload posts to the db with multiple local servers.

 _**Uploaded files are stored in the uploads folder!!**_ Don't forget to delete them.

## Dev on local machine

* Need php7
* Changes to php.ini for MAMP
 - Memory_limit = 128M (or higher)
 - Upload_max_filesize = 128M (or higher)
 - Post_max_size = 128M (or higher)

## Use

* `npm install` (no scripts yet)
* Go through `app/index.php` to set the default values and database creds.
* Start uploading data.

## @TODO

* Setup build process with webpack or gulp.
* Abstract assets into build folder.
* Remove CSV slices after completion.
* GUI for database credentials.
* GUI for wp_postmeta default key value pairs.
* GUI for wp_post default values.
