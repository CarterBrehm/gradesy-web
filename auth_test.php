<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gradesy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <!--    
        Carter Brehm
        index.php
        INFO2439.PLA
        Thoendel
        10/1/13
    -->

    <?php

    // print all errors to the page
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    function fetchAssignments($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $result = explode("<table CELLPADDING=\"0\" CELLSPACING=\"0\" border=2 width=100%>", $result)[2];
        $result = explode("</table>", $result)[0];
        $full_html = "<table CELLPADDING=\"0\" CELLSPACING=\"0\" border=2 width=100%>" . $result . "</table>";
        
        $dom = new DOMDocument();

        //load the html  
        @$dom->loadHTML($full_html);

        //discard white space   
        $dom->preserveWhiteSpace = false;

        //the table by its tag name  
        $tables = $dom->getElementsByTagName('table');


        //get all rows from the table  
        $rows = $tables->item(0)->getElementsByTagName('tr');
        // get each column by tag name  
        $cols = $rows->item(0)->getElementsByTagName('th');
        $row_headers = NULL;
        foreach ($cols as $node) {
            //print $node->nodeValue."\n";   
            $row_headers[] = $node->nodeValue;
        }

        $table = array();
        //get all rows from the table  
        $rows = $tables->item(0)->getElementsByTagName('tr');
        foreach ($rows as $row) {
            // get each column by tag name  
            $cols = $row->getElementsByTagName('td');
            $row = array();
            $i = 0;
            foreach ($cols as $node) {
                # code...
                //print $node->nodeValue."\n";   
                if ($row_headers == NULL)
                    $row[] = $node->nodeValue;
                else
                    $row[$row_headers[$i]] = $node->nodeValue;
                $i++;
            }
            $table[] = $row;
        }
        $_SESSION['TABLE'] = $table;
    }

    fetchAssignments("https://simsweb.esu3.org/childassignmentlist.cfm?cid=1004986&scid=355%20&crsid=FL43&sectid=Y41&term=1&CFID=10615120&CFTOKEN=42327470");

    ?>

    <pre>
    <?php
        print_r($_SESSION['TABLE']);
    ?>
    </pre>

    ?>
</body>

</html>