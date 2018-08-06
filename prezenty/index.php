<!doctype html>
<meta charset="utf8" />
<style>
body{
  background:url('present.jpeg');
  color:#555150;
  font-size:1.5em
}
h1{
  color:#b30100
}
input,select{
  border-radius: 20px;
  font-size: 1em;
  padding: 20px;
  text-align: center;
  vertical-align: middle;
}
</style>

<?php

$presents = array(
  'Marysia' => '<ul><li>książka "Christian Dior" Marie France Pochna</li><li>książka "Maria Antonina cz. II W Wersalu i Petit Trianon oraz "Maria Antonina. cz.I Z Wiednia do Wersalu"</li><li>spiralkę do malowania rzęs koloru czarnego</li></ul>',
  'Janek' => '<ul><li>pianka do golenia</li><li>maszynki jednorazowe</li><li>woda po goleniu</li><li>skarpetki</li></ul>',
  'Ela' => '<ol><li>Perfumy</li><li>Piżama – XS</li><li>Bluza dresowa</li></ol>',
  'Zbyszek' => '<ol><li>Bluza dresowa ( szara,czarna,brązowa) – rozmiar L</li><li><a href="http://allegro.pl/logitech-m600-myszka-dotykowa-nowa-tanio-wroclaw-       i3784952112.html">Logitech M600</a> lub <a href="http://allegro.pl/mysz-logitech-t400-touch-mouse-wireless-dotykowa-i3796322170.html">Mysz Logitech T400</a></li><li>Koszulka termoaktywna - L</li></ol>',
  'Gosia' => '1/2 parowaru czarnego. Jeśli chcesz połączyć siły i spełnić marzenie wylosowanej osoby - skontaktuj się z Elą.',
  'Robert' => '1/2 parowaru czarnego. Jeśli chcesz połączyć siły i spełnić marzenie wylosowanej osoby - skontaktuj się z Olą',
  'Adam' => '1/2 <a href="http://www.ceneo.pl/8396012">sokowirówki</a>. Jeśli chcesz połączyć siły i spełnić marzenie wylosowanej osoby - skontaktuj się ze Zbyszkiem.',
  'Ola' => '1/2 <a href="http://www.ceneo.pl/8396012">sokowirówki</a> Jeśli chcesz połączyć siły i spełnić marzenie wylosowanej osoby - skontaktuj się z Robertem.',
  'Tomek' => '<ul><li><a href="http://allegro.pl/hit-nowy-starter-kit-z-arduino-uno-r3-i3797966301.html">Arduino</a></li><li>bielizna termoaktywna, tj. bluza/kalesony (M, ~174 wzrostu)</li></ul>	'
);
  
$all = array_keys($presents);
$n = count($all);

// same seed no longer gives the same results in php...
function random() {
  static $pseudo_rand = array(10, 20, 80, 79, 49, 6, 92, 95, 62, 59, 30, 76, 93, 50, 85, 5, 81, 2, 13, 42, 94, 2, 98, 68, 45, 73, 9, 43, 51, 30, 52, 3, 9, 65, 94, 58, 63, 79, 77, 76, 72, 84, 71, 6, 68, 27, 89, 76, 85, 89, 61, 88, 60, 13, 28, 80, 53, 28, 17, 98, 52, 71, 9, 65, 69, 74, 94, 25, 31, 61, 24, 71, 59, 59, 3, 37, 45, 95, 92, 92, 43, 27, 17, 38, 31, 64, 10, 16, 43, 96, 87, 18, 85, 94, 13, 31, 42, 26, 51, 8);  
  static $c = 0;
  $c = ($c + 1) % count($pseudo_rand);
  return $pseudo_rand[$c];
}

// generate random cycle
$shuffled = $all;
for ($i = 0; $i < $n; $i++) {
  $j = random() % $i;
  $tmp = $shuffled[$i];
  $shuffled[$i] = $shuffled[$j];
  $shuffled[$j] = $tmp;
}

// HACK: Robert should map to Adam so move Robert to 0 and Adam to 1
function swap(&$el1, &$el2) {
  $tmp = $el1;
  $el1 = $el2;
  $el2 = $tmp;
}
$robert_nr = array_search('Robert', $shuffled);
swap($shuffled[$robert_nr], $shuffled[0]);

$adam_nr = array_search('Adam', $shuffled);
swap($shuffled[$adam_nr], $shuffled[1]);
// HACK: end

$map = array();
for ($i = 0; $i < $n; $i++) {
  $map[$shuffled[$i]] = $shuffled[($i+1) % $n];
}

if (isset($_POST['name']) && strlen($_POST['name']) > 0) {
  $name = $map[$_POST['name']];
?>
  <h1>Będzie to: <?php echo $name;?>!</h1>
  <p>Sugerowane prezenty:
  <?php echo $presents[$name]; ?></p>
<?php
} else {  
?>

<h1>Losowanie prezentów</h1>
<p>Wybierz z formularza swoje imię i wylosuj komu sprawisz prezent.</p>
<form method="post">
  <select name="name">
    <option value="">Wybierz</option>
    <?php
      foreach($all as $name) {
        echo '<option value="' . $name . '">' . $name . '</option>';
      }
    ?>
  </select>
  <input type="submit" value="losuj!" />
</form>

<?php
}
?>