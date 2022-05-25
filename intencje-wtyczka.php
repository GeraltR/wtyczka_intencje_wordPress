<?php

/*
 Plugin Name: Intencje
 Description: Lista intencji
 Version: 1.2 Author: Paweł Mętelski

 */

class Intencje {
    private $wp_db;
    private $table_name;

    function intencje_add_menu(){
        add_menu_page('Strona główna wtyczki', 'Intencje Mszalne', 'administrator', 'intencje-glowna', array(&$this ,'intencje_wprowadzanie'), 'dashicons-admin-multisite', 24);
        add_action( 'intencje_drukowanie', 'print_intencje' );
        //add_options_page('Strona główna wtyczki', 'Intencje', 'administrator', 'intencje-ustawienia', array(&$this ,'print_intencje'), 'dashicons-admin-multisite', 24);
    }

    function intencje_add_footer(){
        echo '<br><p style="text-align: center">&copy 2020 Gosia &amp Geralt</p>';
        //echo '<br><p style="text-align: center">wersja php: </p>'.phpinfo();
//	echo '<br>'.get_template_directory_uri();
//        echo '<br>'.get_stylesheet_uri();
    }

    function intencje_style() {
        wp_enqueue_style( 'intencje-first-css', get_stylesheet_uri() );
        //wp_enqueue_style( 'intencje-second-css', get_template_directory_uri() . '/intencje.css');
    }

    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'intencje_wpisy';
        add_action('admin_menu', array(&$this, 'intencje_add_menu'));
        add_action('wp_footer', array(&$this, 'intencje_add_footer'),99);
        //add_action('wp_enqueue_scripts','intencje_style');
    }


    function intencje_wprowadzanie(){
        if(isset($_POST['intencje_action'])) {
            if($_POST['intencje_action'] == 'add') {
                if($this->add_wpis($_POST['intencje_dzien'], $_POST['intencje_opis_dnia'],$_POST['intencje_czy_swieto'],
                                   $_POST['intencje_msza'], $_POST['intencje_wpis'])) {
                    $notice = '<div class="notice notice-success">Dodano wiadomość o treści: ' . $_POST['intencje_wpis'] . '</div>';
                } else {
                    $notice = '<div class="notice notice-error">Nie dodano wiadomość o treści: ' . $_POST[0].$_POST[1] . '</div>';
                }
            } else if($_POST['intencje_action'] == 'edit') {
                if($this->edit_wpis($_POST['intencje_post_id'],$_POST['intencje_dzien'],$_POST['intencje_opis_dnia'],
                                    $_POST['intencje_czy_swieto'],$_POST['intencje_msza'],
                                    $_POST['intencje_wpis'])) {
                    $notice = '<div class="notice notice-success">Edytowano wiadomość o treści: ' . $_POST['intencje_wpis'] . '</div>';
                } else {
                    $notice = '<div class="notice notice-error">Nie udało się zaktualizować wiadomości o treści: ' . $_POST['intencje_wpis'] . '</div>';
                }
            }
        }
        if(isset($_POST['intencje_delete'])) {
            //usuwanie wiadomości
            if($this->delete_wpis($_POST['intencje_post_id'])) {
                $notice = '<div class="notice notice-success">Usunięto intencję id: ' . $_POST['intencje_post_id'] . '</div>';
            } else {
                $notice = '<div class="notice notice-error">Nie usunięto intencję o id: ' . $_POST['intencje_post_id'] . '</div>';
            }
        }

        //pobieram wiadomość do edycji
        $edit = FALSE;
        if(isset($_POST['intencje_to_edit'])) {
            $edit = $this->get_intencje_wpis($_POST['intencje_post_id']);
        }

        if(isset($_POST['intencje_dzien_print'])) {
              $dzienprint = $_POST['intencje_dzien_print'];
              $notice = '<div class="notice notice-success">Drukowanie intencji od ' . $dzienprint . '</div>';
        }else $dzienprint = 'dzisiaj';

        ?>
            <div class="wrap">
               <h2>Wprowadzanie intencji</h2>
               <p>Wypełnij pola i naciśnij przycisk Dodaj intencje</p>
               <?= isset($notice) ? $notice : ''; ?>
               <form method="POST">
               <h3>Kolejna intencja</h3>
               <?= $edit ? '<input type="hidden" name="intencje_post_id" value="' . $edit->id . '" />' : ''; ?>
               <input type="hidden" name="intencje_action" value="<?= $edit ? 'edit' : 'add'; ?>"/>
               <table class="form-table">
                 <tbody>
                   <tr>
                       <th scope="row">
                           <label for="dzien">Dzień</label>
                       </th>
                       <td>
                           <input type="date" id="dzien" name="intencje_dzien" value="<?= $edit ? $edit->dzien : ''; ?>" />
                           <input type="text" id="opis dnia" name="intencje_opis_dnia" value="<?= $edit ? $edit->opis_dnia : ''; ?>" />
                           <input type="checkbox" id="intencje_czy_swieto" name="intencje_czy_swieto"  value="1"
                                <?php if (isset($edit->czy_swieto)) if ($edit->czy_swieto == 1) echo "checked"; ?> />
                           <span>Niedziela/Święto</span><br>
                           <span class="description">Wybierz dzień</span>
                       </td>
                   </tr>
                   <tr>
                       <th scope="row">
                           <label for="godzina">Godzina</label>
                       </th>
                       <td>
                           <input type="time" id="godzina" name="intencje_msza" value="<?= $edit ? $edit->msza : ''; ?>"
                                  placeholder="Wprowadź godzinę"/></br>
                           <span class="description">Wprowadź godzinę</span>
                       </td>
                   </tr>
                   <tr>
                       <th scope="row">
                           <label for="intencje">Intencja</label>
                       </th>
                       <td>
                           <input type="text" id="intencje" name="intencje_wpis" value="<?= $edit ? $edit->wpis : ''; ?>"
                                  width="550"  placeholder="Wprowadź opis"  style="width: 500px;" /></br>
                           <span class="description">Wprowadź opis intencji</span>
                       </td>
                   </tr>

                   <tr>
                       <td colspan="2">
                           <input type="submit" value="<?= $edit ? 'Zapisz' : 'Dodaj'; ?> intencje" class="button-primary" />
                       </td>
                   </tr>
                   <tr>
                       <td>
                           <!--<input type="reset" value="Wyczyść dane"  class="button-secondary"/>
                           <span></span>-->

                       </td>
                       <td>

                       </td>
                   </tr>
                 </tbody>
               </table>
               </form>
               <?php
               echo '<form action="'.esc_url(plugins_url('intencje-wtyczka/print.php')).'" target="_blank">';
               //echo '<form action="http://localhost/blog/print.php" target="_blank">';
               ?>
                   <input type="date" id="dzien_print" name="intencje_dzien_print" value="" />

               <?php
                    $adres = esc_url(plugins_url('intencje-wtyczka/functions.php')).'?imie=pawel'.$dzienprint;
                    $adres = "'".$adres."'";
                    echo '<button class="button-secondary">Drukuj</button>'; //onClick="window.open('.$adres.')"
               ?>
               </form>
            </div>
            <div><hr>
            <?php
                $this->show_intencje_wprowadzanie();
            ?></div><?php
            $this->intencje_add_footer();
    }


    function add_wpis($dzien, $opis_dnia, $czy_swieto, $msza, $wpis) {
        $sql="select * from ".$this->table_name." where dzien='".$dzien."' ORDER BY id ASC LIMIT 1";
        $wynik=$this->wpdb->get_results($sql);
        if($wynik){
            $opis_dnia = $wynik[0]->opis_dnia;
            $czy_swieto = $wynik[0]->czy_swieto;
            $sql=" ";
        }
        if ($czy_swieto=="1") {
            $ics = 1;
        }
        else {
            $ics = 0;
        };
        if(trim($wpis) != ''){
            $user_id = get_current_user_id();
            $wpis = esc_sql($wpis);
            $this->wpdb->insert( $this->table_name,
                         array('user_id' => $user_id,
                               'dzien' => $dzien,
                               'czy_swieto' => $ics,
                               'msza' => $msza,
                               'wpis' => $wpis,
                               'opis_dnia' =>$opis_dnia) );
            if ($sql != " ") {
                $sql="update ".$this->table_name." set czy_swieto=".$ics.", opis_dnia='".$opis_dnia."' where id != ".$id." and dzien='".$dzien."'";
                $this->wpdb->check_current_query = false;
                $value= "";
                $this->wpdb->query($this->wpdb->prepare($sql, $value));
            }
            return TRUE;
        }
        return FALSE;
    }

    function edit_wpis($id, $dzien, $opis_dnia, $czy_swieto, $msza, $wpis){
        if(trim($wpis) != '') {
            $id = esc_sql($id);
            if ($czy_swieto=="1") {
                $ics = 1;
            }
            else {
                $ics = 0;
            };
            $wpis = esc_sql($wpis);
            $this->wpdb->update($this->table_name,
                                     array('dzien' => $dzien,
                                           'czy_swieto' => $ics,
                                           'msza' => $msza,
                                           'wpis' => $wpis,
                                           'opis_dnia' => $opis_dnia),
                                     array('id' => $id));
            $sql="update ".$this->table_name." set czy_swieto=".$ics.", opis_dnia='".$opis_dnia."' where id != ".$id." and dzien='".$dzien."'";
            $this->wpdb->check_current_query = false;
            $value= "";
            $this->wpdb->query($this->wpdb->prepare($sql, $value));
            return TRUE;
        }else {
            return FALSE;
        }
    }

    function delete_wpis($id) {
        $id = esc_sql($id);
        if(is_user_logged_in()) {
            return $this->wpdb->delete($this->table_name, array('id' => $id));
        } else {
            return FALSE;
        }
    }

    function get_intencje_wpis($id) {
        $id = esc_sql($id);
        $wynik = $this->wpdb->get_results("SELECT * FROM $this->table_name WHERE id = $id");
        if(isset($wynik)){
            $tmp = $this->get_intencje_opis_dnia($wynik[0]->dzien);
            $wynik[0]->opis_dnia=$tmp;
            $tmp = $this->get_intencje_czy_swieto($wynik[0]->dzien);
            if ($tmp == 1)
               $wynik[0]->czy_swieto = 1;
            else $wynik[0]->czy_swieto = 0;
            return $wynik[0];
        } else {
            return FALSE;
        }
    }
    function get_intencje_wszystkie($ileWierszy=100) {
        if ($ileWierszy==0){
           $limitWierszy="SELECT * FROM $this->table_name WHERE dzien BETWEEN CURRENT_DATE() AND  DATE_ADD(CURRENT_DATE(), INTERVAL 14 DAY) ORDER BY dzien ASC, msza ASC, id ASC";
        } else {
           $limitWierszy= "SELECT * FROM $this->table_name ORDER BY dzien DESC, msza ASC, id ASC LIMIT $ileWierszy";
        }
        if ((is_user_logged_in()) and ($ileWierszy > 0)) {
          echo 'obecnie wyświetlam: '.$ileWierszy.' najnowszych wierszy';
        }
        $intencje_wszystkie = $this->wpdb->get_results($limitWierszy);
        if(isset($intencje_wszystkie)){
            return $intencje_wszystkie;
        }
    }
    function show_intencje_wprowadzanie(){
        $styl_th = ' style="background-color:silver" ';
        $all_posts=$this->get_intencje_wszystkie();
        echo '<table>';
        echo '<tbody>';
        echo '<th'.$styl_th.'>Rodzaj</th><th'.$styl_th.'>Dzień</th><th'.$styl_th.'>Msza</th><th'.$styl_th.'>Opis święta</th><th'.$styl_th.'>Intencja</th>';
        $wiersz = 0;
        foreach ($all_posts as $p) {
            if ($wiersz==0){
                $styl_td= ' style="background-color:#E7E7E7" ';
                $wiersz=1;
            } else {
                $styl_td= ' style="background-color:#C9C9C9" ';
                $wiersz=0;
            }
            echo '<tr'.$styl_td.'>';
            if ($p->czy_swieto==1) $niedziela= 'niedziela/święto'; else $niedziela='';
            echo '<td style="width:90px">' . $niedziela . '</td>';
            echo '<td style="width:80px">' . date('d.m.Y', strtotime($p->dzien)) . '</td>';
            echo '<td style="width:60px">' . date('H:i', strtotime($p->msza)) . '</td>';
            echo '<td style="width:250px">' . $p->opis_dnia . '</td>';
            echo '<td style="width:380px">' . $p->wpis . '</td>';
            //echo '<td style="width:120px">' . date('d.m.Y H:i:s',strtotime($p->create_date)) . '</td>';
            echo '<td><form method="POST">
                                <input type="hidden" name="intencje_post_id" value="' . $p->id . '" />
                                <input type="submit" name="intencje_to_edit" value="Edytuj" class="button-primary" />
                                <input type="submit" name="intencje_delete" value="Usuń" class="button-primary error" />
                            </form></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    function get_intencje_opis_dnia ($ANaKiedy) {
        $zdanie = "SELECT opis_dnia FROM $this->table_name WHERE dzien = '".$ANaKiedy."' and LENGTH(opis_dnia) > 0 ORDER BY msza ASC, id ASC limit 1";
        return $this->wpdb->get_var($zdanie);
    }
    function get_intencje_czy_swieto ($ANaKiedy) {
        $zdanie = "SELECT czy_swieto FROM $this->table_name WHERE dzien = '".$ANaKiedy."' ORDER BY msza ASC, id ASC limit 1";
        $wynik = $this->wpdb->get_var($zdanie);
        return $wynik;
    }
    function show_intencje_html ($atytul){
         $dni = array('Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota','Niedziela');
         $old_dzien = '';
         $all_posts=$this->get_intencje_wszystkie(0);
         $wiersze='';

         foreach ($all_posts as $p) {
             $dzien = Date ('j.m', strtotime($p->dzien));
             $nazwadnia = $dni [Date('N', strtotime($p->dzien))-1];
             if ($old_dzien != $p->dzien) {
                $naglowek = '<td';
                if ($p->czy_swieto==1 && $p->opis_dnia){
                   $naglowek = $naglowek.' style="background-color:#7bdcb5" font-weight:"bold" font-size:"1.1" text-allign:"left" colspan="2"';
                }
                else
                if ($p->czy_swieto==1 && $p->opis_dnia.length < 1) {
                   $naglowek = $naglowek.' style="background-color:#f78da7" font-weight:"bold" font-size:"1.1" text-allign:"left" colspan="2"';
                }
                if ($p->czy_swieto!=1) {
                   $naglowek = $naglowek.' style="background-color:#8ed1fc" font-weight:"bold" font-size:"1.1" text-allign:"left" colspan="2"';
                }
                $naglowek = $naglowek.'>'.$nazwadnia.' '.$dzien.'<br>'.$p->opis_dnia.'</td>';
                $old_dzien = $p->dzien;
             }
             else { $naglowek = ''; }
             $msza = Date ('G:i', strtotime($p->msza));
             $wiersze = $wiersze.$naglowek.'<tr><td style="width:80px">'.$msza.'</td><td>'.$p->wpis.'</td></tr>';
         };
         $wynik = '<div> <p>'.$atytul.'</p> <table><tbody>'.$wiersze.'</tbody></table> </div>';
         return $wynik;
    }

    function print_intencje($adzienprint){

    }
}
$Intencje = new Intencje();

register_activation_hook(__FILE__, 'intencje_activation');

function intencje_activation() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'intencje_wpisy';

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") != $table_name) {
        $query = "CREATE TABLE " . $table_name . " (
        id int(9) NOT NULL AUTO_INCREMENT,
        user_id MEDIUMINT(6) NOT NULL,
        dzien DATE NOT NULL,
        msza TIME NOT NULL,
        wpis TEXT CHARACTER SET utf8 COLLATE utf8_bin,
        czy_swieto BOOL DEFAULT 0,
        opis_dnia CHAR(255) CHARACTER SET utf8 COLLATE utf8_bin,
        create_date TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
        )";
        $wpdb->query($query);
    }

}


function intencje_shortcode($atr){
    global $Intencje;
    return $Intencje->show_intencje_html('Lista nadchodzących intencji');
}
add_shortcode('Intencje_wywolanie', 'intencje_shortcode');

