<?php

$ruleset = new TwigCsFixer\Ruleset\Ruleset();
$ruleset->addStandard(new TwigCsFixer\Standard\TwigCsFixer());
// Samakan dengan .editorconfig: indentasi Twig 2 spasi
$ruleset->overrideRule(new TwigCsFixer\Rules\Whitespace\IndentRule(spaceRatio: 2));

$finder = new TwigCsFixer\File\Finder();
$finder->in('app/Views');

$config = new TwigCsFixer\Config\Config();
$config->setRuleset($ruleset);
$config->setFinder($finder);

return $config;
