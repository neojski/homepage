<? header( 'Content-type: text/html; charset=utf-8;' ); 
 if ($_GET['ajax']){
        $sc = curl_init('http://www.google.com/tbproxy/spell?lang=pl&hl=pl');
        
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        $txt = stripslashes(htmlspecialchars($_POST['txt']));
        
        function unichr($input) {
            $dec = hexdec( $input[1] );
            
            if ($dec < 128) { 
                $utf = chr($dec); 
            } elseif ($dec < 2048) { 
                $utf = chr(192 + (($dec - ($dec % 64)) / 64)); 
                $utf .= chr(128 + ($dec % 64)); 
            } else { 
                $utf = chr(224 + (($dec - ($dec % 4096)) / 4096)); 
                $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64)); 
                $utf .= chr(128 + ($dec % 64)); 
            }
            return $utf;
        }
        function encdec( $s ){
                return preg_replace_callback( '/&#x([a-f0-9]+);/iU', 'unichr', $s );
        }
        
        curl_setopt( $sc, CURLOPT_POST, TRUE );
        curl_setopt( $sc, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $sc, CURLOPT_POSTFIELDS, 
                '<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1"'
                        .' ignoreallcaps="1"><text>'.$txt.'</text></spellrequest>');
        $ret = encdec(curl_exec($sc)) ;
        
        $xml = xml_parser_create();
        xml_parse_into_struct( $xml, $ret, $vals );
        $deli = array();
        foreach ($vals as $val )
                if( $val['tag'] != 'C' ) continue;
                else printf( "%s:%s\t%s", $val['attributes']['O'],
                        $val['attributes']['L'],
                        str_replace("\t", " ", $val['value'])."\n"
                );
        die();
 }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<title>Testowanie AJAX handler</title>
<style type="text/css">
HTML{ width: 100%; }
BODY{ width: 500px; margin: 100px auto; }
.spellchecktxt{ overflow: auto;  }
.spellchecktxt,textarea{ height: 150px; width: 100%; border: 1px solid;
  margin: 0; white-space: pre-wrap; }
.del { color: red; font-weight: bold; cursor: pointer;}
span.dsug{ position: absolute; display: block; width: 100px;
  background-color: #eee; padding: .3em; border: 1px solid;}
span.dsug var{ display: block; cursor: pointer;}
</style>
<script type="text/javascript">
    function $() {
        var elements = new Array();

        for (var i = 0; i < arguments.length; i++) {
            var element = arguments[i];
            if (typeof element == 'string')
                element = document.getElementById(element);
            if (arguments.length == 1)
                return element;
            elements.push(element);
        }
        return elements;
    }

    var ajaxLink = window.XMLHttpRequest
        ? new XMLHttpRequest()
        : new ActiveXObject("Microsoft.XMLHTTP");
        
    var edt = true;     
        dtxt = null;
        dsug = null;
        suggest = Array();
        sprtxt = 'SprawdĹş';
        edttxt = 'Edytuj';
        waittxt = 'Czekaj...';
    function zmien(){
        document.getElementById('dsugspan').firstChild.data = this.firstChild.data;
    }

    function pokaz(){
        if( dsug ){
                dsug.parentNode.removeChild(dsug);
                dsug = null
        }
        dsug = document.createElement('span');
        dane = this.getAttribute('title').split(" ");
        
        dsug.style.top = (event.y+10) + 'px';
        dsug.style.left = event.x + 'px';
        dsug.className = 'dsug';
        
        this.setAttribute( 'id', 'dsugspan' );
        
        for( ii=0; ii < dane.length; ii++){
                v = document.createElement('var');
                v.appendChild( document.createTextNode( dane[ii] ) );
                v.onclick = zmien; 
                dsug.appendChild (v);
        }
        if( !dane.length ) dsug.appendChild( document.createTextNode( 'brak podpowiedzi' ) );
        //this.appendChild( dsug );
        document.body.appendChild( dsug );
    }
        
    function check( el, sender ){
        // wartoĹÄ formularza:
        el = $(el);
        
        var txt = encodeURIComponent( el.value );

        // IE hack. :o
        if( !window.XMLHttpRequest ) ajaxLink = new ActiveXObject("Microsoft.XMLHTTP");
        
        ajaxLink.onreadystatechange = function(){
            if( ajaxLink.readyState == '1' ){
                dtxt = document.createElement('div');
                dtxt.className = 'spellchecktxt';
                sender.value = waittxt;
            }
            if( ajaxLink.readyState == '4' ){
                el.style.display = 'none';
                el.parentNode.insertBefore( dtxt, el );
                data = ajaxLink.responseText.split( "\n" );
                seek = 0;
                data.pop();
                for( ii = 0; ii < data.length; ii++ ){
                        tmp = data[ii].split("\t");
                        tmp2 = tmp[0].split(':');
                                index = tmp2[0]; len = tmp2[1];
                        
                        dtxt.appendChild( document.createTextNode(
                                el.value.substring( seek, index ) ) );
                        seek = Number(index) + Number(len);
                        
                        span = document.createElement( 'span' );
                        span.className = 'del';
                        span.onclick = pokaz;
                        span.setAttribute('title', tmp[1] );
                        span.appendChild( document.createTextNode( 
                                el.value.substr( index, len ) ) );
                        dtxt.appendChild( span );
                }
                dtxt.appendChild( document.createTextNode( 
                        el.value.substring( seek, el.value.length ) ) );
                
                sender.value = edttxt;
                sender.disabled = false;
                el.readOnly = false;
            }
        }
        ajaxLink.open('post', '?ajax=1' );
        ajaxLink.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        ajaxLink.send( 'txt=' + txt );
        
        // zakĹadajÄc, Ĺźe sender = button, zmieĹmy jego napis
        edt = !edt;
        el.readOnly = true;
        sender.value = waittxt;
        sender.disabled = true;
    }
    
    function upd(el, sender){
        sender.value = sprtxt;
        el = $(el);
        el.value = '';
        for( ii=0; ii < dtxt.childNodes.length; ii++ ){
                cel = dtxt.childNodes[ii];
                el.value += (cel.firstChild ? cel.firstChild.data : cel.data );
        }
        el.style.display = 'block';
        dtxt.parentNode.removeChild( dtxt );
        if( dsug ) dsug.parentNode.removeChild( dsug );
        dtxt = null;
        edt = !edt;
    }
</script>
<textarea cols="60" rows="8" name="txt" id="txt"></textarea>
<input type="button" onclick="edt ? check('txt',this) : upd('txt',this)" value="sprawdĹş">
