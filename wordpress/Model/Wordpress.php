<?php
class Eshop_Wordpress_Model_Wordpress extends Mage_Core_Model_Abstract{
    public function getRssData(){
        $url = Mage::getStoreConfig('wordpress_options/eshop_wordpress/rssfeed_url');
        try{
	        $rssUrl = simplexml_load_file($url);
	        $rssObj = $rssUrl->channel->item;
	        return $rssObj;
        } catch (Exception $e) {
        	Mage::log($e->getMessage(), null, 'error-blog.log');
        	return false;
        }
    }

}
