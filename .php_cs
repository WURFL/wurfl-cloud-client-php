<?php

$finder = PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude("vendor")
            ->exclude("cache")
;

return PhpCsFixer\Config::create()
    ->setRules(array(
         '@PSR2' => true,
         'hash_to_slash_comment' => true,
    ))
    ->setFinder($finder)
;
