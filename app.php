<?php

require 'vendor/autoload.php';

use League\CLImate\CLImate;
use Charcleaner\Cleaner;

$showRules = true;
$showStats = true;
$showWarnings = true;

$cli = new CLImate();
$cleaner = new Cleaner();

$cleanTerms = [
    "&#x29a3;8364;&#8220;"                   => "-",
    "&#x29a3;8364;?"                         => "-",
    "&#xe6;#339;"                            => "Ü",
    "&#xe6;#376;"                            => "ß",
    "&#x29a3;8364;&zcaron;"                  => "\"",
    "&#x29a3;8364;&#339;"                    => "\"",
    "&#xe6;#376;"                            => "ß",
    "u&#776;"                                => "ü",
    "&#8239;"                                => " ",
    "&#124;"                                 => "|",
    "&#xe6;#8211;"                           => "Ö",
    "&#x38e5;"                               => "öße",
    "ÃŸ"                                     => "ß",
    "Ã¼"                                     => "ü",
    "Ãœ"                                     => "Ü",
    "&#x29a3;8364;&#732;"                    => "'",
    "&#8232;"                                => " ",
    "&#x29a3;8364;&#8482;"                   => "'",
    "a&#xe6;#8230;N"                         => "ä",
    "&#xe6;#8222;"                           => "Ä",
    "iso-8859-1"                             => "utf-8",
    " href=\"http://2008.tsi-kongress.de/\"" => "",
    " href=\"http://2009.tsi-kongress.de/\"" => "",
    " href=\"http://2010.tsi-kongress.de/\"" => "",
    " href=\"http://2011.tsi-kongress.de/\"" => "",
    " href=\"http://2012.tsi-kongress.de/\"" => "",
    " href=\"http://2013.tsi-kongress.de/\"" => "",
  ];
$cleaner->cleanTerms = $cleanTerms;

/* Array mit allen Dateien ermitteln */
$files = [];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('files/'));
while ($it->valid()) {
    if (!$it->isDot()) {
        $files[] = $it->key();
    }
    $it->next();
}

/* Regeln im Terminal anzeigen */
if ($showRules) {
    $cli->underline("Folgende Regeln sind definiert:");
    foreach ($cleanTerms as $search => $replace) {
        $cli->inline('ersetze ');
        $cli->red()->inline($search);
        $cli->inline(' mit ');
        $cli->green()->inline($replace);
        $cli->break();
    }
    $cli->border();
}

/* Gesamtzahl der gefundenen Dateien ausgeben (alle Dateiendungen) */
$cli->out('Anzahl gefundener Dateien: '.count($files));

/* Cleaner starten */
$stats = $cleaner->clean($files);

/* weitere verdächtige Sonderzeichen im Terminal anzeigen (+ Datei) */
if (count($stats['warningTerms']) > 0 && $showWarnings) {
    $cli->yellow()->bold()->inline('⚠ ');
    $cli->red()->out('es wurden weitere Verdächtige Zeichen gefunden: ');
    foreach ($stats['warningTerms'] as $warningTerm) {
        if (count($warningTerm['matches']) > 0) {
            $cli->red()->underline()->out($warningTerm['file']);
            foreach ($warningTerm['matches'] as $term) {
                $cli->red()->out($term);
            }
        }
    }
}

/* Statistiken im Terminal ausgeben */
if ($showStats) {
    $cli->green()->bold()->inline('✓ ');
    $cli->inline('geprüfte Dateien: ');
    $cli->green()->inline($stats['checked']);
    $cli->break();
    $cli->green()->bold()->inline('✓ ');
    $cli->inline('bereinigte Dateien: ');
    $cli->green()->inline($stats['cleaned']);
    $cli->break();
}
