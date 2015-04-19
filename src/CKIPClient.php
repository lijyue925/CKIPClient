<?php namespace Lijyue925\CKIP;

use xmlWriter;

class CKIPClient {

    private $server_ip;
    private $server_port;
    private $username;
    private $password;

    /**
     * Method __construct initialize instance
     *
     * @param $server_ip
     * @param $server_port
     * @param $username
     * @param $password
     */
    public function __construct ($server_ip, $server_port, $username, $password)
    {

        $this->server_ip = $server_ip;
        $this->server_port = $server_port;
        $this->username = $username;
        $this->password = $password;

    }

    /**
     * Method send
     *
     * @param string $raw_text
     *
     * @return string $result
     */
    private function send ($raw_text)
    {
        if (! empty($raw_text))
        {
            /*$this->raw_text = $raw_text;*/
            $xw = new xmlWriter();
            $xw->openMemory();
            $xw->startDocument('1.0');

            $xw->startElement('wordsegmentation');
            $xw->writeAttribute('version', '0.1');
            $xw->startElement('option');
            $xw->writeAttribute('showcategory', '1');
            $xw->endElement();

            $xw->startElement('authentication');
            $xw->writeAttribute('username', $this->username);
            $xw->writeAttribute('password', $this->password);
            $xw->endElement();

            $xw->startElement('text');
            $xw->writeRaw($raw_text);
            $xw->endElement();

            $xw->endElement();

            $message = iconv("utf-8", "big5", $xw->outputMemory(true));

            //send message to CKIP server
            set_time_limit(60);

            $protocol = getprotobyname("tcp");
            $socket = socket_create(AF_INET, SOCK_STREAM, $protocol);
            socket_connect($socket, $this->server_ip, $this->server_port);
            socket_write($socket, $message);
            $result = iconv("big5", "utf-8", socket_read($socket, strlen($message) * 3));
            socket_shutdown($socket);
            socket_close($socket);

            return $result;
        }

        return null;
    }

    /**
     * Method getSentence
     *
     * @return array $sentences
     */
    public function getSentence ($raw_text)
    {
        $return_text = $this->send($raw_text);
        $sentences = [];
        if ($parse_return_text = simplexml_load_string($return_text))
        {
            $sentence_array = $parse_return_text->result->sentence;
            foreach ($sentence_array as $key => $sentence)
            {
                $sentence_value = (string) $sentence;
                // remove invisible characters
                $check_sentence = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $sentence_value);
                if (! empty($check_sentence))
                {
                    array_push($sentences, $sentence_value);
                }
            }
        }

        return $sentences;
    }

    /**
     * Method getTerm
     *
     * @return array $terms
     */
    public function getTerm ($raw_text)
    {
        $sentences = $this->getSentence($raw_text);
        $terms = [];
        foreach ($sentences as $sentence)
        {
            foreach (explode("　", $sentence) as $word)
            {
                if ($word != "")
                {
                    preg_match("/(\S*)\((\S*)\)/", $word, $m);
                    $temp_array = array("term" => $m[1], "tag" => $m[2]);
                    array_push($terms, $temp_array);
                }
            }
        }

        return $terms;
    }

    /**
     * Method __destruct unset instance value
     *
     * @return void
     */
    public function __destruct ()
    {
        $class_property_array = get_object_vars($this);
        foreach ($class_property_array as $property_key => $property_value)
        {
            unset($this->$property_key);
        }
    }
}

?>