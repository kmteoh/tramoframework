<?php

/**
 * @author Ming Teoh
 * @copyright 2013
 * @name Tramo Framework
 *
 * @license http://opensource.org/licenses/MIT
 */

class mailService extends service {
    public function sendHtmlMail($to,$subject,$body,$from=null,$cc=null,$bcc=null,$highPriority=false) {
        return $this->_sendMail($to,$subject,$body,$from,'html',$cc,$bcc,$highPriority);
    }

    public function sendTextMail($to,$subject,$body,$from=null,$cc=null,$bcc=null,$highPriority=false) {
        return $this->_sendMail($to,$subject,$body,$from,'plain',$cc,$bcc,$highPriority);
    }

    private function _sendMail($to,$subject,$body,$from,$contentType,$cc,$bcc,$highPriority) {
        $runtimeConfing = config::getInstance();
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/$contentType; charset=iso-8859-1";
        if (!$from) {
            $from = $runtimeConfing->mail['from'];
        }
        $headers[] = "From: $from";
        if ($cc)
            $headers[] = "Cc: " . $cc;
        if ($bcc)
            $headers[] = "Bcc: " . $bcc;
        if ($highPriority)
            $headers[] = "X-Priority: 1\r\nX-MSMail-Priority: High\r\nImportance: High";

        $header = implode("\r\n", $headers) . "\r\n";

        if ($runtimeConfing->environment != "production")
            $subject = '[' . $runtimeConfing->environment . '] ' . $subject;

        return mail($to, $subject, $body, $header);
    }
}