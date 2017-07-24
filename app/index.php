<?php
  $up_error_mes = '';
  $up_succes_mes = '';
  $file_destination = null;
  $connetion = null;

  // @NOTE debug
  // echo '<pre>';
  // print_r($GLOBALS);
  // echo '</pre>';

  /**
   * recursive delete files and directeries untill $src is reached
   */
  function rrmdir($src) {
      $dir = opendir($src);
      while(false !== ( $file = readdir($dir)) ) {
          if (( $file != '.' ) && ( $file != '..' )) {
              $full = $src . '/' . $file;
              if ( is_dir($full) ) {
                  rrmdir($full);
              }
              else {
                  unlink($full);
              }
          }
      }
      closedir($dir);
      rmdir($src);
  }

  /**
   * Connect to database array( PDO::ATTR_PERSISTENT => true )
   */
  function dbConnect() {

    // @NOTE comment me out when creds are set
    die("STOP me here! Make sure the correct Db is connected!!");

    try {
      $host = '[]';
      $dbname = '[]';
      $user = '[]';
      $pass = '[]';
      return new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    } catch (PDOException $e) {
      print "Error!: " . $e->getMessage() . "<br/>";
      die();
    }
  }

  /**
   * Close the database connection
   */
  function closeConnect() {
    // $close all instances that talk to the db = null;
    $connetion = null;
    // close objects and the remove slices
    unlink($file_destination);
  }

  /**
   * Open connection to uploaded CSV
   */
  function openCsvFile($file_destination) {
    // set the src file
    $srcFile = new SplFileObject($file_destination);
    uploadRows($srcFile);
  }

  /**
   * Deal with the CSV header for wp_posts table
   */
  function insert_post($connetion, $post_data) {
    $escape_pc = [];
    foreach($post_data AS $pc) {
      $escape_pc[] = addslashes($pc);
    }
    $build_post_query = "INSERT INTO wp_posts (";
    $build_post_query .= implode(", ", array_keys($post_data));
    $build_post_query .= ") VALUES ('";
    $build_post_query .= implode("', '", $escape_pc);
    $build_post_query .= "')";
    $connetion->query($build_post_query);
    return $connetion->lastInsertId();
  }

  /**
   * Deal with the CSV header for wp_postmeta table
   */
  function insert_postmeta($connetion, $id, $meta_data) {
    foreach($meta_data AS $key => $val ) {
      $esc_val = addslashes($val);
      $build_meta_query = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES ('";
      $build_meta_query .= $id . "', '" . $key . "', '" . $esc_val;
      $build_meta_query .= "')";
      $connetion->query($build_meta_query);
    }
  }


  /**
   * find all column keys
   */
  function add_meta_columns_per_table($header_line) {
    // set default values
    $wp_post_keys = [
      'post_author' => '',
      'post_date' => '',
      'post_date_gmt' => '',
      'post_content' => '',
      'post_title' => '',
      'post_excerpt' => '',
      'post_status' => '',
      'comment_status' => '',
      'ping_status' => '',
      'post_password' => '',
      'post_name' => '',
      'to_ping' => '',
      'pinged' => '',
      'post_modified' => '',
      'post_modified_gmt' => '',
      'post_content_filtered' => '',
      'post_parent' => '0',
      'guid' => '',
      'menu_order' => '',
      'post_type' => '',
      'post_mime_type' => '',
      'comment_count' => ''
    ];
    $wp_postmeta_keys = [];
    foreach($header_line AS $hl) {
      if(! array_key_exists(trim(utf8_encode($hl), "ï»¿\r\n"), $wp_post_keys)) {
        $wp_postmeta_keys[trim(utf8_encode($hl), "ï»¿\r\n")] = '';
      }
    }
    array_pop($meta_columns);

    // add default meta key values pairs to $wp_postmeta_keys
    $wp_postmeta_keys = array_merge ($wp_postmeta_keys, [
      '[REPLACE ME WITH METAKEY]' => '[REPLACE ME WITH METAVALUE]',
      '[REPLACE ME WITH METAKEY1]' => '[REPLACE ME WITH METAVALUE]',
    ]);

    return [$wp_post_keys, $wp_postmeta_keys];
  }

  function add_content_per_column($header_line, $content_line, $post_keys, $meta_keys) {
    $post_key_val = $post_keys;
    $meta_key_val = $meta_keys;
    foreach($header_line AS $key => $hl) {
      // go through the csv header to create key-value pairs for entry in to the db
      if(array_key_exists(trim(utf8_encode($hl), "ï»¿\r\n"), $post_key_val)) {
        $post_key_val[trim(utf8_encode($hl), "ï»¿\r\n")] = trim(utf8_encode($content_line[$key]), "ï»¿\r\n");
      }
      if(array_key_exists(trim(utf8_encode($hl), "ï»¿\r\n"), $meta_key_val)) {
        $meta_key_val[trim(utf8_encode($hl), "ï»¿\r\n")] = trim(utf8_encode($content_line[$key]), "ï»¿\r\n");
      }
      
      // format data as necessary
      // format the post_title
      $post_key_val['post_title'] = '[THE TITLE IS]: ' . $meta_key_val['METAKEY'] . ' [GENERATED];';
    }

    return [$post_key_val, $meta_key_val];
  }


  /**
   * Loop through rows in CSV
   */
  function uploadRows($srcFile) {
    // keep track in the loop (print)
    $line_count = 0;
    $header_line = null;
    $post_keys = null;
    $meta_keys = null;

    $count_me = 1;

    $connetion = dbConnect();
    $connetion->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    // foreach line in the src file, create slices
    foreach ($srcFile as $key => $line) {
      // if this is the first loop save the header
      if($line_count == 0) {
        $header_line = explode(',', $line);
        $column_keys = add_meta_columns_per_table($header_line);
        $post_keys = $column_keys[0];
        $meta_keys = $column_keys[1];
        $line_count++;
      } else {
        $content_line = str_getcsv($line);
        $column_keys_values = add_content_per_column($header_line, $content_line, $post_keys, $meta_keys);
        $line_count++;

        $new_post_id = insert_post($connetion, $column_keys_values[0]);
        insert_postmeta($connetion, $new_post_id, $column_keys_values[1]);

        // @NOTE echo one row to debug the imports
        // Don't forget to comment out the 2 lines above
        // echo '<pre>';
        // var_dump($column_keys_values[0]);
        // var_dump($column_keys_values[1]);
        // echo '</pre>';
        // die('uploadRows');

        echo $count_me++ . '<br>';
      }
    }
    closeConnect();
  }


  /**
   * Upload CSV to Uploads/[some_id-csv].csv
   */
  if(isset($_FILES['csvfile'])) {
    // The file
    $up_file      = $_FILES['csvfile'];
    // The file upload properties
    $up_name      = $up_file['name'];
    $up_tmp_name  = $up_file['tmp_name'];
    $up_size      = $up_file['size'];
    $up_error     = $up_file['error'];

    // check and model the file extention
    $up_ext       = explode('.', $up_name);
    $up_ext       = strtolower(end($up_ext));
    $up_allowed_ext = array('csv');

    // checks
    // Set Max upload size
    $up_max_size  = 128*1024*1024;
    $up_error_mes .= $up_error !== 0 ? 'An error occurred' : false;
    $up_error_mes .= $up_size > $up_max_size ? 'The file you are trying to upload is to big' : false;
    $up_error_mes .= !in_array($up_ext, $up_allowed_ext) ? 'The file you are trying to upload does not have the right extention' : false;

    if($up_error_mes === '' ) {
      // create unique id with random int and time
      $file_stamp = time() . uniqid('_', true);
      // set the name and location for the uploaded
      $file_name_up = $file_stamp . '.' . $up_ext;
      $file_destination = '../uploads/' . $file_name_up;

      if(move_uploaded_file($up_tmp_name, $file_destination)) {
        // a file was uploaded from the temp location to the uploads location , do something with it
        $up_succes_mes .= 'Your file upload was succesfull';
        // loop through the data and import
        openCsvFile($file_destination);
      }
    }
  }



?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">


    <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" type="text/css" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="app/styles.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" class="bg-ci">


    <section id=csv-form-container class="bg-ci" id="csv">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <form enctype="multipart/form-data" action="/" method="POST">
                        <div class="form-group">
                            <label for="csvfile">WP csv posts dbLoader</label>
                            <input accept=".csv" type="file" name="csvfile" class="form-control" id="csvfile" placeholder="*.csv">
                        </div>
                        <button type="button" class="btn btn-default">Load</button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <?php
    if($_FILES || $up_error_mes) {
      echo '<section class="bg-primary" id="file">';
        echo '<pre>';
          print_r($_FILES);
          echo '<br>';
          echo $up_error_mes;
        echo '</pre>';
      echo '</section>';
    }
    ?>


    <!-- script -->
    <script type="text/javascript" src="app/scripts.js"></script>


</body>

</html>
