<?php
/*
Plugin Name: en.Loadtr.com
Plugin URI: http://en.loadtr.com/
Description: En.loadtr.com  wordpress image hosting plugin, keep your bandwidth.This plugin host your images on en.loadtr.com  and creats 5 different  size of your images.You can use which you want. We recommended Medium size (500x400). Thanks for using.

Version: 1.0
Author: Hakan Demiray
Author URI: http://www.dmry.net/
*/ 


class LoadTr{
	var $eklenti_url = '';
	
    function LoadTr() {
        load_plugin_textdomain('loadtr', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));
        $this->eklenti_url = get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
        add_action('media_buttons', array($this, 'loadtr_icon'), 20);
        add_action('media_upload_loadtr', array($this, 'media_upload_loadtr'));
		add_action('activate_loadtr/loadtr.php', array($this, 'loadtr_aktif'));
		add_action('deactivate_loadtr/loadtr.php', array($this, 'loadtr_pasif'));
    }
	
	function loadtr_aktif() {
		$loadtr_eklenti = array('surum'=>'1.0');
		update_option('loadtr_eklenti', $loadtr_eklenti);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://img1.loadtr.com/rsapi.php');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"eklenti=ac&site=". get_option('siteurl'));
		$result= curl_exec ($ch);
		curl_close ($ch);
		
	}
	
	function loadtr_pasif() {
		ob_start();
		delete_option('loadtr_eklenti');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://img1.loadtr.com/rsapi.php');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"eklenti=kapat&site=". get_option('siteurl'));
		$result= curl_exec ($ch);
		curl_close ($ch);		
	}	

	
	function loadtr_icon($icons) {
		global $post_ID, $temp_ID;
		$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
		
		$loadtr_icon = $this->eklenti_url.'/images/ico.loadtr.gif';
		$media_loadtr_title = __('Loadtr Image Upload', 'loadtr');

		$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
		$media_loadtr_iframe_src = apply_filters('media_loadtr_iframe_src', "$media_upload_iframe_src&amp;type=loadtr&amp;tab=loadtr");
		
		echo "<a href=\"{$media_loadtr_iframe_src}&amp;TB_iframe=true&amp;height=500&amp;width=640\" class=\"thickbox\" title='$media_loadtr_title'><img src='$loadtr_icon' alt='$media_loadtr_title' /></a>";
	}
	


	
	
    function media_upload_loadtr() {
		wp_register_script('jquery-1.3.2', $this->eklenti_url.'/js/jquery-1.3.2.min.js');
		wp_register_script('loadtr_js', $this->eklenti_url.'/js/loadtr.js');
		
		wp_enqueue_script('jquery-1.3.2');
		wp_enqueue_script('loadtr_js');
		wp_enqueue_style( 'media' );
        wp_iframe(array($this, 'media_upload_type_loadtr'));
    }	

    function loadtr_media_tablar($tabs) {
        return array(
			'loadtr' =>  __('Loadtr Photo', 'loadtr')
        );
    }

	function media_upload_type_loadtr() {
		global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types, $loadtr;
		
		add_filter('media_upload_tabs', array($loadtr, 'loadtr_media_tablar'));
		
		media_upload_header();
		
		if($_POST) {
		
			if ($_FILES['userfile']['name']=='') $var_hata = 2;
			if (!isset($var_hata) && empty($_POST['boyut'])) $var_hata = 5;
			if (!isset($var_hata) && empty($_POST['site'])) $var_hata = 6;
			if (!isset($var_hata) && empty($_POST['tags'])) $var_hata = 1;
			
			
			$__loadtr_dil_hata_[7] =__('Image upload error. Temp folder is not writable', 'loadtr');
			$__loadtr_dil_hata_[1] =__('Description is empty', 'loadtr');
			$__loadtr_dil_hata_[2] =__('Image not select', 'loadtr');
			$__loadtr_dil_hata_[3] =__('Image is corrupt', 'loadtr');
			$__loadtr_dil_hata_[4] =__('Invalid extension', 'loadtr');
			$__loadtr_dil_hata_[5] =__('Size is empty', 'loadtr');
			$__loadtr_dil_hata_[6] =__('URL is empty', 'loadtr');
			$__loadtr_dil_hata_[7] =__('Unknown error', 'loadtr');
			$__loadtr_dil_hata_[8] =__('Loadtr.com connection error', 'loadtr');

			if (!isset($var_hata)) {
				$uploaddir = WP_PLUGIN_DIR . '/'. dirname(plugin_basename(__FILE__)) .'/temp/';
				$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
			
				if (@move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
					
					$objCurlFileUploader = new CurlFileUploader($uploadfile, "http://img1.loadtr.com/rsapi.php",'userfile', Array('tags' => $_POST['tags'], 'dilkod'=>$_POST['dilkod'], 'boyut'=>$_POST['boyut'], 'site'=>$_POST['site']));
					$sonuc =  $objCurlFileUploader->UploadFile();
					
					preg_match('#<hata>(.*?)</hata>#si',$sonuc,$hata_kod);
					$hata_kod = $hata_kod[1];
					if($hata_kod == 0) {
						preg_match('#<image>(.*?)</image>#si',$sonuc,$image);
						preg_match('#<url>(.*?)</url>#si',$sonuc,$url);
						$image = $image[1];
						$url = $url[1];
						$var_hata = 0;
						unlink($uploadfile);
					} else {
						$var_hata = $hata_kod;
					}
				} else {
					$var_hata = 7;
				}
			}
		}
		
?>
<form enctype="multipart/form-data" method="post" action="" class="media-upload-form type-form validate" id="image-form" style="margin-left:15px;">
<input type="hidden" name="site" id="site" value="<?php echo get_option('siteurl'); ?>" />
<h3 class="media-title"><?php _e('Loadtr.com Image Upload', 'loadtr'); ?></h3>
<div id="media-items">
<div class="media-item media-blank">
<style type="text/css">
.loadtrform{ font-family:Verdana, Geneva, sans-serif; font-size:12px; width:600px; padding:10px;}
.loadtrform label, .loadtrform .input{display:block; float:left;height:30px;}
.loadtrform label{clear:both; width:100px; }
.loadtrform label{padding:3px 5px;}
.loadtrform .input .tput{font-family:Verdana, Geneva, sans-serif; font-size:12px; width:300px; padding:3px 5px;}
.loadtrform .s2{ height:100px;}
.loadtrform ul{list-style:none; margin:0; padding:0; font-size:11px;}
.loadtrform ul li{display:block; float:left; width:90px; text-align:center;}
.clear{clear:both;}
.loadtrform .yukle{text-align:center;}
.loadtrform .bilgi{font-size:11px; color:#666666; margin-top:10px;}
.loadtrform .yukleme{font-size:11px; color:#666666; margin-top:10px; font-weight:bold;}
.loadtrform .yukleme .hata{ background:url(<?php echo $this->eklenti_url; ?>/images/hata.gif) no-repeat left; padding:15px 0 0 35px; font-size:14px; font-weight:bold; color:#C00; height:35px; width:400px; margin:0 auto;}
.loadtrform .yukleniyor{ text-align:center; font-size:14px; font-weight:bold;}
.loadtrform .yaziya{ text-align:center;}
</style>
<script type="text/javascript">
function yaziya_yolla(resim,url) {
	var p__html = '<a href="'+ url +'" target="_blank"><img src="'+ resim +'" border="0"></a>';
    top.send_to_editor(p__html);
    top.tb_remove();
}

var __loadtr_eklenti_url='<?php echo $this->eklenti_url; ?>';
var __loadtr_dil_1 = '<?php _e('Type keywords/description about your image...', 'loadtr'); ?>';
var __loadtr_dil_2 = '<?php _e('Description is empty', 'loadtr'); ?>';
var __loadtr_dil_3 = '<?php _e('Image not select', 'loadtr'); ?>';
var __loadtr_dil_4 = '<?php _e('Size is empty', 'loadtr'); ?>';
var __loadtr_dil_5 = '<?php _e('Language code not selected', 'loadtr'); ?>';
var __loadtr_dil_6 = '<?php _e('Uploading, please wait...', 'loadtr'); ?>';
</script>
<div class="loadtrform">

	<label for="userfile"><?php _e('Image', 'loadtr'); ?></label> <div class="input">: <input type="file" name="userfile" id="userfile" class="tput" size="42" />
    <select name="dilkod" id="dilkod">
    	<option value=""><?php _e('Language', 'loadtr'); ?></option>
        <option value="tr"><?php _e('Türkçe', 'loadtr'); ?></option>
        <option value="en"><?php _e('English', 'loadtr'); ?></option>
    </select>
    </div>
    <label for="tags"><?php _e('Description', 'loadtr'); ?></label> <div class="input">: <input type="text" name="tags" id="tags" value="" class="tput" onclick="if(this.value==__loadtr_dil_1) this.value='';" /></div>
    
    <label for="b1" class="s2"><?php _e('Select Size', 'loadtr'); ?></label> 
    <div class="input s2">:
<ul>
        	<li><label for="b1"><?php _e('Thumbnail', 'loadtr'); ?><br />120x96<br /><input type="radio" name="boyut" id="b1" value="k" class="res_size" /></label></li>
            <li><label for="b2"><?php _e('Small', 'loadtr'); ?><br />240x192<br /><input type="radio" name="boyut" id="b2" value="s" class="res_size" /></label></li>
            <li><label for="b3"><?php _e('Medium', 'loadtr'); ?><br />500x400<br /><input type="radio" name="boyut" id="b3" value="b" class="res_size" /></label></li>
            <li><label for="b4"><?php _e('Large', 'loadtr'); ?><br />1024x820<br /><input type="radio" name="boyut" id="b4" value="l" class="res_size" /></label></li>
            <li><label for="b5"><?php _e('Original Size', 'loadtr'); ?><br />-<br /><input type="radio" name="boyut" id="b5" value="o" class="res_size" /></label></li>
        </ul>
    </div>
     <div class="clear"></div>  
     <div class="yukle clear"><input type="button" value="<?php _e('UPLOAD', 'loadtr'); ?>" id="btn_yukle" /></div> 
     <div class="bilgi clear"><?php _e('<strong>Attention</strong> Adult image (+13) and illegal image upload is Disallow', 'loadtr'); ?></div>
     
     <div class="yukleme clear">
     <?php
	 if (isset($var_hata) && $var_hata > 0 ) {
	 	echo '<div class="hata">'.$__loadtr_dil_hata_[$var_hata].'</div>';
	 } else if (isset($var_hata) && $var_hata == 0 ) {
?>		 
	<div class="yaziya"><img src="<?php echo $image; ?>" /><br /><input type='button' class='button' name='send' value='<?php _e('Send to Post', 'loadtr'); ?>' onclick="yaziya_yolla('<?php echo $image; ?>','<?php echo $url; ?>')" id="btn_yukle" /></div>

<?php		 
	 }
	 ?>
     
     </div> 
     
     
     
</div>
</div>
</div>
</form>

<?php
	}
}



/**************************************************************************************
Author:  Abhishek Kumar Srivastava
Email:     abhisheksrivastava@fastmail.fm
Purpose: The class is written for uploading file using curl
Liscense: GNU GPL
**************************************************************************************/

class CurlFileUploader {
	var $filePath;
	var $uploadURL;
	var $formFileVariableName;
	var $postParams = array();
	
	/* Constructor for CurlFileUploader
	* @param $filePath absolute path of file
	* @param $uploadURL url where you want to upload file
	* @param $formFileVariableName form field name to upload file
	* @param $otherParams assosiative array of other params which you want to send as post
	*/ 
	function CurlFileUploader ($filePath, $uploadURL, $formFileVariableName, /* assosiative array */ $otherParams = false) {
		$this->filePath = $filePath;
		$this->uploadURL = $uploadURL;
		if(is_array($otherParams) && $otherParams != false) {
			foreach ($otherParams as $fieldName => $fieldValue) {
				$this->postParams[$fieldName] = $fieldValue;
			}
		}
		$this->postParams[$formFileVariableName] = "@".$filePath;
		
	}
	
	/*
	* function to upload file
	* if unable to upload file produce error and exit
	* else upload file
	*/
	function UploadFile () {
   		$ch = curl_init();
   		curl_setopt($ch, CURLOPT_URL, $this->uploadURL );
   		curl_setopt($ch, CURLOPT_POST, 1 );
   		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postParams);
   		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   		$postResult = curl_exec($ch);

   		if (curl_errno($ch)) {
       		return '{"hatakod":8}';
       		exit();
   		} else {
			return $postResult;
		}
   		curl_close($ch);
		exit();
	}
}

$loadtr = new LoadTr;
?>