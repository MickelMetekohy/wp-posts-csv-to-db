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

## @NOTE

### CSV

"Comma-separated values", a simple file format used to move tabular data between programs and databases. See also Comma-separated values on Wikipedia. The needed/correct line-ending-char(s) of a Feeds-imported CSV-file depends on the type of the operating system of the www-server:

If you are using a Linux-Server, use only "LF" at the line-end of the CSV-file.
If you are using a Windows-Server, use "CR+LF" at the line-end of the CSV-file.
If you are using a Mac-Server, use only "CR" at the line-end of the CSV-file.
The changing of the line-end of the CSV-file before importing is important, if the source of the CSV-file (e.g. your computer or the database of the CSV-file) has a different operating system!
For the meaning of "LF" (line feed) and "CR" (carriage return) see http://en.wikipedia.org/wiki/Newline#Representations.

If you have date fields in your input file:
The only allowed date formats in the input-file are:
"YYYY-MM-DD" or "MM/DD/YYYY" or "DD.MM.YYYY".
The delimiters are different for each format and have to be used properly!
This is only for the import!
Within e.g. a view, you can chose the format of the output.

When the Feeds-imported CSV-file is in "UTF8 with BOM"-format, then the import of special characters (letters like €, £, ß, ö ,ü, ä, Ö, Ä and Ü or other non-ASCII-signs) is without problems. See also UTF8 - Byte order mark on Wikipedia.
You can use a good editor like 'notepad++ on windows' or 'LibreOffice Calc', both when indicated: '... Portable', (or 'MS Excel') to change this.
Tip:
Use "Save as" and change the needed properties before and/or during saving the file ("before / during" is depending on the program used).
