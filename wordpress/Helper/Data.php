<?php
class Eshop_Wordpress_Helper_Data extends Mage_Core_Helper_Abstract{
	Const DESCRIPTION_CHAR_LIMIT = 'wordpress_options/eshop_wordpress/description_char_limit';

	public function getDescriptionCharLimit(){
		return Mage::getStoreConfig(self::DESCRIPTION_CHAR_LIMIT);
	}
}
