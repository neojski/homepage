<?php
/*
 *	class upload
 *	easy thumb creator
 *
 *	Copyright :(C) 2007 Tomasz Kołodziejski
 *
 *	This library is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU Lesser General Public
 *	License as published by the Free Software Foundation; either
 *	version 2.1 of the License, or (at your option) any later version.
 *	
 *	This library is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *	Lesser General Public License for more details.
 *
 *	You should have received a copy of the GNU Lesser General Public
 *	License along with this library; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
class upload{

	private $types=array('image/jpeg', 'image/gif', 'image/png');
	private $upload_dir='images';
	private $permissions=0777;
	private $defaultWidth=100;
	private $defaultHeight=100;
	private $error=false;
	
	public function save($file){
		if(isset($_FILES[$file])){
			$file=$_FILES[$file];
			if($file['error']==0){//jeśli napotkano błąd
				if(is_uploaded_file($file['tmp_name'])){//jeśli wysłano plik
					if(in_array($file['type'], $this->types)){
						$this->name=$file['name'];
						$this->type=$file['type'];
						$this->filename=$this->upload_dir.'/'.$file['name'];
						move_uploaded_file($file['tmp_name'], $this->filename);//przesuwamy go do odpowiedniego katalogu
						chmod($this->filename, $this->permissions);
						
						return true;
					}else{
						echo 'niepoprawny mime - <code>'.$file['type'];
						return false;
					}
				}else{
					// Możliwy atak hakerski
					$this->error=true;
					return false;
				}
			}else{
				// błąd
				$this->error=true;
				return false;
			}
		}else{
			// nie wysłano obrazka
			return false;
		}
	}
	
	public function thumb($prefix, $width=0, $height=0){
		if(!$this->error){
			
			// = directory + filename
			$filename = $this->filename;
			
			if($width==0){
				$width=$this->defaultwidth;
			}
			if($height==0){
				$height=$this->defaultheight;
			}
		
	
			// check imagetype
			$type=exif_imagetype($filename);
			
			// you can use various type
			switch($type){
				case IMAGETYPE_GIF:
					$image=imagecreatefromgif($filename);
					break;
				case IMAGETYPE_JPEG:
					$image=imagecreatefromjpeg($filename);
					break;
				case IMAGETYPE_PNG:
					$image=imagecreatefrompng($filename);
					break;
			}
			
			// Get new dimensions
			list($width_orig, $height_orig) = getimagesize($filename);
			
			$ratio_orig = $width_orig/$height_orig;
			
			if ($width/$height > $ratio_orig) {
				$width = $height*$ratio_orig;
			} else {
				$height = $width/$ratio_orig;
			}
			
			// create new image
			$image_p = imagecreatetruecolor($width, $height);
			
			// fill the new image white
			$white = imagecolorallocate($image_p, 0xff, 0xff, 0xff);
			imagefill($image_p, 0, 0, $white);
			
			// resize
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			
			// new_filename = directory + prefix + filename
			$new_filename= $this->upload_dir. '/' .$prefix . $this->name;
			
			// save image
			switch($type){
				case IMAGETYPE_GIF:
					imagegif($image_p, $new_filename);
					break;
				case IMAGETYPE_JPEG:
					imagejpeg($image_p, $new_filename);
					break;
				case IMAGETYPE_PNG:
					imagepng($image_p, $new_filename);
					break;
			}
			
			// change permission
			chmod($new_filename, $this->permissions);
		}
	}
}

?>