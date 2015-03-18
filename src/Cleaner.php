<?php
namespace Charcleaner;

use League\CLImate\CLImate;
use ForceUTF8\Encoding;

class Cleaner
{
    public $cleanTerms = [];

    protected $cli;

    public function __construct()
    {
        $this->cli = new CLImate();
    }

    protected function fileContent($filename)
    {
        return file_get_contents($filename);
    }
    public function clean($files)
    {
        $stats['cleaned'] = 0;
        $stats['checked'] = 0;
        $progress = $this->cli->progress()->total(count($files));
        $index = 0;
        foreach ($files as $file) {
            if (file_exists($file) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                $content = Encoding::toUTF8(file_get_contents($file));

                $size['original'] = strlen($content);

                foreach ($this->cleanTerms as $search => $replace) {
                    $content = str_replace($search, $replace, $content);
                }

                if (!file_put_contents($file, $content)) {
                    die("Datei $file konnte nicht geschrieben werden!");
                }

                if (strpos($content, '&')) {
                    preg_match('/&(?!nbsp|amp|quot|szlig|uuml|Uuml|auml|Auml|ouml|Ouml|bdquo|ndash|agrave|sup2|bdquo|ndash|gt|ldquo|rdquo)[#a-zA-Z0-9&<> ]*;/', $content, $matches);
                    $stats['warningTerms'][] = [
                      'matches' => $matches,
                      'file'    => $file,
                    ];
                }

                $size['new'] = strlen($content);

                if ($size['original'] !== $size['new']) {
                    $stats['cleaned'] += 1;
                }
                $stats['checked'] += 1;
            }
            $index += 1;
            $progress->current($index);
        }

        return $stats;
    }
}
