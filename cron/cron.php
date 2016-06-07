<?php
set_time_limit(500);
class MGS_EshopExperian_Model_Cron{
	

	public function updateNewsletterSubs(){
		/* funcion creada para poder obtener los registros en el cual se haya hecho algun 
		tipo de modificacion y asi poder obtener datos actualizados
	  	 */
		$storeId = Mage::app()->getDefaultStoreView()->getStoreId();
		Mage::app()->setCurrentStore($storeId);
		$tienda = Mage::getStoreConfig('eshopexperian_options/eshopexperian_general/store_translate', $storeId);

		$dir = Mage::getBaseDir('var') . "/experian/subscribers";
		if (!is_dir($dir)) {
	        try {
	            mkdir($dir, 0777, true);
	        } catch (Exception $e) {
	            
	        } 
	    }
		$yesteday = date('Y-m-d 00:00:00',strtotime("-1 days"));
		$today = date('Y-m-d 00:00:00');

		$filename = '/'.$tienda.'-SubscribersExport-'.date('Y-m-d').'.csv';	             	
		$fp = fopen($dir.$filename, 'w');
		

		$sql = "SELECT * FROM newsletter_subscriber WHERE subscriber_status = 1 AND change_status_at > '".$yesteday."' AND change_status_at < '".$today."'";
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$subsCol = $connection->fetchAll($sql);
	
	
        $cabecera = array('email', 'id_tienda', 'fecha_alta','sexo','fecha_nacimiento','estado','fecha_baja','desea_info_comercial',
						  'nombre','apellidos','provincia','codigo_postal','telefono','last_update','md5','ref','actividad_tienda','envio_tienda'); 
        
        fputcsv($fp, $cabecera, ";"); 
        $row = array(); 

		foreach($subsCol as $sub){
			//exit(print_r($sub));
			if($sub['subscriber_status'] == 3): $baja = $sub['change_status_at']; endif; 
			$row = array($sub['subscriber_email'],$sub['store_id'],$sub['subscribe_date'],'','','0','',$sub['subscriber_status'],$baja,'','','','',$sub['change_status_at'],$sub['md5'],$sub['ref'],$sub['actividad_tienda'],$sub['envio_tienda']);
			fputcsv($fp, $row, ";");
			unset($row);
		}
		fclose($fp);
		$this->openFtpConnection($filename,'subscribers');
		
	}

