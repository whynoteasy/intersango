<?php
require_once 'util.php';
require_once 'view_util.php';
require_once 'errors.php';
require_once 'openid.php';

echo "<div class='content_box'>\n";
echo "<h3>Recent Trades</h3>\n";

$query = "
    SELECT txid,
           a_amount,
           b_amount,
           " . sql_format_date("t.timest") . " AS timest,
           a.uid AS a_uid,
           b.uid AS b_uid
    FROM transactions AS t
    JOIN orderbook AS a
    ON a.orderid = a_orderid
    JOIN orderbook AS b
    ON b.orderid = b_orderid
    WHERE b_amount > 0
          AND t.timest > NOW() - INTERVAL 1 DAY
    ORDER BY txid;
";
$result = do_query($query);
$first = true;
$amount_aud_total = $amount_btc_total = '0';
while ($row = mysql_fetch_assoc($result)) {
    if ($first) {
        $first = false;
        echo "<table class='display_data'>\n";
        echo "<tr>";
        echo "<th>TID</th>";
        echo "<th>AUD</th>";
        echo "<th>BTC</th>";
        echo "<th>Price</th>";
        echo "<th>Date</th>";
        echo "</tr>";
    }
    
    $txid = $row['txid'];
    $a_amount = $row['a_amount'];
    $b_amount = $row['b_amount'];
    $timest = $row['timest'];
    $a_uid = $row['a_uid'];
    $b_uid = $row['b_uid'];
    $price = bcdiv($a_amount, $b_amount, 4);

    $amount_aud_total = gmp_add($amount_aud_total, $a_amount);
    $amount_btc_total = gmp_add($amount_btc_total, $b_amount);

    echo "<tr>";
    echo "<td>$txid</td>";
    echo "<td>", internal_to_numstr($a_amount,4), "</td>";
    echo "<td>", internal_to_numstr($b_amount,4), "</td>";
    echo "<td>$price</td>";
    echo "<td>$timest</td>";
    echo "</tr>\n";
}

if ($first)
    echo "<p>There are no recent trades.</p>\n";
else {
    $price = bcdiv(gmp_strval($amount_aud_total), gmp_strval($amount_btc_total), 4);
    echo "    <tr>\n";
    echo "        <td></td><td>--------</td><td>--------</td><td>--------</td>\n";
    echo "    </tr>\n";
    echo "    <tr>\n";
    echo "        <td></td>";
    echo "        <td>", internal_to_numstr($amount_aud_total,4), "</td>";
    echo "        <td>", internal_to_numstr($amount_btc_total,4), "</td>";
    echo "        <td>$price</td>";
    echo "    </tr>\n";
    echo "</table>\n";
}

?>
</div>
