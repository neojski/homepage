<?php header( 'Content-type: text/xml; charset=utf-8;' ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
  <head>
  <title></title>
  <style type="text/css">
  #divtxt, #t{
    border: 1px solid black;
    font-size: 18px;
    font-family:verdana, lucia;
    width: 600px;
    height: 200px;
  }
  .error{
    color:red;
  }
  #current{
    color:lightblue;
  }
  #suggestions{
    background:lightblue;
    
  }
  #suggestions var{
    display:block;
  }
  </style>
  <script type="text/javascript">
  var edit=0;
  function spell(button){
    edit=1;
    var textarea=document.getElementById('t');
    if(window.XMLHttpRequest){
      http_request = new XMLHttpRequest();
      if(http_request.overrideMimeType){
        http_request.overrideMimeType('text/xml');
      }
    }else if(window.ActiveXObject){
      try{
        http_request = new ActiveXObject("Msxml2.XMLHTTP");
      }catch(e){
        try{
          http_request = new ActiveXObject("Microsoft.XMLHTTP");
        }catch(e){}
      }
    }
    if(http_request){button.value='Czekaj';}
    http_request.onreadystatechange=function() {
      if(http_request.readyState == 4) {
        if(http_request.status == 200){
          var odpowiedz=http_request.responseXML;
          var divtxt=document.createElement('div');
          divtxt.id='divtxt'
          textarea.parentNode.insertBefore(divtxt,textarea);
          document.body.removeChild(textarea);
          var index=0;
          for(i=0;d=odpowiedz.getElementsByTagName('c')[i++];){          
            var offset=+d.getAttribute('o');
            var len=+d.getAttribute('l');
            divtxt.appendChild(document.createTextNode(textarea.value.substring(index, offset)));
            index=offset+len;
            spanerror=document.createElement('span');
            spanerror.appendChild(document.createTextNode(textarea.value.substr(offset,len)));
            spanerror.className='error';
            spanerror.suggestion=d.firstChild.data;
            spanerror.onclick=function(e){
              if(document.getElementById('suggestions')){
                return;
              }
              var suggestions=document.createElement('span');
              var sug=this.suggestion.split("\t");
              this.id='current';
              for(i=0;s=sug[i++];){
                one=document.createElement('var');
                one.appendChild(document.createTextNode(s));
                suggestions.appendChild(one);
                suggestions.id='suggestions';
                one.onclick=function(){
                  document.getElementById('current').firstChild.nodeValue=this.firstChild.nodeValue;
                  document.body.removeChild(this.parentNode);
                  document.getElementById('current').removeAttribute('class')
                  document.getElementById('current').removeAttribute('id')
                }
                
              }
              e=e||event;
              suggestions.style.position='absolute';
              suggestions.style.top=e.clientY+10+'px';
              suggestions.style.left=e.clientX+10+'px';
              document.body.appendChild(suggestions);
            }
            divtxt.appendChild(spanerror);
          }        
          divtxt.appendChild(document.createTextNode(textarea.value.substring(index, textarea.value.length)))
          button.value='Edytuj';
        }
      }
    }
    http_request.open('POST','check.php',true);
    http_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    http_request.send('tekst='+textarea.value);
  }
  function change(button){
    if(document.getElementById('current')){
      document.body.removeChild(document.getElementById('suggestions'))
    }
    button.value='Spell';
    var newvalue='';
    for(i=0;d=document.getElementById('divtxt').childNodes[i++];){
      newvalue+=d.firstChild?d.firstChild.nodeValue:d.nodeValue;
    }
    
    var textarea=document.createElement('textarea');
    textarea.id='t';
    textarea.value=newvalue;
    document.body.insertBefore(textarea,document.getElementById('divtxt'));
    document.body.removeChild(document.getElementById('divtxt'));
    edit=0;
  }
  </script>
  </head>
  <body>
    <textarea id="t"></textarea>
    <input id="check" type="button" onclick="edit?change(this):spell(this)" value="spell" />
  </body>
</html>
