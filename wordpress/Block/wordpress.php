<?php
class Eshop_Wordpress_Block_Wordpress extends Mage_Core_Block_Template{
	public $blockDataModel;

	public function getBlockData(){
		$blockDataModel = Mage::getModel("eshop_wordpress/wordpress");
		return $blockDataModel->getRssData();
	}

	public function limitText($text){
		//Sacamos la cadena limitada
		$formattedText = substr_replace($text,'',Mage::helper('eshop_wordpress')->getDescriptionCharLimit());
		$formattedText = substr_replace($formattedText,'[...]', strrpos($formattedText,' '));
		//$formattedText = substr_replace($text,'',70);
		return $formattedText;
	}

    public function getBlockDataModel(){
        return $this->blockDataModel;
    }

    public function setBlockDataModel($blockDataModel){
        $this->blockDataModel = $blockDataModel;
        return $this;
    }
}
