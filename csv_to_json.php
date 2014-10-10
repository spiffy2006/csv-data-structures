<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript">
            window.onload = function() {
                document.getElementsByClassName('link')[0].addEventListener('click', newFile);
                function newFile() {
                    var finput = document.getElementById('finput');
                    var fileNum = parseInt(finput.getAttribute('fileNum'));
                    var html = '<label for="file">Filename:</label><input type="file" name="file' + fileNum + '" id="file" /><br />';
                    finput.innerHTML += html;
                    finput.setAttribute('fileNum', (fileNum + 1))
                }
            };
        </script>
    </head>
    
<body>
<?php

class reports {
    private $masterJSON = array();
    
    /*\
     *  __construct() function
     *
     *  Arranges data into a multidimensional array whose first dimension is numerically indexed and second dimension is indexed with 'name' and 'raw'.
     *  'name' is the header from the csv, and 'raw' is the data from that column.
     *  
     *  This is an example of the array:
     *
     *  array(
     *    [0] => array(
     *      'name' => 'CSV Header',
     *      'raw' => ['First Value', 'Second Value', 'Third Value']
     *    ),
     *    [1] => array(
     *      'name' => 'CSV Header',
     *      'raw' => ['First Value', 'Second Value', 'Third Value']
     *    )
     *  )
     *
    \*/
    
    public function __construct($files) {
        foreach($files as $file_array => $file_data) {
            $file = $_FILES[$file_array]['tmp_name'];
            $handle = fopen($file, "r");
            $masterJSON = $this->masterJSON;
            $json = array();
            $ftc = true;
            while( ( $data = fgetcsv($handle, 1000, ",") ) !== false ) {
                $data = array_map("utf8_decode", $data);
                for ( $i = 0; $i < count($data); $i++ ) {
                    if ($ftc) {
                        $json[$i]['name'] = trim($data[$i], " ?");
                        $json[$i]['raw'] = array();
                        if ($i == (count($data) - 1)) {
                            $ftc = false;
                        }
                    } else {
                        array_push($json[$i]['raw'], trim($data[$i]));
                    }
                }
            }
            $this->masterJSON = array_merge($masterJSON, $json);
         }
        return $this->masterJSON;
    }
    
    /*\
     *  data_by_cells() function
     *  
     *  Arranges data into a multidimensional array whose first dimension is numerically indexed and represents the first row of data from the CSV. The second dimension is CSV Header and the value of a given cell in the CSV.
     *  
     *  This is an example of the array:
     *
     *  array(
     *    [0] => array(
     *      'CSV Header' => 'Value of first cell of first column',
     *      'CSV Header' => 'Value of first cell of second column',
     *      'CSV Header' => 'Value of first cell of third column',
     *      'CSV Header' => 'Value of first cell of fourth column'
     *    ),
     *    [1] => array(
     *      'CSV Header' => 'Value of second cell of first column',
     *      'CSV Header' => 'Value of second cell of second column',
     *      'CSV Header' => 'Value of second cell of third column',
     *      'CSV Header' => 'Value of second cell of fourth column'
     *    )
     *  )
     *
    \*/
    
    public function data_by_cells() {
        $masterJSON = $this->masterJSON;
        $newJSON = array();
        for($i = 0; $i < count($masterJSON); $i++) {
            for($j = 0; $j < count($masterJSON[$i]['raw']); $j++) {
                $newJSON[$j][$masterJSON[$i]['name']] = $masterJSON[$i]['raw'][$j];
            }
        }
        return $newJSON;
    }
    
    /*\
     *  data_by_frequency() function
     *
     *  Arranges data into a multidimensional array whose first dimension is numerically indexed and second dimension is indexed with 'header' and 'frequency'.
     *  
     *  This is an example of the array:
     *
     *  array(
     *    [0] => array(
     *      'header' => 'Name of csv header',
     *      'frequency' => array(
     *         'Data from column' => 'Frequency of data in column',
     *         'Data from column' => 'Frequency of data in column', 
     *       )
     *    )
     *  )
     *
    \*/
    
    public function data_by_frequency() {
        $masterJSON = $this->masterJSON;
        $newJSON = array();
        for ($i = 0; $i < count($masterJSON); $i++){
            $newJSON[$i]['header'] = $masterJSON[$i]['name'];
            $newJSON[$i]['frequency'] = array_count_values($masterJSON[$i]['raw']);
        }
        return $newJSON;
    }
    