	public function updateAllCustomers(){ 
		$storeId = Mage::app()->getDefaultStoreView()->getStoreId();
		Mage::app()->setCurrentStore($storeId);
		$tienda = Mage::getStoreConfig('eshopexperian_options/eshopexperian_general/store_translate', $storeId);

		$dir = Mage::getBaseDir('var') . "/experian/allcustomers";
		if (!is_dir($dir)) {
	        try {
	            mkdir($dir, 0777, true);
	        } catch (Exception $e) {
	            
	        } 
	    }

		$today = date("Y-m-d"); 
		$yesteday = date('Y-m-d',strtotime("-1 days"));

		$filename = '/'.$tienda.'-CustomersExport-'.date('Y-m-d').'.csv';	             	
		$fp = fopen($dir.$filename, 'w');

		$cabecera = array('email', 'id_tienda', 'fecha_alta','sexo','fecha_nacimiento','estado','fecha_baja','desea_info_comercial',
						  'nombre','apellidos','provincia','codigo_postal','telefono','last_update','md5','ref','external_source','internal_source','freq_news','actividad_tienda','envio_tienda');


		fputcsv($fp, $cabecera, ";");
        $row = array(); 

		$sql = "SELECT * FROM newsletter_subscriber WHERE change_status_at > '".$yesteday."' AND change_status_at < '".$today."'";
		
		
		//WHERE subscriber_status = 1 AND 
		
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$subsCol = $connection->fetchAll($sql);
		Mage::log(" query allCustomers", null, "miguel.log");
		Mage::log($subsCol, null, "miguel.log");

		foreach($subsCol as $sub){
			//exit(print_r($sub));
			if($sub['subscriber_status'] == 3): $baja = $sub['change_status_at']; else: $baja=""; endif;			
			
			$customerDataSql = "SELECT `e`.*, 
       `at_prefix`.`value` 
       AS `prefix`, 
       `at_firstname`.`value` 
       AS `firstname`, 
       `at_middlename`.`value` 
       AS `middlename`, 
       `at_lastname`.`value` 
       AS `lastname`, 
       `at_suffix`.`value` 
       AS `suffix`, 
       Concat(IF(at_prefix.value IS NOT NULL 
                 AND at_prefix.value != '', 
              Concat(Ltrim(Rtrim(at_prefix.value)), ' '), 
              ''), Ltrim(Rtrim(at_firstname.value)), ' ', IF( 
       at_middlename.value IS NOT NULL 
       AND 
       at_middlename.value != '', Concat(Ltrim(Rtrim(at_middlename.value)), ' ') 
                                                          , ''), 
       Ltrim(Rtrim(at_lastname.value)), IF(at_suffix.value IS NOT NULL 
       AND at_suffix.value != '', Concat(' ', Ltrim(Rtrim(at_suffix.value))), '' 
                                        )) AS 
       `name`, 
       `at_dob`.`value` 
       AS `dob`, 
       `at_default_billing`.`value` 
       AS `default_billing`, 
       `at_billing_prefix`.`value` 
       AS `billing_prefix`, 
       `at_billing_street`.`value` 
       AS `billing_street`, 
       `at_billing_postcode`.`value` 
       AS `billing_postcode`, 
       `at_billing_city`.`value` 
       AS `billing_city`, 
       `at_billing_telephone`.`value` 
       AS `billing_telephone`, 
       `at_billing_region`.`value` 
       AS `billing_region` 
FROM   `customer_entity` AS `e` 
       LEFT JOIN `customer_entity_varchar` AS `at_prefix` 
              ON ( `at_prefix`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_prefix`.`attribute_id` = '4' ) 
       LEFT JOIN `customer_entity_varchar` AS `at_firstname` 
              ON ( `at_firstname`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_firstname`.`attribute_id` = '5' ) 
       LEFT JOIN `customer_entity_varchar` AS `at_middlename` 
              ON ( `at_middlename`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_middlename`.`attribute_id` = '6' ) 
       LEFT JOIN `customer_entity_varchar` AS `at_lastname` 
              ON ( `at_lastname`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_lastname`.`attribute_id` = '7' ) 
       LEFT JOIN `customer_entity_varchar` AS `at_suffix` 
              ON ( `at_suffix`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_suffix`.`attribute_id` = '8' ) 
       LEFT JOIN `customer_entity_datetime` AS `at_dob` 
              ON ( `at_dob`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_dob`.`attribute_id` = '11' ) 
       LEFT JOIN `customer_entity_int` AS `at_default_billing` 
              ON ( `at_default_billing`.`entity_id` = `e`.`entity_id` ) 
                 AND ( `at_default_billing`.`attribute_id` = '13' ) 
       LEFT JOIN `customer_address_entity_varchar` AS `at_billing_prefix` 
              ON ( `at_billing_prefix`.`entity_id` = 
                   `at_default_billing`.`value` ) 
                 AND ( `at_billing_prefix`.`attribute_id` = '19' ) 
       LEFT JOIN `customer_address_entity_text` AS `at_billing_street` 
              ON ( `at_billing_street`.`entity_id` = 
                   `at_default_billing`.`value` ) 
                 AND ( `at_billing_street`.`attribute_id` = '25' ) 
       LEFT JOIN `customer_address_entity_varchar` AS `at_billing_postcode` 
              ON ( `at_billing_postcode`.`entity_id` = 
                   `at_default_billing`.`value` ) 
                 AND ( `at_billing_postcode`.`attribute_id` = '30' ) 
       LEFT JOIN `customer_address_entity_varchar` AS `at_billing_city` 
              ON ( `at_billing_city`.`entity_id` = 
                 `at_default_billing`.`value` ) 
                 AND ( `at_billing_city`.`attribute_id` = '26' ) 
       LEFT JOIN `customer_address_entity_varchar` AS `at_billing_telephone` 
              ON ( `at_billing_telephone`.`entity_id` = 
                   `at_default_billing`.`value` ) 
                 AND ( `at_billing_telephone`.`attribute_id` = '31' ) 
       LEFT JOIN `customer_address_entity_varchar` AS `at_billing_region` 
              ON ( `at_billing_region`.`entity_id` = 
                   `at_default_billing`.`value` ) 
                 AND ( `at_billing_region`.`attribute_id` = '28' ) 
WHERE  ( `e`.`entity_type_id` = '1' ) 
       AND ( `e`.`entity_id` = '".$sub['customer_id']."' )"; 
			
			$customerData = $connection->fetchAll($customerDataSql);

			if($customerData[0]['prefix'] == "Sr" or $customerData[0]['billing_prefix'] == "Sr."):
				$sexo = "0";
			elseif($customerData[0]['prefix'] == "Sra" or $customerData[0]['billing_prefix'] == "Sra."):
				$sexo = "1";
			else:
				$sexo = "2";
			endif;

			if($sub['subscriber_status'] == 1):
				$deseaInfo = 1;
			else:
				$deseaInfo = 0;
			endif;

			if($customerData[0]['dob'] != ""):
				$customerDob = date('Y-m-d',strtotime($customerData[0]['dob']));
			else:
				$customerDob = "";
			endif; 

			if($sub_name = $customerData[0]['firstname'] != ""):
				$sub_name = $customerData[0]['firstname'];
			else:
				$sub_name = $sub['subscriber_name'];
			endif;

			if($sub_lastname = $customerData[0]['lastname'] != ""):
				$sub_lastname = $customerData[0]['lastname'];				
			else:
				$sub_lastname = $sub['subscriber_lastname'];
			endif;

			$row = array($sub['subscriber_email'],
						$tienda,
						$sub['subscribe_date'],
						$sexo,
						$customerDob,
						$sub['subscriber_status'],
						$baja,
						$deseaInfo,
						$sub_name,
						$sub_lastname,
						$customerData[0]['billing_city'],
						$customerData[0]['billing_postcode'],
						$customerData[0]['billing_telephone'],
						$sub['change_status_at'],						
						$sub['md5'],
						$sub['ref'],
						$sub['external_source'],
						$sub['internal_source'],
						$sub['actividad_tienda'],
						$sub['envio_tienda'],
						$sub['freq_newsletter']);



			 
			fputcsv($fp, $row, ";");
			unset($row);
		}
		fclose($fp);
		$this->openFtpConnection($filename,'allcustomers');
	}

