<?php

require_once('phpmailer/class.phpmailer.php');

class EmailManager {

    private $_operator_details = '';
    private $_default_operator = '';
    private $path = '';

    public function __construct($db) {
        $this->db_obj = new DB($db['signon_db']);
        $this->get_email_settings();
    }

    private function get_email_settings() {
        $query = "SELECT SMTPName, SMTPServer, SMTPUser, SMTPPassword, SMTPPort, SmtpSelected FROM " . TBL_SMSEMAILSETTINGS . " ORDER BY SmtpSelected DESC";
        $result = $this->db_obj->query($query);
        while ($row = $this->db_obj->fetchData($result)) {
            if (strtoupper($row['SmtpSelected']) == 'Y') {
                $this->_default_operator = trim($row['SMTPName']);
            }
            $this->_operator_details[] = $row;
        }
    }

    public function send_email($to_details, $from_details, $email_subject, $email_text) {
        if (!is_array($to_details))
            return 'ERROR:INVALID_RECEPIENT';
        if (!is_array($from_details))
            return 'ERROR:INVALID_SENDER';

        $mail = new PHPMailer();
        $mail->IsSMTP();
        //$mail->SMTPDebug = true;

        $mail->AddReplyTo('noreply@justdial.com');
        $mail->From = $from_details['email'];
        $mail->FromName = $from_details['name'];
        $mail->Subject = $email_subject;

        $mail->MsgHTML($email_text);

        $pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})^";

        foreach ($to_details as $to_name => $to_email) {
            $mail->AddAddress($to_email, $to_name);
            /* try{
              if(!preg_match($pattern,$to_email))
              {
              //return 'ERROR: Invalid email address';
              throw new customException($to_email);
              }
              }

              catch (customException $e)
              {
              echo $e->errorMessage();
              } */
        }

        for ($i = 0; $i < count($this->_operator_details); $i++) {
            $mail->Host = $this->_operator_details[$i]['SMTPServer'];
            $mail->Port = $this->_operator_details[$i]['SMTPPort'];

            if (!empty($this->_operator_details[$i]['SMTPUser'])) {
                $mail->Username = $this->_operator_details[$i]['SMTPUser'];
                $mail->Password = $this->_operator_details[$i]['SMTPPasswd'];
                $mail->SMTPAuth = true;
            }

            $status = $mail->Send();
            if (!$status) {
                $error = $mail->ErrorInfo;
                if (starts_with($error, 'SMTP Error'))
                    continue;
            }
            else
                break;
        }

        if ($status === true) {
            return '1';
        } else {
            return "SEND ERROR: " . $error;
        }
    }

    public function errorMessage() {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
                . ': <b>' . $this->getMessage() . '</b> is not a valid E-Mail address';
        return $errorMsg;
    }

}

?>
