<?php

namespace AppBundle\Translation;

use Symfony\Component\Translation\Dumper\YamlFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

class TreeYamlFileDumper extends YamlFileDumper
{
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = []): string
    {
        $options['as_tree'] = true;
        $options['inline'] = 5;

        return parent::formatCatalogue($messages, $domain, $options);
    }
}
