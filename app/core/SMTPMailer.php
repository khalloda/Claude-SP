<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

class SMTPMailer
{
    private $socket;
    private array $config;

    public function __construct()
    {
        $this->config = Config::$mail;
    }

    public function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            $this->connect();
            $this->authenticate();
            $this->sendMessage($to, $subject, $body, $attachments);
            $this->disconnect();
            return true;
        } catch (\Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            return false;
        }
    }

    private function connect(): void
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        
        $this->socket = fsockopen($host, $port, $errno, $errstr, 30);
        
        if (!$this->socket) {
            throw new \Exception("Cannot connect to SMTP server: $errstr ($errno)");
        }
        
        $this->readResponse(); // Read greeting
        
        // Send EHLO
        $this->sendCommand("EHLO " . $_SERVER['HTTP_HOST']);
        
        // Start TLS if required
        if ($this->config['encryption'] === 'tls') {
            $this->sendCommand("STARTTLS");
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand("EHLO " . $_SERVER['HTTP_HOST']);
        }
    }

    private function authenticate(): void
    {
        if (!empty($this->config['username']) && !empty($this->config['password'])) {
            $this->sendCommand("AUTH LOGIN");
            $this->sendCommand(base64_encode($this->config['username']));
            $this->sendCommand(base64_encode($this->config['password']));
        }
    }

    private function sendMessage(string $to, string $subject, string $body, array $attachments = []): void
    {
        // Send mail commands
        $this->sendCommand("MAIL FROM: <{$this->config['from_address']}>");
        $this->sendCommand("RCPT TO: <{$to}>");
        $this->sendCommand("DATA");
        
        // Send headers and body
        $message = $this->buildMessage($to, $subject, $body, $attachments);
        $this->sendData($message);
        $this->sendCommand(".");
    }

    private function buildMessage(string $to, string $subject, string $body, array $attachments = []): string
    {
        $boundary = uniqid('boundary_');
        
        $message = "To: {$to}\r\n";
        $message .= "From: {$this->config['from_name']} <{$this->config['from_address']}>\r\n";
        $message .= "Subject: {$subject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        
        if (!empty($attachments)) {
            $message .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
            $message .= "--{$boundary}\r\n";
        }
        
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $body . "\r\n";
        
        // Add attachments
        foreach ($attachments as $attachment) {
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: application/octet-stream\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
            $message .= chunk_split(base64_encode($attachment['content'])) . "\r\n";
        }
        
        if (!empty($attachments)) {
            $message .= "--{$boundary}--\r\n";
        }
        
        return $message;
    }

    private function sendCommand(string $command): void
    {
        fwrite($this->socket, $command . "\r\n");
        $this->readResponse();
    }

    private function sendData(string $data): void
    {
        fwrite($this->socket, $data);
    }

    private function readResponse(): string
    {
        $response = '';
        while (($line = fgets($this->socket, 512)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    private function disconnect(): void
    {
        if ($this->socket) {
            $this->sendCommand("QUIT");
            fclose($this->socket);
        }
    }
}
