<?php
	class xmlgen {
		var $xml;
		var $ver;
		var $charset;
	 
		function xmlgen($ver='1.0',$charset='UTF-8') {
			$this->ver = $ver;
			$this->charset = $charset;
		}

		function generate($root,$data=array()) {
			$this->xml = new XmlWriter();
			$this->xml->openMemory();
			$this->xml->startDocument($this->ver,$this->charset);
			$this->xml->startElement($root);
			$this->write($this->xml, $data);
			$this->xml->endElement();
			$this->xml->endDocument();
			$xml = $this->xml->outputMemory(true);
			$this->xml->flush();
			return $xml;
		}
		 
		function write(XMLWriter $xml, $data) {
			foreach($data as $key => $value) {
				if(is_array($value)) {
					$xml->startElement($key);
					$this->write($xml,$value);
					$xml->endElement();
					continue;
				}
				$xml->writeElement($key,$value);
			}
		}
	}
?>
