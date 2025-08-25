<?php

if (!class_exists('NixMoney')) {
	class NixMoney {
		
		private $m_name = "";
		private $m_id = "";
		private $accountid;
		private $passphrase;
		
		function __construct($m_name, $m_id, $accountid, $passphrase) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->accountid = trim($accountid);
			$this->passphrase = trim($passphrase);
			
		}
		
		public function getHistory($sStartDate, $sEndDate, $key = 'batchid', $what = 'prihod') {
			
			$date1 = explode('.', $sStartDate);
			$date2 = explode('.', $sEndDate);
			
			$sdata =  array( 
				'ACCOUNTID' => $this->accountid,
				'PASSPHRASE' => $this->passphrase,
				'STARTDAY' => $date1[0] - 0,
				'STARTMONTH' => $date1[1] - 0,
				'STARTYEAR' => $date1[2] - 0,
				'ENDDAY' => $date2[0] - 0,
				'ENDMONTH' => $date2[1] - 0,
				'ENDYEAR' => $date2[2] - 0,
				// 'BATCHFILTER' => $batch_id,
				// 'PAYMENT_ID' => $payment_id			
			);
			if ('prihod' == $what) {
				$sdata['PAYMENTSRECEIVED'] = true;
			} else {
				$sdata['PAYMENTSMADE'] = true;
			}
			
			$result = $this->request('https://www.nixmoney.com/history', $sdata);
			
			$outs = explode("\n", $result);
			
			$data = array();
			$data['error'] = 1;
			if ('Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account,Payment ID,Memo' == trim($outs[0])) {
				$data['error'] = 0;
				foreach ($outs as $res) {
					$res = trim($res);
					$arr_data = explode(',', $res);
					if (count($arr_data) >= 9) {
						if ('batchid' == $key) {
							$now_key = (int)$arr_data[0];
						} else {
							$now_key = (int)$arr_data[8];
						}	
						$data['responce'][$now_key] = array(
							'date' => $arr_data[1],
							'type' => $arr_data[2],
							'batch' => $arr_data[0],
							'currency' => $arr_data[3],
							'amount' => $arr_data[4],
							'fee' => $arr_data[5],
							'receiver' => $arr_data[7],
							'sender' => $arr_data[6],
							'payment_id' => intval($arr_data[8]),
						);
					}
				}
			} elseif ('No Records Found.' == trim($outs[0])) {
				$data['error'] = 0;
				$data['responce'] = array();
			}
			
			return $data;
		}		
		
		function request($url, array $data = array()) {
			
			$json_data = http_build_query($data);
			
			$headers = array();			
		
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				$ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
		
				return $result;				 
			}				
			
			return '';
		}
	}
}