    /*\
     *  data_by_frequency() function
     *
     *  Arranges data into a multidimensional array whose first dimension is a numeric array containing data grouped by data under a specific header. 
     *  The second dimension is an array whose keys are 'name' and 'raw'. 'name' is the name of the datum that is being grouped, and 'raw' is all of the data associated with that datum.
     *
     *  The 'raw' array is a numerically indexed array whose value is an array with 'name', 'data', and condensed.
     *      In this array 'name' is the name of the header that the data is under.
     *      'data' is the raw data from this data set.
     *      'condensed' is an array of a condensed version of the data containing the keys: 'name' and 'amount'.
     *          'name is the name of the data, and 'amount' is either the amount that data is shown in the data set, or the sum of the numeric data.
     *  
     *  This is an example of the array:
     *
     *  array(
     *    [0] => array(
     *      'name' => 'Data Grouped By',
     *      'raw' => array(
     *        [1] => array(
     *          'name' => 'CSV Header',
     *          'data' => array(
     *            [0] => 'Data from group',
     *            [1] => 'Data from group',
     *            [2] => 'Data from group',
     *          ),
     *          'condensed' => array(
     *            'name' => 'Data from group',
     *            'amount' => 3
     *          )
     *        )
     *        [2] => array(
     *          'name' => 'CSV Header',
     *          'data' => array(
     *            [0] => 3,
     *            [1] => 5.63,
     *            [2] => 1.1,
     *          ),
     *          'condensed' => 9.73
     *          )
     *        )
     *      )
     *    )
     *  )
     *
    \*/
    
    public function data_by_header($header) {
        
        $masterJSON = $this->masterJSON;
        $keys = array();
        $newJSON = array();
        
        for ($i = 0; $i < count($masterJSON); $i++){
            if ($masterJSON[$i]['name'] == $header) {
                $keys = array_keys(array_count_values($masterJSON[$i]['raw']));
            }
        }
        
        $keys = array_filter($keys);
        
        for($j = 0; $j < count($keys); $j++) {
            
            $newJSON[$j]['name'] = $keys[$j];
            $newJSON[$j]['raw'] = array();
            for($k = 0; $k < count($masterJSON); $k++) {
                for($l = 0; $l < count($masterJSON[$k]['raw']); $l++) {
                    if ($keys[$j] == $masterJSON[$k]['raw'][$l]) {
                        for($m = 0; $m < count($masterJSON); $m++) {
                            if ($masterJSON[$m]['name'] != $header) {
                                $newJSON[$j]['raw'][$m]['name'] = $masterJSON[$m]['name'];
                                if (isset($newJSON[$j]['raw'][$m]['data']) && is_array($newJSON[$j]['raw'][$m]['data'])) {
                                    array_push( $newJSON[$j]['raw'][$m]['data'], $masterJSON[$m]['raw'][$l] );
                                } else {
                                    $newJSON[$j]['raw'][$m]['data'] = array($masterJSON[$m]['raw'][$l]);
                                }
                            }
                        }
                    }
                }
            }
            
        }
        
        for($i = 0; $i < count($newJSON); $i++) {            
            for($j = 1; $j <= count($newJSON[$i]['raw']); $j++) {
                if (intval($newJSON[$i]['raw'][$j]['data'][1])) {
                    $newJSON[$i]['raw'][$j]['condensed'] = array_sum( array_map( 'floatval', $newJSON[$i]['raw'][$j]['data'] ) );
                    
                } else {
                    $tmp = array_count_values($newJSON[$i]['raw'][$j]['data']);
                    $newJSON[$i]['raw'][$j]['condensed'] = array();
                    foreach($tmp as $k => $v) {
                        array_push($newJSON[$i]['raw'][$j]['condensed'], array( 'name' => $k, 'amount' => $v ));
                    }
                }
            }            
        }
        
        return $newJSON;
    }
    
}

if (isset($_POST['submit'])) {
    
    $d3_data = new reports($_FILES);
    
    $data = $d3_data->data_by_header('SEOSpecialist');
    var_dump($data);
    
} else { ?>



<form method="post" action="" enctype="multipart/form-data">
    <div id="finput" fileNum="1"><label for="file">Filename:</label>
    <input type="file" name="file" id="file"><br></div>
    <input name="submit" value="Submit" type="submit" />
</form>
<a href="javascript:void();" class="link">New File Input</a>

<?php } ?>
</body>
</html> 