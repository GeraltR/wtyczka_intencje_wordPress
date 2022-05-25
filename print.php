<?php
require_once "stale.php";

$od_dnia = $_GET['intencje_dzien_print'];

$tajne = new Tajne();

$mysql_host = 'localhost';
$mysql_login = $tajne->mysql_login;
$mysql_haslo = $tajne->mysql_haslo;
$mysql_baza = $tajne->mysql_baza;


$mysqli = new mysqli($mysql_host, $mysql_login, $mysql_haslo, $mysql_baza);


if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
else 'połączono';
mysqli_set_charset($mysqli, "utf8");

if ($result = $mysqli->query("SELECT * FROM wp_intencje_wpisy WHERE dzien BETWEEN '".$od_dnia."' AND  DATE_ADD('".$od_dnia."', INTERVAL 7 DAY) ORDER BY dzien ASC, msza ASC, id ASC")) {

    $tytul = 'Intencje mszalne w dniach od '.date('d.m.Y', strtotime($od_dnia));
    $dni = array('Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota','Niedziela');
    $old_dzien = '';
    $wiersze='';

    foreach ($result as $p) {
         $dzien = Date ('j.m', strtotime($p['dzien']));
         $nazwadnia = $dni [Date('N', strtotime($p['dzien']))-1];
         if ($old_dzien != $p['dzien']) {
            $naglowek = '<tr><td><br></td></tr><th';
            if ($p['czy_swieto']==1 && $p['opis_dnia']){
               $naglowek = $naglowek.' style=font-weight:"bold" font-size:"1.1" text-allign:"left" colspan="2" "padding: 15px"';
            }
            else
            if ($p['czy_swieto']==1 && strlen($p['opis_dnia']) < 1) {
               $naglowek = $naglowek.' style=font-weight:"bold" font-size:"1.1" text-allign:"left" colspan="2" "padding: 15px"';
            }
            if ($p['czy_swieto']!=1) {
               $naglowek = $naglowek.' style=font-weight:"bold" font-size:"1.1" text-allign:"left" colspan="2" "padding: 15px "';
            }
            $naglowek = $naglowek.'>'.$nazwadnia.' '.$dzien.'<br>'.$p['opis_dnia'].'</th>';
            $old_dzien = $p['dzien'];
         }
         else { $naglowek = ''; }
         $msza = Date ('G:i', strtotime($p['msza']));
         $wiersze = $wiersze.$naglowek.'<tr><td style="border: 1px solid black">'.$msza.'</td><td style="border: 1px solid black">'.$p['wpis'].'</td></tr>';
     };
     $wynik = '<div align="center"> <h3 style="text-align: center">'.$tytul.'</h3> <table><tbody>'.$wiersze.'</tbody></table> </div>';

    echo $wynik;
    $result->close();
}
else echo 'chyba jest pusto';

$mysqli->close();

?>
