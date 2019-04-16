<?php
	class telephony 
	{
		public $conn_primary;
		public $conn_secondary;
        public $conn_local;
		public function __construct($dbarr){
                $this->conn_local = new DB($dbarr['LOCAL']);
                if($this->getserverstatus() == 1){
                    $this->conn_primary= new DB($dbarr['AVAHAN_P']);
                    $this->conn_secondary= new DB($dbarr['AVAHAN_S']);
                }
		}

		public function getExtStatus($extensionno, $server) {
            $count_ext=0;
			//$sql = "SELECT COUNT(*) as cnt FROM sysactiveextensions WHERE txtextension='".$extensionno."';";
			
			$sql = "SELECT COUNT(*) as cnt FROM interdialog.vlog_channel_configuration WHERE channel_login_agent_terminal ='".$extensionno."'";
			if($server=='P') {
				$res =$this->conn_primary->query_sql($sql);
			} else if ($server=='S'){
				$res = $this->conn_secondary->query_sql($sql);
			}
            if(mysql_num_rows($res)>0){
                $rowcount		= mysql_fetch_assoc($res);
                $count_ext = $rowcount['cnt'];
            }
			return $count_ext;
		}
        
		public function getserverstatus() {
            $serverstatus=0;
            $sqlserver= "SELECT id, name, status FROM tbl_promptstatus WHERE name='csdeavhanlogin';";
            $resserver = $this->conn_local->query_sql($sqlserver);
            if(mysql_num_rows($resserver)>0){
                $rowserver		 = mysql_fetch_assoc($resserver);
                $serverstatus   = $rowserver['status'];
            }
            return $serverstatus;
        }
	}
?>
