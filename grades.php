<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gradesy</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
</head>

<body>
    <link rel="stylesheet" href="vendor/waves/waves.min.css" />
	<link rel="stylesheet" href="vendor/wow/animate.css" />
    <link rel="stylesheet" href="css/nativedroid2.css" />
    <!--    
        Carter Brehm
        index.php
        INFO2439.PLA
        Thoendel
        10/1/13
    -->

    <?php

    function printClassOverview() {
        if (isset($_SESSION['TABLE'])) {
            $emptyClasses = array();
            echo "<div data-role=\"content\">
            <ul data-role=\"listview\" data-inset=\"true\">";
            echo "<li data-role=\"list-divider\">Classes</li>";

                $table = $_SESSION['TABLE'];
                foreach ($table as $row) {
                    if(1 === preg_match('~[0-9]~', mb_substr($row[5], 0, 5))) {
                        echo "<li><a href=\"#" . str_replace(' ', '', $row[0] . $row[1]) . "\">". $row[0] . "<span class=\"ui-li-count\">" . mb_substr($row[5], 0, 5); 
                        echo "%"; 
                    } else {
                        array_push($emptyClasses, $row);
                    }
                    if(preg_match("/[a-z]/i", $row[6])) {
                    	echo " | " . mb_substr($row[6], 0, 2);
                    } 
                    echo "</span>" . "</a></li>";
                }

                foreach ($emptyClasses as $row) {
                    echo "<li class=\"ungraded\"><a href=\"#" . str_replace(' ', '', $row[0] . $row[1]) . "\">". $row[0]; 
                    echo "<span class=\"ui-li-count\">N/A</span></a></li>";
                }

            echo "</ul><p style=\"text-align: center;\"><b>Tip:</b> You can save this to your home screen as an app by pressing the share button below and hitting \"Add to Home Screen.\" This will save your login automatically.</p>
        </div>";
        } else {
            echo "<p style=\"text-align: center; margin: 10px;\">Your login info seems to be incorrect. Please go back and try again.</p>";
        }
    }

    function printClass($row) {
        echo "<div data-role=\"page\" data-title=\"" . $row[0] . "\" id=\"" . str_replace(' ', '', $row[0] . $row[1]) . "\">
        <header data-role=\"header\" data-add-back-btn=\"true\">
            <h1>" . $row[0] .  "</h1>
            <a data-rel=\"back\">↩️</a>
        </header>
        <div data-role=\"content\">
            <ul data-role=\"listview\" data-inset=\"true\">";
        echo "<li data-role=\"list-divider\">Class Details</li>";
        unset($row[0]);
        echo "<li><b>Term: </b>" . $row[1] . "</li>";
        unset($row[1]);
        echo "<li><b>Class Hour: </b>" . $row[2] . "</li>";
        unset($row[2]);
        echo "<li><b>Class Time: </b>" . $row[3] . "</li>";
        unset($row[3]);
        echo "<li><b>Teacher: </b>" . $row[4] . "</li>";
        unset($row[4]);
        echo "<li><b>Percentage: </b>" . $row[5] . "</li>";
        unset($row[5]);
        echo "<li><b>Grade: </b>" . $row[6] . "</li>";
        unset($row[6]);
        echo "<li data-role=\"list-divider\">Terms</li>";
        if ($row) {
            foreach ($row as $link) {
                echo "<li>" . $link . "</li>";
            }
        } else {
            echo "<li>There are no available terms with assignments. Has anything been graded in your class yet?</li>";
        }
        echo "</ul>
        </div>
    </div>";
    }

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    function makeAuthRequest($username, $password)
    {
        // make the request to the authentication server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://simsweb.esu3.org/processlogin.cfm?sdist=plv');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "userid=" . $username . "&password=" . $password);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);

        //grab the link from curl info, then shut down the request
        $curl_array = curl_getinfo($ch);
        $number_array = array_values($curl_array);
        $auth_complete_link = $number_array[25];
        curl_close($ch);
        return $auth_complete_link;
    }

    function fetchGrades($url)
    {

        // make a new DOM object to hold the file 
        $dom = new DOMDocument();
        @$dom->loadHTML(file_get_contents($url));

        // get all the links and find the one that has the child ID we need (it always should be the second one)
        $child_array = $dom->getElementsByTagName('a');
        $child_link = $child_array->item(2);
        $child_href = $child_link->getAttribute('href');
        $exploded_url = explode("?", $child_href);
        $child_ids = $exploded_url[1];
        $full_url = "http://simsweb.esu3.org/gradebookschedules.cfm?" . $child_ids;

        // make a new request to the gradebook page
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        // trim down to only the data table on the page
        $new_result = explode("</b></center></td>", $result)[1];
        $new_new_result = explode("</table>", $new_result, 2)[1];
        $new_new_new_result = explode("</table>", $new_new_result, 2)[1];
        $new_new_new_new_result = explode("* To", $new_new_new_result)[0];
        

        // load that table into a new DOM object, grab the tables, and convert them into arrays
        @$dom->loadHTML($new_new_new_new_result);
        $dom->preserveWhiteSpace = false;
        $tables = $dom->getElementsByTagName('table');

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
        foreach ($rows as $row) {

            // get each column by tag name  
            $cols = $row->getElementsByTagName('td');
            $row = array();
            $i = 0;
            foreach ($cols as $node) {
                # code...
                //print $node->nodeValue."\n";   
                if ($i == 7) {
                    foreach ($node->getElementsByTagName('a') as $link) {
                        $full_link = $link->getAttribute('href');
                        $term = get_string_between($full_link, "term=", "&");
                        array_push($row, "<a href=\"https://simsweb.esu3.org/" . $full_link . "\">Quarter " . $term . "</a>");
                    }
                } else {
                    $row[] = $node->nodeValue;
                }
                $i++;
            }
            $table[] = $row;
        }
        unset($table[0]);
        return $table;
    }

    $username = $_GET['id'];
    $password = $_GET['pass'];

    $authResponse = makeAuthRequest($username, $password);

    if (strpos($authResponse, "childlist")) {
        $_SESSION['TABLE'] = fetchGrades($authResponse);
    }

    ?>

    <div data-role="page" id="home" data-title="Gradesy">
        <header data-role="header">
            <a href="index.html">🏠</a>
            <h1>Gradesy</h1>
        </header>
        <?php
            printClassOverview();
        ?>
        <footer style="text-align: center;" data-role="footer" data-position="fixed">Created by: <a href="mailto:crbrehm@mail.mccneb.edu">Carter Brehm</a></footer>
    </div>
    <?php 
        foreach ($_SESSION['TABLE'] as $row) {
            printClass($row);
        }
    ?>
</body>

</html>