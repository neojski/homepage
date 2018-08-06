<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <title></title>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        </head>
    <body>
        <form enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF'];?>" method="post">
            <input type="file" name="file" />
            <input type="password" name="password" />
            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
            <input type="submit" name="submit" value="wyślij">
        </form>
        <?php 
        if(isset($_POST['submit']) && md5($_POST['password'])==='6867e6596cdff47cac6ff2b65a34c424'){//jeśli zatwierdzono formularz
         if($_FILES['file']['error']!=0){//jeśli napotkano błąd
          echo 'Nastąpił błąd przy wysyłaniu pliku';
         }else{
          if(is_uploaded_file($_FILES['file']['tmp_name'])){//jeśli wysłano plik
           move_uploaded_file($_FILES['file']['tmp_name'],'upload/'.$_FILES['file']['name'].time().'.txt');//przesuwamy go do odpowiedniego katalogu
           echo 'Wszystko przebiegło pomyślnie';
           //$dane=strip_tags(file_get_contents('upload/'.$_FILES['file']['name']));//usuwamy tagi php i html
           //$file=fopen('upload/'.$_FILES['file']['name'],'w');//otwieramy plik
           //fwrite($file,$dane);//zapisujemy go w zmienionej formie
          }else{
           echo 'Możliwy atak hakerski';
          }
         }
        }
        ?>
    </body> 
</html>
