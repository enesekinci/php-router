<?php

namespace EnesEkinci\PhpRouter;

class Response
{
    public function header($header)
    {
    }

    public function withHeaders(array $headers)
    {
    }

    public function html()
    {
    }

    public function json($data)
    {
        # code...
    }

    public function download()
    {
    }

    public function file()
    {
    }

    public function status(int $code = 200)
    {
        http_response_code($code);
    }

    public function redirect(string $to, int $status = 301)
    {
        $this->status($status);
        header("location:$to");
    }
}
