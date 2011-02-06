<?php
	
	/**
	 * Usage:
	 * 	$api = new SMS('username', 'password');
	 *  $api->textMessage('Originator', array('number'), 'message');
	 */
	Class SMS {
		
		private $url = 'http://www.mollie.nl/xml/sms.xml';
		
		private $auth = array('username' => '', 'password' => '');
		
		public function __construct($username = false, $password = false) {

			if ($username)
				$this->auth['username'] = $username;
				
			if ($password)
				$this->auth['password'] = $password;
		}
		
		private function request($data) {
			
			$post = $data;
					
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			
			$return = curl_exec($curl);
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			curl_close($curl);
			
			$xml = simplexml_load_string($return);
			
			return $this->xml2array($xml);
		}
		
		function xml2array($xml) {
		
			$arXML=array();
			$arXML['name']=trim($xml->getName());
			$arXML['value']=trim((string)$xml);
			$t=array();
			foreach($xml->attributes() as $name => $value) $t[$name]=trim($value);
			$arXML['attr']=$t;
			$t=array();
			foreach($xml->children() as $name => $xmlchild) $t[$name]=$this->xml2array($xmlchild); 
			$arXML['children']=$t;
			return($arXML);
		}
		
		/**
		 * API functions
		 */
		public function textMessage($originator, $recipients, $message, $reference = false) {
			
			/**
			 * validate the arguments
			 */
			if (!$originator) {
				
				$this->error('No $originator');
			}
			if (!$recipients) {
				
				$this->error('No $recipients');
			}
			if (!$message) {
				
				$this->error('No $message');
			}
						
			$xml = '<?xml version="1.0" encoding="UTF-8" ?>';
			$xml .='<text_message>';
			$xml .='    <username>'.$this->auth['username'].'</username>';
			$xml .='    <password>'.$this->auth['password'].'</password>';
			$xml .='    <messages>';
			$xml .='        <message reference="'.$reference.'">';
			$xml .='            <originator>'.$originator.'</originator>';
			$xml .='            <recipients>';
								foreach ($recipients as $recipient) {
			$xml .='                <recipient>'.$recipient.'</recipient>';
								}
			$xml .='            </recipients>';
			$xml .='            <body>'.$message.'</body>';
			$xml .='        </message>';
			$xml .='    </messages>';
			$xml .='</text_message>';
			
			$this->request($xml);
		}
		
	}
	
?>