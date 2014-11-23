<?php
error_reporting(E_ALL);
set_time_limit(0);
$adr = "127.0.0.1";//gethostbyname(trim(`hostname`));
echo $adr."\n\n";
$port = 1577;

$m_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($m_sock, SOL_SOCKET, SO_REUSEADDR, 1);
$cls = array($m_sock);

socket_bind($m_sock, $adr, $port);
socket_listen($m_sock, 5);
echo "Starting server...\n\n";

do{
    usleep(500);
    $changed = $cls;
    $val = @socket_select($changed,$write=NULL,$except=NULL,0);
    foreach ($changed as $sock) {
        if($sock === $m_sock){
            echo "wait...\n\n";
            $msgsock = socket_accept($m_sock);
            array_push($cls, $msgsock);
            echo "Connected...\n\n";
            socket_recv($msgsock, $hds, 2048, MSG_WAITALL);

            if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$hds,$matchs)){
                echo "do handshake...\n\n";

                $key = $matchs[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
                $key =  base64_encode(sha1($key, true)); 
                $headers = "HTTP/1.1 101 Switching Protocols\r\n".
                "Upgrade: websocket\r\n".
                "Connection: Upgrade\r\n".
                "Sec-WebSocket-Accept: $key".
                "\r\n\r\n";
                socket_write($msgsock, $headers);
                echo "handshak done...\n";
            }
        }else{
            $bytes = socket_recv($sock, $data, 2048, null);
            $d = unmask($data);
            foreach ($cls as $socket) {
                if($socket != $m_sock && $val > 0){
                    try{
                       socket_write($socket,(encode($d))); 
                    }catch(Exception $e){
                        unset($cls[$socket]);
                        socket_close($cls[$socket]);
                    }
                }
            }
        }
    } 
    
    
}while(1);
socket_close($m_sock);


function unmask($payload) {
    $length = ord($payload[1]) & 127;

    if($length == 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    }
    elseif($length == 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    }
    else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }

    $text = '';
for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i%4];
    }
    return $text;
}

function encode($text)
{
    // 0x1 text frame (FIN + opcode)
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if($length <= 125)      $header = pack('CC', $b1, $length);     elseif($length > 125 && $length < 65536)        $header = pack('CCS', $b1, 126, $length);   elseif($length >= 65536)
        $header = pack('CCN', $b1, 127, $length);

    return $header.$text;
}

