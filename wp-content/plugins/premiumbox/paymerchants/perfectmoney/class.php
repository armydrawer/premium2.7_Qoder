<?php

if (!class_exists('AP_PerfectMoney')) {
	class AP_PerfectMoney {
		
		private $m_name = "";
		private $m_id = "";
		private $iAccountID = "";
		private $sPassPhrase = "";
		
		public function __construct($m_name, $m_id, $iAccountID, $sPassPhrase) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->iAccountID = intval($iAccountID);
			$this->sPassPhrase = trim($sPassPhrase);
			
		}
		
		public function getHistory($sStartDate, $sEndDate, $key = 'batchid', $what = 'prihod') {
							 
			$date1 = explode('.', $sStartDate);
			$date2 = explode('.', $sEndDate);
			
			$sdata =  array( 
				'AccountID' => $this->iAccountID,
				'PassPhrase' => $this->sPassPhrase,
				'startday' => $date1[0] - 0,
				'startmonth' => $date1[1] - 0,
				'startyear' => $date1[2] - 0,
				'endday' => $date2[0] - 0,
				'endmonth' => $date2[1] - 0,
				'endyear' => $date2[2] - 0,
				// 'batchfilter' => $batch_id,
				// 'payment_id' => $payment_id			
			);
			if ('prihod' == $what) {
				$sdata['paymentsreceived'] = true;
			} else {
				$sdata['paymentsmade'] = true;
			}
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.com');
			$outs = explode("\n", $this->request('https://' . $perfetcmoney_domain . '/acct/historycsv.asp', $sdata));
			
			$data = array();
			$data['error'] = 1;
			if ('Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account,Payment ID,Memo' == trim($outs[0])) {
				$data['error'] = 0;
				foreach ($outs as $res) {
					$arr_data = explode(',', $res);
					if (count($arr_data) >= 9) {
						if ('batchid' == $key) {
							$now_key = $arr_data[2];
						} else {
							$now_key = $arr_data[8];
						}	
						$data['responce'][$now_key] = array(
							'date' => $arr_data[0],
							'type' => $arr_data[1],
							'batch' => $arr_data[2],
							'currency' => $arr_data[3],
							'amount' => $arr_data[4],
							'fee' => $arr_data[5],
							'receiver' => $arr_data[6],
							'sender' => $arr_data[7],
							'payment_id' => $arr_data[8],
						);
					}
				}
			} elseif ('No Records Found.' == trim($outs[0])) {
				$data['error'] = 0;
				$data['responce'] = array();			
			} 	
			
			return $data;
		}
		
		public function get_balance() {
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.com');
			$out = $this->request( 
				'https://' . $perfetcmoney_domain . '/acct/balance.asp', 
				array( 
					'AccountID' => $this->iAccountID,
					'PassPhrase' => $this->sPassPhrase,
				) 
			);
			
			$data = array();
			
			if (preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)) {
				foreach ($result as $val) {
					$data[$val[1]] = $val[2];
				}
			}	
			
			return $data;	
		
		}
		
		public function SendMoney($sender, $receiver, $amount, $item_id, $comment) {
			
			$data = array();
			$data['error'] = 1;
			$data['trans_id'] = 0;
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.com');
			$out = $this->request( 
				'https://' . $perfetcmoney_domain . '/acct/confirm.asp', 
				array( 
					'AccountID' => $this->iAccountID,
					'PassPhrase' => $this->sPassPhrase,
					'Payer_Account' => $sender,
					'Payee_Account' => $receiver,
					'Amount' => $amount,
					'PAYMENT_ID' => $item_id,
					'Memo' => $comment,		
				) 
			);	
			
			if (preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)) {
				$ndata = array();
				foreach ($result as $val) {
					$ndata[$val[1]] = $val[2];
				}		
				if (isset($ndata['PAYMENT_ID'], $ndata['PAYMENT_BATCH_NUM'])) {
					$data['error'] = 0;
					$data['trans_id'] = $ndata['PAYMENT_BATCH_NUM'];
				}
			}
			
			return $data;
		}

		public function CreateVaucher($sender, $amount) {
			
			$data = array();
			$data['error'] = 1;
			$data['trans_id'] = 0;
			$data['code'] = '';
			$data['num'] = '';
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.com');
			$out = $this->request( 
				'https://' . $perfetcmoney_domain . '/acct/ev_create.asp', 
				array( 
					'AccountID' => $this->iAccountID,
					'PassPhrase' => $this->sPassPhrase,
					'Payer_Account' => $sender,
					'Amount' => $amount,		
				) 
			);	
			
			if (preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)) {
				$ndata = array();
				foreach ($result as $val) {
					$ndata[$val[1]] = $val[2];
				}		
				if (isset($ndata['VOUCHER_NUM'], $ndata['PAYMENT_BATCH_NUM'], $ndata['VOUCHER_CODE'])) {
					$data['error'] = 0;
					$data['trans_id'] = $ndata['PAYMENT_BATCH_NUM'];
					$data['code'] = $ndata['VOUCHER_NUM'];
					$data['num'] = $ndata['VOUCHER_CODE'];
				}
			}
			
			return $data;
		}	
		
		function request($url, array $data = array()) {
			
			$json_data = http_build_query($data);
			
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
				$ch = apply_filters('curl_ap', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, '', $json_data, $result, $err);
				
				return $result;				 
			}			 
			
			return '';
		}
	}
}