	/* 
		Función que envía por FTP los CSV
		Los parámetros del FTP se definen en el admin de Magento.
		$file, el nombre del fichero
		$mode, el tipo de fichero que es el CSV
	*/


private function openFtpConnection($file,$mode){ 
		$defStoreId = Mage::app()->getDefaultStoreView()->getStoreId();
		$host = Mage::getStoreConfig('eshopexperian_options/eshopexperian_general/experian_host', $defStoreId);
		$user = Mage::getStoreConfig('eshopexperian_options/eshopexperian_general/experian_user', $defStoreId);
		$pass = Mage::getStoreConfig('eshopexperian_options/eshopexperian_general/experian_pass', $defStoreId);
		$ruta = Mage::getStoreConfig('eshopexperian_options/eshopexperian_general/experian_route', $defStoreId);
		switch ($mode) {
			case 'customers':
				$dir = Mage::getBaseDir('var') . "/experian/customers";
				break;
			case 'orders':
				$dir = Mage::getBaseDir('var') . "/experian/orders";
				break;
			case 'products':
				$dir = Mage::getBaseDir('var') . "/experian/products";
				break;
			case 'orderlines':
				$dir = Mage::getBaseDir('var') . "/experian/orders/orderlines";
				break;
			case 'wishlist':
				$dir = Mage::getBaseDir('var') . "/experian/wishlist";
				break;
			case 'wishlistlines':
				$dir = Mage::getBaseDir('var') . "/experian/wishlist/wishlistlines";
				break;
			case 'subscribers':
				$dir = Mage::getBaseDir('var') . "/experian/subscribers";
				break;
			case 'abandoned':
				$dir = Mage::getBaseDir('var') . "/experian/abandoned";
				break;
			case 'abandonedlines':
				$dir = Mage::getBaseDir('var') . "/experian/abandoned/abandonedlines";
				break;
			case 'allcustomers':
				$dir = Mage::getBaseDir('var') . "/experian/allcustomers";
				break;	
			default:				
				break;
		}
		$localFile = $file;
		$ftp_file = $dir.DS.$localFile;
		$remote_file = $ruta.'/'.$file; 
		$conn_id = ftp_connect($host); 
		$login_result = ftp_login($conn_id, $user, $pass); 
		ftp_pasv($conn_id, true); 
		if (ftp_put($conn_id, $remote_file, $ftp_file, FTP_ASCII)) { 
		    echo "Éxito en el envío del fichero $file\n"; 
		} else { 
		    echo "Hubo un problema con el envío del fichero $file\n"; 
		    } 
		ftp_close($conn_id);
	}



	

	
	
}
