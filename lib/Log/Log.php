<?php

namespace lib\Log;

class Log {

    protected $name;
    protected $route;

    public function __construct($route, $name = null)
    {
        $this->route = realpath($route);
        $this->name = $name==null?date("Ymd",time()):$name;
        $this->dump("\n\n\n\n");
        $this->dd([],"************** NEW EXECUTION **************");
    }

    public function dump ($content)
    {
        file_put_contents($this->route . DIRECTORY_SEPARATOR . $this->name . '.log', $content, FILE_APPEND);
    }
    
    public function dd ($headers = [], $message, $data = [], $dump_file = true)
    {
        $date = date("\[H:i:s\] ", time());
        $headers = !$headers?'':(is_array($headers)?"[".preg_replace('/\[$/i', "", implode("][", $headers))."] ":"[{$headers}] ");
        $log = $date.$headers.$message;
        if (is_array($data) && count($data)>0)
        {
            $data = json_encode($data);
            $data = preg_replace("/\"([\w\d\-]+)\"/i", "$1", $data);
            $log.= "\n".$date.$data;
        }
        $log.= "\n\n";

        echo $log;
        if ($dump_file)
            $this->dump($log);
    }
}