<?php
namespace NewdichFiles;

class Download{
    private $path;
    public function __construct($path){
        $this->path = $path;
    }

    public function process(){
        $filePath = $this->path;
        if (!file_exists($filePath)) {
            http_response_code(404);
            return json_encode(["status"=>"failed", "response"=>"File not found"], JSON_PRETTY_PRINT);
        }

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"");
        header("Content-Length: " . filesize($filePath));
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        readfile($filePath);
        return json_encode(["status"=>"success", "response"=>"Download Initiated"], JSON_PRETTY_PRINT);
    }
}
?>