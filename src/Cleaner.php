<?php
namespace Charcleaner;

use League\CLImate\CLImate;
use ForceUTF8\Encoding;

class Cleaner
{
    /**
     * 'suchen und ersetzen' Regeln in folgender Form
     *     array(
     *         'suchen'  => 'ersetzen',
     *         'suchen1' => 'ersetzen1',
     *     )
     *
     * @var array
     */
    public $cleanTerms = [];

    /**
     * Instanz von \League\CLImate\CLImate.
     *
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    public function __construct()
    {
        $this->cli = new CLImate();
    }

    /**
     * Alle Dateien aus dem Array $files auslesen und Regeln aus $cleanTerms
     * anwenden. Dabei werden Statistiken über die verarbeitet und bereinigten
     * Dateien gesammelt und übergeben.
     * Außerdem werden mögliche Sonderzeichen erkannt und zurückgegeben.
     * Wenn man die Methode über das CLI aufruft, zeigt eine Progressbar den
     * Fortschritt an.
     *
     * @param array $files zu bereinigende Dateien (Pfad zur Datei)
     *
     * @return array Statistiken in folgender Form
     *               array(
     *               'checked' => 100, // Anzahl aller geprüften Dateien
     *               'cleaned' => 10, // Anzahl aller bereinigten Dateien
     *               'warningTerms' => array(
     *               array(
     *               'matches' => '&#123;', // gefundenes Sonderzeichen
     *               'file' => 'files/index.html', // Datei, in der das
     *               //Sonderzeichen gefunden wurde
     *               )
     *               )
     *               )
     */
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
