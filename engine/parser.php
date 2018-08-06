<?php
/*************************
 * mój pierwszy parser
 * utworzono 18.04.2006
 * zmodyfikowano 22.04.2006
 * **********************/   
class engine{
    private $template;
    private $data;
    private $parsed;
    private $time;
    private $if_level=0;
    private $foreach_level=0;
    private $foreach_names=array();
    public $xhtml=1;
    
    /* ladowanie szablonu, konstruktor */
    public function __construct($template){
        $this->time=$this->gettime();//wystartuj zegar
        if(file_exists($template)){
            $this->template=file_get_contents($template);
        }else{
            $this->error('Plik szablonu '.$template.' nie istnieje');
        }
    }
        
    private function gettime(){
        list($usec,$sec)=explode(' ',microtime()); 
        return((float)$usec+(float)$sec); 
    }
    
    private function error($str){
        $file=fopen('engine_error.php','a');
        fwrite($file,"\n".$str);
        fclose($file);
        if(file_exists('error.tpl')){
            $str=str_replace('{$error}',$str,file_get_contents('error.tpl'));
        }
        die($str);
    }
    
    /* dodawanie blokow, nic ciekawego */
    public function assign($name,$value){
        $this->data[$name]=$value;
    } 
    
    /* parsowanie if */
    private function parse_if($str){   
        $name=$str[1];
        $str=$str[2];
        $str=preg_replace('#\$([a-zA-Z_0-9]+)#e','$this->parse_var(\'\1\',0)',$str);  
        if($name=='if'){
            $this->if_level++;
            return '<?php if('.$str.'){ ?>';
        }else{
            return '<?php }elseif('.$str.'){ ?>';
        }
    }
    
    /* parsowanie else */
    private function parse_else($name){
        $name=$name[1];
        if($name=='else'){
            return '<?php }else{ ?>';
        }else{
            $this->if_level--;
            return '<?php } ?>';
        }
    }
    
    /* parsowanie zmiennych */
    private function parse_var($name, $s=1){
        if(is_array($name)){
            $name=$name[1];
        }
        $name=explode('.',$name);        
        if(count($name)>1){
            $return='$this->data';
            for($i=0; $i<count($name)-1; $i++){
                $return.='[\''.$name[$i].'\'][$i_'.$name[$i].']';
            }
            $return.='[\''.$name[$i].'\']';
        }else{
            $return='$this->data[\''.$name[0].'\']';
        }
        if($s){
            return '<?php echo '.$return.' ?>';
        }else{
            return $return;
        }
    }
    

    
    /* parsowanie pętli */
    private function parse_foreach($name){
        $name=$name[1];
        if(strlen($name)>0){ 
            $name=explode('.',$name);
            $name=$name[count($name)-1];
            $this->foreach_names[$this->foreach_level]=$name;
            $this->foreach_level++; 
            $current=implode('.',$this->foreach_names);            
            return '<?php for($i_'.$name.'=0;$i_'.$name.'<count('.$this->parse_var($current, 0).');$i_'.$name.'++){ ?>';
        }else{
            array_pop($this->foreach_names);
            $this->foreach_level--;
            return '<?php } ?>';
        }
        
        
    }
    
    /* tworzenie zmiennych wewnatrz szablonu przez {var $zmienna="wartosc"} */
    private function new_var($str){
        $this->data[$str[1]]=$str[2];
        return '';
    }
    
    /* parsowanie, główna funkcja wywołująca całą zabawę */
    public function parse(){
        $this->parsed=$this->template;
        $this->parsed=preg_replace_callback('#(?:\s*){var \$([a-zA-Z_0-9]+)="(.*?)"}(?:\s*)#s',array($this,'new_var'),$this->parsed);
        $this->parsed=preg_replace_callback('#{(if|elseif) (.*)}#U',array($this,'parse_if'),$this->parsed);
        $this->parsed=preg_replace_callback('#{(else|/if)}#',array($this,'parse_else'),$this->parsed);
        $this->parsed=preg_replace_callback('#{\$(.*)}#U',array($this,'parse_var'),$this->parsed);
        $this->parsed=preg_replace_callback('#(?:{foreach \$(.*)}|{/foreach})#U',array($this,'parse_foreach'),$this->parsed);
    }
    
    /* wyswietlanie */
    public function display(){
        if(!$this->parsed){
            $this->parse();
        }
        $this->parsed='?>'.$this->parsed;
        /********************************
         *         master debug 
         ********************************/
        //echo '<code style="border:1px solid; display:block; white-space: pre">'.htmlspecialchars($this->parsed).'</code>';
        
        if($this->if_level!==0){
            die('Błędnie zagnieżdżone if-y');
        }
        if($this->foreach_level!==0){
            die('Błędnie zagnieżdżone foreach');
        }
        
        $time=$this->gettime()-$this->time;

        
        //ustawiamy kompresję gz
        ini_set('zlib.output_compression_level', 5);
        //buforuj
        ob_start();
        //parsuj       
        eval($this->parsed);
        //zapisz bufor do zmiennej
        $str=ob_get_contents();
        //wyczysc bufor
        ob_end_clean();
        //$str to cała treść strony
        //dodaj czas generowania
        
        //dodaj nagłówki xhtml-owe
        include('xhtml.php');
        if($this->xhtml){
            xhtml();
        }else{
            xhtml(0);
        }
        //echo str_replace('<body>','<body><p style="position:absolute; top:20px; right:20px; background:yellowgreen">'.$time.'</p>',$str);
        echo $str;
    }
}
?>
