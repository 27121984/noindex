<?php
set_time_limit(500);

class Eshop_Wordpress_Model_Cron
{
	public function constructBlog(){
		if ($blogData = $this->getCurlBlogContent()){
			$this->putContentInBlock($blogData);
		}
	}

	private function getCurlBlogContent(){
        try{
			$blogData = $this->CallAPI();
	        return $blogData;
        } catch (Exception $e) {
        	Mage::log($e->getMessage(), null, 'error-blog.log');
        	return false;
        }
	}

	private function putContentInBlock($blogData){

		$blockId  = Mage::getStoreConfig('wordpress_options/eshop_wordpress/block_id');

		$block = Mage::getModel('cms/block')->load($blockId);
		$blockIdentifier = $block->getIdentifier();

		$data = simplexml_import_dom($blogData);
		$html = $this->toHtml($data);

		$stores = array(0);
		foreach ($stores as $store){
			$block = Mage::getModel('cms/block');
			$block->load($blockIdentifier);
			$block->setStores(array($store));
			$block->setIsActive(1);
			$block->setContent( $html );
			$block->save();
		}
	}


	private function CallAPI(){
		$url  = Mage::getStoreConfig('wordpress_options/eshop_wordpress/rssfeed_url');
	    $curl = curl_init();

	    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	        'Content-Type: application/json',
	        'Accept: application/json'
	    ));

	    $result = curl_exec($curl);
	    $data = simplexml_load_string($result);

	    curl_close($curl);
	    return $data;
	}

	private function toHtml($allData){

		foreach ($allData as $value) {
			$itemList = $value->item;
		}
		$charLimit = Mage::getStoreConfig('wordpress_options/eshop_wordpress/description_char_limit');
		$html = '';
		$html .= '<h3 class="home-title">Artículos para Mamukys</h3>';
		$html .= '<div class="row blog-home">';

		for ($i=0; $i < 4; $i++) {
			$html .= '<div class="col-xs-12 col-sm-4 col-md-3">';
				$html .= '<a href="' . $itemList[$i]->link . '">';
					$html .= '<img class="lazy img-responsive" width="420" height="280" src="' . $itemList[$i]->image . '" style="display: block;">';
					$html .= '<div class="title">' . $itemList[$i]->title . '</div>';
					$formattedText = substr_replace($itemList[$i]->description,'',Mage::helper('eshop_wordpress')->getDescriptionCharLimit());
					$formattedText = substr_replace($formattedText,'[...]', strrpos($formattedText,' '));
					$html .= '<div class="description">' . $formattedText . '</div>';
				$html .= '</a>';
			$html .= '</div>';
		}
		$html .= '</div>';

		return $html;
	}
}
Blog  Support  Plans & pricing  Documentation  API  Site status  Version info  Terms of service  Privacy policy

