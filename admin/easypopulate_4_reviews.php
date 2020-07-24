<?php
// $Id: easypopulate_4_attrib.php, v4.0.35.ZC.2 10-03-2016 mc12345678 $

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}


// attribute import loop - read 1 line of data from input file
while ($contents = fgetcsv($handle, 0, $csv_delimiter, $csv_enclosure)) { // while #1 - Main Loop
    $v_id = $contents[$filelayout['v_id']];
    $v_products_model = $contents[$filelayout['v_products_model']];
    $v_reviews_text = $contents[$filelayout['v_reviews_text']];
    $v_customers_name = $contents[$filelayout['v_customers_name']];

    $dateNum = rand(1, 10);

    if (!empty($v_products_model)) {
        $query = "SELECT * FROM " . TABLE_PRODUCTS . " WHERE (products_model = :v_products_model:) LIMIT 1";
        $query = $db->bindVars($query, ':v_products_model:', $v_products_model, 'string');
    } else {
        $query = "SELECT * FROM " . TABLE_PRODUCTS . " WHERE (products_id = :v_id:) LIMIT 1";
        $query = $db->bindVars($query, ':v_id:', $v_id, 'integer');
    }

    $result = ep_4_query($query);

    if (($ep_uses_mysqli ? mysqli_num_rows($result) : mysql_num_rows($result)) == 0) { // products_model is not in TABLE_PRODUCTS
        $display_output .= sprintf('<br /><font color="red"><b>SKIPPED! - Model: </b>%s - Not Found! Unable to apply attributes.</font>', !empty($v_products_model) ? $v_products_model : $v_id);
        $ep_error_count++;
        continue; // skip current record (returns to while #1)
    }

    while ($row = ($ep_uses_mysqli ? mysqli_fetch_array($result) : mysql_fetch_array($result))) { // BEGIN while #2
        $v_products_id = $row['products_id'];

        $sql = "INSERT INTO " . TABLE_REVIEWS . "
						(products_id, customers_id, customers_name,reviews_rating,date_added,last_modified,reviews_read,status)
						VALUES
						(:v_products_id:, :v_customers_id:, :v_customers_name:, :v_reviews_rating:, :v_date_added:, :v_last_modified:, :v_reviews_read:, :v_status:)";
        $sql = $db->bindVars($sql, ':v_products_id:', $v_products_id, 'integer');
        $sql = $db->bindVars($sql, ':v_customers_id:', 0, 'integer');
        $sql = $db->bindVars($sql, ':v_customers_name:', empty($v_customers_name) ? random_user() : $v_customers_name, 'string');
        $sql = $db->bindVars($sql, ':v_reviews_rating:', 5, 'integer');
        $sql = $db->bindVars($sql, ':v_date_added:', date('Y-m-d H:i:s', strtotime('-' . $dateNum . ' day')), 'date');
        $sql = $db->bindVars($sql, ':v_last_modified:', '0001-01-01 00:00:00', 'date');
        $sql = $db->bindVars($sql, ':v_reviews_read:', 0, 'integer');
        $sql = $db->bindVars($sql, ':v_status:', 1, 'integer');
        $errorcheck = ep_4_query($sql);
        $v_insert_id = ($ep_uses_mysqli ? mysqli_insert_id($db->link) : mysql_insert_id()); // id is auto_increment

        // TABLE_REVIEWS_DESCRIPTION
        $description_sql = "INSERT INTO " . TABLE_REVIEWS_DESCRIPTION . "
						(reviews_id, languages_id, reviews_text)
						VALUES
						(:v_reviews_id:, :v_languages_id:, :v_reviews_text:)";
        $description_sql = $db->bindVars($description_sql, ':v_reviews_id:', $v_insert_id, 'integer');
        $description_sql = $db->bindVars($description_sql, ':v_languages_id:', 1, 'integer');
        $description_sql = $db->bindVars($description_sql, ':v_reviews_text:', $v_reviews_text, 'string');

        $errorcheck = ep_4_query($description_sql);

    }  // END: while #2
} // END: while #1

function random_user() {
    $male_names = array("James", "John", "Robert", "Michael", "William", "David", "Richard", "Charles", "Joseph", "Thomas", "Christopher", "Daniel", "Paul", "Mark", "Donald", "George", "Kenneth", "Steven", "Edward");
    $famale_names = array("Mary", "Patricia", "Linda", "Barbara", "Elizabeth", "Jennifer", "Maria", "Susan", "Margaret", "Dorothy", "Lisa", "Nancy", "Karen", "Betty", "Helen", "Sandra", "Donna", "Carol", "Ruth");
    $surnames = array("Smith", "Jones", "Taylor", "Williams", "Brown", "Davies", "Evans", "Wilson", "Thomas", "Roberts", "Johnson", "Lewis", "Walker", "Robinson", "Wood", "Thompson", "White", "Watson", "Jackson");
    $frist_num = mt_rand(0, 19);
    $sur_num = mt_rand(0, 19);
    $type = rand(0, 1);
    if ($type == 0) return $male_names[$frist_num] . " " . $surnames[$sur_num];
    return $famale_names[$frist_num] . " " . $surnames[$sur_num];
}
