<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gradesy</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
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

    function printClassOverview() {
        if (isset($_SESSION['TABLE'])) {
            echo "<div data-role=\"content\"><p style=\"text-align: center;\"><b>Tip:</b> You can save this to your home screen as an app by pressing the share button below and hitting \"Add to Home Screen.\"</p>
            <ul data-role=\"listview\" data-inset=\"true\">";
            echo "<li data-role=\"list-divider\">Classes</li>";

                $table = $_SESSION['TABLE'];
                foreach ($table as $row) {
                    echo "<li><a href=\"#" . str_replace(' ', '', $row[0]) . "\">". $row[0] . "<span class=\"ui-li-count\">" . $row[6] . "</span>" . "</a></li>";
                }

            echo "</ul>
        </div>";
        } else {
            echo "<p style=\"text-align: center; margin: 10px;\">Your login info seems to be incorrect. Please go back and try again.</p>";
        }
    }

    function printClass($row) {
        echo "<div data-role=\"page\" data-title=\"" . $row[0] . "\" id=\"" . str_replace(' ', '', $row[0]) . "\">
        <header data-role=\"header\" data-add-back-btn=\"true\">
            <h1>" . $row[0] .  "</h1>
        </header>
        <div data-role=\"content\">
            <ul data-role=\"listview\" data-inset=\"true\">";
        unset($row[0]);
        foreach ($row as $value) {
            echo "<li>" . $value . "</li>";
        }
        echo "</ul>
        </div>
    </div>";
    }

    function makeAuthRequest($username, $password)
    {
        // make the request to the authentication server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://simsweb.esu3.org/processlogin.cfm?sdist=plv');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "userid=" . $username . "%40paplv.org&password=" . $password);
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
                if ($row_headers == NULL)
                    $row[] = $node->nodeValue;
                else
                    $row[$row_headers[$i]] = $node->nodeValue;
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